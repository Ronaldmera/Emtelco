<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use OpenSpout\Reader\XLSX\Reader;
use OpenSpout\Writer\XLSX\Writer;
use OpenSpout\Common\Entity\Row;
use Carbon\Carbon;

class MaterialController extends Controller
{
    public function showMissingMaterials()
    {
        $bodegas = [
            ['id' => 3175, 'ciudad' => 'Popayan'],
            ['id' => 3177, 'ciudad' => 'Cali'],
        ];

        return view('Materials.missingMaterials', compact('bodegas'));
    }

    public function excelInput(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'excel_files'   => 'required',
            'excel_files.*' => 'mimes:xls,xlsx|max:20480'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Error al subir los archivos.'
            ], 422);
        }

        $archivosGuardados = [];

        foreach ($request->file('excel_files') as $archivo) {
            $nombre = time() . '_' . $archivo->getClientOriginalName();
            $ruta = $archivo->storeAs('excels', $nombre, 'public');

            $archivosGuardados[] = [
                'nombre' => $nombre,
                'ruta'   => $ruta
            ];
        }

        session(['archivos_excel' => $archivosGuardados]);

        return response()->json([
            'message'  => 'Archivos subidos correctamente.',
            'archivos' => $archivosGuardados
        ]);
    }

    public function modalData(Request $request)
    {
        $idAlmacen = $this->normValue($request->input('almacen_id'));

        $archivosExcel = session('archivos_excel', []);
        if (empty($archivosExcel)) {
            return response()->json(['message' => 'No hay archivos Excel cargados en la sesión.'], 400);
        }

        // Preparar archivo de salida
        $fileName = 'MatFiltrados' . time() . '.xlsx';
        $dirPublic = storage_path('app/public/excels');
        if (!is_dir($dirPublic)) {
            @mkdir($dirPublic, 0775, true);
        }
        $filePath = $dirPublic . '/' . $fileName;

        $writer = new Writer();
        $writer->openToFile($filePath);

        $encabezadosEscritos = false;
        $headersMap = []; // Guardar nombres de columnas por índice

        foreach ($archivosExcel as $archivo) {
            $ruta = storage_path("app/public/" . $archivo['ruta']);
            $reader = new Reader();
            $reader->open($ruta);

            foreach ($reader->getSheetIterator() as $sheet) {
                $rowCount = 0;
                $columnasPermitidas = [];
                $colALM = null;

                foreach ($sheet->getRowIterator() as $row) {
                    $cells = $row->toArray();
                    $rowCount++;

                    if ($rowCount === 1) {
                        // --- ENCABEZADOS ---
                        $columnasExcluidas = ['APROV-ASEG', 'PILOTOS', 'DIF. LIMITES'];
                        $mapa = array_map([$this, 'normValue'], $cells);

                        foreach ($mapa as $i => $val) {
                            if (!in_array($val, $columnasExcluidas)) {
                                $columnasPermitidas[] = $i;
                                $headersMap[$i] = $cells[$i]; // Guardar nombre real de la columna
                            }
                        }

                        if (!$encabezadosEscritos) {
                            $encabezados = [];
                            foreach ($columnasPermitidas as $i) {
                                $encabezados[] = $cells[$i];
                            }
                            $rowEncabezado = Row::fromValues($encabezados);
                            $writer->addRow($rowEncabezado);
                            $encabezadosEscritos = true;
                        }

                        // Guardamos la posición de ALM
                        $colALM = array_search('ALM', $mapa);
                        continue;
                    }

                    // --- FILAS ---
                    if (isset($colALM) && $this->normValue($cells[$colALM]) === $idAlmacen) {
                        $filaFiltrada = [];
                        foreach ($columnasPermitidas as $i) {
                            $valor = $cells[$i];

                            // Formatear fechas solo en las columnas correctas
                            if (in_array(strtoupper(trim($headersMap[$i])), ['FECHA', 'FECHA_CARGA', 'FECHA PEDIDO'])) {
                                try {
                                    $fecha = Carbon::parse($valor);
                                    $valor = $fecha->format('j/m/Y'); 
                                } catch (\Exception $e) {
                                    // si no se puede parsear, dejar valor original
                                }
                            }

                            $filaFiltrada[] = $valor;
                        }
                        $rowData = Row::fromValues($filaFiltrada);
                        $writer->addRow($rowData);
                    }
                }
            }

            $reader->close();
        }

        $writer->close();

        session()->forget('archivos_excel');

       return response()->json([
            'message' => 'Archivo generado correctamente',
            'file' => asset('storage/excels/' . $fileName)
        ]);
    }

    private function normValue($value)
    {
        return strtoupper(trim((string) $value));
    }
}
