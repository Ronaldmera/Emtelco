<?php

use App\Http\Controllers\HomeController;
use App\Http\Controllers\MaterialController;
use Illuminate\Support\Facades\Route;

Route::controller(HomeController::class)->group(function () {
    Route::get('/','index')->name('home.index');
  });

Route::controller(MaterialController::class)->group(function () {
    Route::get('materials/missingMaterials','showMissingMaterials')->name('material.showMissingMaterials');
    Route::post('materials/uploadExcel','excelInput')->name('material.excelInput');
    Route::post('materials/modalData','modalData')->name('material.modalData');
  });

