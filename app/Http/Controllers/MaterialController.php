<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Shared\Date;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;

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
            'excel_files.*' => 'mimes:xls,xlsx,xlsm|max:20480' // hasta 20MB
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
        // $ciudad    = $this->normValue($request->input('ciudad')); 

        $archivosExcel = session('archivos_excel', []);
        if (empty($archivosExcel)) {
            return response()->json(['message' => 'No hay archivos Excel cargados en la sesión.'], 400);
        }

        $encabezadosOriginales = null;
        $spreadsheetOut = new Spreadsheet();
        $sheetOut = $spreadsheetOut->getActiveSheet();
        $rowOut = 2;

        foreach ($archivosExcel as $archivo) {
            $ruta = storage_path("app/public/" . $archivo['ruta']);

            $reader = IOFactory::createReaderForFile($ruta);
            $reader->setReadDataOnly(false); // necesario para detectar fechas
            $spreadsheet = $reader->load($ruta);

            foreach ($spreadsheet->getAllSheets() as $hoja) {
                $highestRow = $hoja->getHighestRow();
                $highestCol = $hoja->getHighestColumn();
                $highestColIndex = Coordinate::columnIndexFromString($highestCol);

                // encabezados (primera fila)
                $mapa = [];
                for ($col = 1; $col <= $highestColIndex; $col++) {
                    $mapa[$col] = $this->normValue($hoja->getCellByColumnAndRow($col, 1)->getValue());
                }

                $colALM = $this->buscarColumna($mapa, [
                    'ALM','ALMACEN','IDALMACEN','ID_ALMACEN',
                    'IDALM','CODALM','COD_ALM','CODALMACEN','COD_ALMACEN'
                ]);
                


                if ($colALM === null) {
                    continue;
                }

                // escribir encabezados solo una vez
                if ($encabezadosOriginales === null) {
                    $encabezadosOriginales = [];
                    for ($col = 1; $col <= $highestColIndex; $col++) {
                        $encabezadosOriginales[] = $hoja->getCellByColumnAndRow($col, 1)->getValue();
                    }
                    $sheetOut->fromArray([$encabezadosOriginales], null, 'A1');
                }

                // recorrer filas
                for ($row = 2; $row <= $highestRow; $row++) {
                    $fila = [];
                    for ($col = 1; $col <= $highestColIndex; $col++) {
                        $cell  = $hoja->getCellByColumnAndRow($col, $row);
                        $valor = $cell->getValue();

                        // 🔑 si es fecha => formatear a d/m/Y
                        if (Date::isDateTime($cell) && is_numeric($valor)) {
                            $valor = Date::excelToDateTimeObject($valor)->format('d/m/Y');
                        }

                        $fila[] = $valor;
                    }

                    $valorALM    = isset($fila[$colALM - 1]) ? $this->normValue($fila[$colALM - 1]) : null;
                
                    if ($valorALM === $idAlmacen ) {
                        $sheetOut->fromArray([$fila], null, 'A' . $rowOut);
                        $rowOut++;
                    }
                }
            }
        }

        $fileName = 'MatFiltrados' . time() . '.xlsx';
        $dirPublic = storage_path('app/public/excels');
        if (!is_dir($dirPublic)) {
            @mkdir($dirPublic, 0775, true);
        }
        $filePath = $dirPublic . '/' . $fileName;

        $writer = new Xlsx($spreadsheetOut);
        $writer->save($filePath);

        session()->forget('archivos_excel');

        return response()->download($filePath);
    }

    private function normValue($value)
    {
        return strtoupper(trim((string) $value));
    }

    private function mapaEncabezados(array $encabezados)
    {
        $mapa = [];
        foreach ($encabezados as $index => $encabezado) {
            $mapa[$index] = $this->normValue($encabezado);
        }
        return $mapa;
    }

    private function buscarColumna(array $mapa, array $nombres)
    {
        foreach ($mapa as $index => $encabezado) {
            if (in_array($encabezado, $nombres)) {
                return $index;
            }
        }
        return null;
    }
}
