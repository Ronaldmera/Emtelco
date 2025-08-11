<?php

namespace App\Http\Controllers;
use Illuminate\Support\Facades\Validator;

use Illuminate\Http\Request;


class MaterialController extends Controller
{
    public function showMissingMaterials(){
        return view('Materials.missingMaterials');
    }


public function excelInput(Request $request)
{
    $validator = Validator::make($request->all(), [
        'excel_files' => 'required',
        'excel_files.*' => 'mimes:xls,xlsx,xlsm|max:2048'
    ]);

    if ($validator->fails()) {
        return response()->json([
            'message' => 'Error al subir los archivos.',    
        ], 422);
    }

    foreach ($request->file('excel_files') as $archivo) {
        $nombre = time() . '_' . $archivo->getClientOriginalName();
        $archivo->storeAs('excels', $nombre, 'public');
    }

    return response()->json(['message' => 'Archivos subidos correctamente.']);
}


}
        

