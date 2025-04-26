<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ReportController;

Route::middleware(['auth'])->prefix('admin')->group(function () {
    Route::get('gerar/catalogo', [ReportController::class, 'generateCatalog'])->name('report.catalog');
});