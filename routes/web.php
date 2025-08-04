<?php

use App\Http\Controllers\MaterialController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
})->name('welcome');

Route::get('/missingMaterials', [MaterialController::class, 'showMissingMaterials'])->name('material.showMissingMaterials');
