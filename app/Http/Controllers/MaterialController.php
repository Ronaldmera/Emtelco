<?php

namespace App\Http\Controllers;
use Illuminate\Support\Facades\Validator;

use Illuminate\Http\Request;


class MaterialController extends Controller
{
    public function showMissingMaterials(){
          $bodegas = [
        ['id' => 3175, 'ciudad' => 'Popayán'],
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

    // Guardar en sesión para usar en modalData
    session(['archivos_excel' => $archivosGuardados]);

    return response()->json([
        'message' => 'Archivos subidos correctamente.',
        'archivos' => $archivosGuardados
    ]);
}
public function modalData(Request $request)
{
    $idAlmacen = $request->input('almacen_id');
    $ciudad = $request->input('ciudad');

    $archivosExcel = session('archivos_excel', []);

    if (empty($archivosExcel)) {
        return response()->json([
            'message' => 'No hay archivos Excel cargados en la sesión.'
        ], 400);
    }
    // Procesar archivos Excel
    foreach ($archivosExcel as $archivo) {
        // Lógica de procesamiento
    }
    // Limpiar la sesión para no acumular
    session()->forget('archivos_excel');

    return response()->json([
        'almacen_id' => $idAlmacen,
        'ciudad'     => $ciudad,
        'archivos'   => $archivosExcel,
        'message'    => 'Datos y archivos procesados correctamente.'
    ]);
}



}
        

