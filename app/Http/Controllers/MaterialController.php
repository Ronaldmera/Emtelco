<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class MaterialController extends Controller
{
    public function showMissingMaterials(){
        return view('Materials.missingMaterials');
    }
}
