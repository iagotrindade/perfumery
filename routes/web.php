<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ReportController;

Route::middleware(['auth'])->prefix('admin')->group(function () {
    Route::get('gerar/catalogo', [ReportController::class, 'generateCatalog'])->name('report.catalog');
    Route::get('gerar/relatório/vendas', [ReportController::class, 'generateSalesReport'])->name('report.sales');
    Route::get('gerar/relatório/clientes', [ReportController::class, 'generateCustomersReport'])->name('report.customers');
    Route::get('gerar/relatório/extrato/venda/{id}', [ReportController::class, 'generateSaleExtractReport'])->name('report.extract');
    Route::get('gerar/relatório/extrato/cliente/{id}', [ReportController::class, 'generateCustomerExtractReport'])->name('report.customer.extract');
});