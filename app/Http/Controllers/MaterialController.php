<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

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
            'excel_files' => 'required',
            'excel_files.*' => 'mimes:xls,xlsx,xlsm|max:2048'
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

        // Guardar en sesión
        session(['archivos_excel' => $archivosGuardados]);

        return response()->json([
            'message' => 'Archivos subidos correctamente.',
            'archivos' => $archivosGuardados
        ]);
    }

public function modalData(Request $request)
{
    $idAlmacen = $this->normValue($request->input('almacen_id'));
    $ciudad    = $this->normValue($request->input('ciudad'));

    $archivosExcel = session('archivos_excel', []);
    if (empty($archivosExcel)) {
        return response()->json(['message' => 'No hay archivos Excel cargados en la sesión.'], 400);
    }

    $registrosFiltrados = [];
    $encabezadosOriginales = null;   // Mantendremos los encabezados tal cual para el archivo final
    $ordenColumnas = null;           

    foreach ($archivosExcel as $archivo) {
        $ruta = storage_path("app/public/" . $archivo['ruta']);
        $spreadsheet = IOFactory::load($ruta);

        foreach ($spreadsheet->getAllSheets() as $hoja) {
            $filas = $hoja->toArray(null, true, true, true); // claves A,B,C...
            if (empty($filas) || !isset($filas[1])) continue;

            // 1) Detectar columnas por encabezado (tolerante a variaciones)
            $encabezados = $filas[1]; // p.ej. ["A"=>"ALM", "B"=>"CIUDAD_EMPRESA", ...]
            $mapa = $this->mapaEncabezados($encabezados);

            $colALM    = $this->buscarColumna($mapa, ['ALM','ALMACEN','IDALMACEN','ID_ALMACEN','IDALM','CODALM','COD_ALM','CODALMACEN','COD_ALMACEN']);
            $colCIUDAD = $this->buscarColumna($mapa, ['CUIDAD_EMPRESA','CIUDAD_EMPRESA','CIUDAD','CUIDAD']); 

            if (!$colALM || !$colCIUDAD) {
                // Si esta hoja no trae las columnas necesarias, seguimos a la siguiente
                continue;
            }

            // Guardar encabezados y orden de columnas la primera vez
            if ($encabezadosOriginales === null) {
                $encabezadosOriginales = $encabezados;   // para escribirlos iguales en el Excel de salida
                $ordenColumnas = array_keys($encabezados); // p.ej. ["A","B","C",...]
            }

            // 2) Recorrer filas de datos (desde la 2)
            foreach ($filas as $n => $fila) {
                if ($n === 1) continue; // saltar encabezados

                // Normalizar valores ALM y CIUDAD de la fila
                $valorALM    = isset($fila[$colALM])    ? $this->normValue($fila[$colALM])    : null;
                $valorCiudad = isset($fila[$colCIUDAD]) ? $this->normValue($fila[$colCIUDAD]) : null;

                if ($valorALM === $idAlmacen && $valorCiudad === $ciudad) {
                    // Asegurar el mismo orden de columnas al exportar
                    $filaOrdenada = [];
                    foreach ($ordenColumnas as $colKey) {
                        $filaOrdenada[] = $fila[$colKey] ?? null;
                    }
                    $registrosFiltrados[] = $filaOrdenada;
                }
            }
        }
    }

    // 3) Crear nuevo Excel con encabezados + coincidencias
    $spreadsheetOut = new Spreadsheet();
    $sheetOut = $spreadsheetOut->getActiveSheet();

    // Encabezados en el mismo orden
    $headersOrdenados = [];
    foreach ($ordenColumnas as $colKey) {
        $headersOrdenados[] = $encabezadosOriginales[$colKey] ?? $colKey;
    }

    $sheetOut->fromArray([$headersOrdenados], null, 'A1');
    $sheetOut->fromArray($registrosFiltrados, null, 'A2');

    $fileName = 'MatFiltrados' . time() . '.xlsx';
    $dirPublic = storage_path('app/public/excels');
    if (!is_dir($dirPublic)) {
        @mkdir($dirPublic, 0775, true);
    }
    $filePath = $dirPublic . '/' . $fileName;

    $writer = new Xlsx($spreadsheetOut);
    $writer->save($filePath);

    // Limpiar sesión
    session()->forget('archivos_excel');

    // return response()->json([
    //     'message' => 'Archivo generado correctamente.',
    //     'coincidencias' => count($registrosFiltrados),
    //     'archivo' => asset('storage/excels/' . $fileName)
    // ]);
    return response()->download($filePath);

}

/**
 * Normaliza valores para comparar (trim, mayúsculas, tildes y espacios raros).
 */
private function normValue($v): string
{
    $v = (string)$v;
    // Reemplazar NBSP y espacios múltiples
    $v = str_replace("\xC2\xA0", ' ', $v);
    $v = preg_replace('/\s+/u', ' ', $v ?? '');
    $v = trim($v);

    // Mayúsculas
    if (function_exists('mb_strtoupper')) {
        $v = mb_strtoupper($v, 'UTF-8');
    } else {
        $v = strtoupper($v);
    }

    // Quitar tildes comunes (sin depender de iconv)
    $repl = ['Á'=>'A','É'=>'E','Í'=>'I','Ó'=>'O','Ú'=>'U','Ü'=>'U','Ñ'=>'N'];
    $v = strtr($v, $repl);

    return $v;
}

/**
 * Normaliza encabezados para buscarlos por “clave”.
 * Quita espacios/guiones/guiones bajos/puntos y tildes.
 */
private function normHeader($v): string
{
    $v = $this->normValue($v);
    $v = str_replace([' ', '_', '-', '.'], '', $v);
    return $v;
}

/**
 * Crea un mapa normalizado de encabezado => claveColumna (A,B,C...).
 */
private function mapaEncabezados(array $encabezados): array
{
    $mapa = [];
    foreach ($encabezados as $colKey => $titulo) {
        $mapa[$this->normHeader($titulo)] = $colKey;
    }
    return $mapa;
}

/**
 * Busca la primera coincidencia de una lista de posibles nombres de columna.
 * @param array $mapa  (clave normalizada => colKey)
 * @param array $posibles  (p.ej. ['ALM','ALMACEN','IDALMACEN',...])
 * @return string|null  colKey (A,B,C,...) o null si no encuentra
 */
private function buscarColumna(array $mapa, array $posibles): ?string
{
    foreach ($posibles as $p) {
        $np = $this->normHeader($p);
        if (isset($mapa[$np])) return $mapa[$np];
    }
    return null;
}

}
