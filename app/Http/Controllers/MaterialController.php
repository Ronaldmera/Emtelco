<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class MaterialController extends Controller
{
    public function showMissingMaterials(){
        return view('Materials.missingMaterials');
    }


    public function excelInput(Request $request)
    {
        $request->validate([
            'excel_files' => 'required',
            'excel_files.*' => 'mimes:xls,xlsx|max:2048'
        ]);

        foreach ($request->file('excel_files') as $archivo) {
            $nombre = time() . '_' . $archivo->getClientOriginalName();
            $archivo->storeAs('excels', $nombre, 'public');
        }

        return response()->json(['message' => 'Archivos subidos correctamente.']);
    }

}
        

