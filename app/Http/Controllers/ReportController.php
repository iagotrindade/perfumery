<?php

namespace App\Http\Controllers;

use App\Models\Brand;
use Illuminate\Http\Request;
use Spatie\LaravelPdf\Facades\Pdf;

class ReportController extends Controller
{
    public function generateCatalog() {

        $brands = Brand::all();

        return Pdf::view('reports.catalog', [
            'brands' => $brands
            ])
        ->format('a4')
        ->name('your-invoice.pdf');
    }
}
