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
            'excel_files.*' => 'mimes:xls,xlsx,xlsm|max:20480'
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

        $spreadsheetOut = new Spreadsheet();
        $sheetOut = $spreadsheetOut->getActiveSheet();
        $rowOut = 2;

        $columnasExcluidas = ['APROV-ASEG', 'PILOTOS', 'DIF. LIMITES'];
        $encabezadosFiltrados = null;

        foreach ($archivosExcel as $archivo) {
            $ruta = storage_path("app/public/" . $archivo['ruta']);

            $reader = IOFactory::createReaderForFile($ruta);
            $reader->setReadDataOnly(false);
            $spreadsheet = $reader->load($ruta);

            foreach ($spreadsheet->getAllSheets() as $hoja) {
                $highestRow = $hoja->getHighestRow();
                $highestCol = $hoja->getHighestColumn();
                $highestColIndex = Coordinate::columnIndexFromString($highestCol);

                $data = $hoja->toArray(null, false, false, false);

                $mapa = [];
                for ($col = 1; $col <= $highestColIndex; $col++) {
                    $colLetter = Coordinate::stringFromColumnIndex($col);
                    $mapa[$col] = $this->normValue($hoja->getCell($colLetter . '1')->getValue());
                }

                $colALM = $this->buscarColumna($mapa, [
                    'ALM','ALMACEN','IDALMACEN','ID_ALMACEN',
                    'IDALM','CODALM','COD_ALM','CODALMACEN','COD_ALMACEN'
                ]);

                if ($colALM === null) {
                    continue;
                }

                $columnasPermitidas = [];
                foreach ($mapa as $index => $encabezado) {
                    if (!in_array($encabezado, $columnasExcluidas)) {
                        $columnasPermitidas[] = $index;
                    }
                }

                if ($encabezadosFiltrados === null) {
                    $encabezadosFiltrados = [];
                    foreach ($columnasPermitidas as $index) {
                        $encabezadosFiltrados[] = $data[0][$index - 1];
                    }
                    $sheetOut->fromArray([$encabezadosFiltrados], null, 'A1');
                }

                for ($row = 2; $row <= $highestRow; $row++) {
                    $filaOriginal = [];
                    for ($col = 1; $col <= $highestColIndex; $col++) {
                        $colLetter = Coordinate::stringFromColumnIndex($col);
                        $cell = $hoja->getCell($colLetter . $row);
                        $valor = $cell->getValue();

                        if (Date::isDateTime($cell) && is_numeric($valor)) {
                            $valor = Date::excelToDateTimeObject($valor)->format('d/m/Y');
                        }

                        $filaOriginal[] = $valor;
                    }

                    $valorALM = isset($filaOriginal[$colALM - 1]) ? $this->normValue($filaOriginal[$colALM - 1]) : null;

                    if ($valorALM === $idAlmacen) {
                        $filaFiltrada = [];
                        foreach ($columnasPermitidas as $index) {
                            $filaFiltrada[] = $filaOriginal[$index - 1];
                        }
                        $sheetOut->fromArray([$filaFiltrada], null, 'A' . $rowOut);
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
        $writer->setPreCalculateFormulas(false);
        $writer->save($filePath);

        session()->forget('archivos_excel');

        return response()->download($filePath);
    }

    private function normValue($value)
    {
        return strtoupper(trim((string) $value));
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
