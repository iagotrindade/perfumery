<?php

namespace App\Http\Controllers;

use App\Models\Sale;
use App\Models\Brand;
use App\Models\Customer;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\DB;

class ReportController extends Controller
{
    public function generateCatalog()
    {
        $brands = Brand::all();

        return PDF::loadView('reports.catalog', ['brands' => $brands])

            ->stream('catalogo.pdf'); // Nome do arquivo
    }

    public function generateSalesReport(Request $request)
    {
        // Definir período do relatório
        $startDate = now()->startOfMonth();
        $endDate = now()->endOfMonth();
        $daysInPeriod = $startDate->diffInDays($endDate) + 1;

        // Buscar dados básicos
        $sales = Sale::with('products')->whereBetween('created_at', [$startDate, $endDate])->get();

        // Cálculos iniciais
        $totalSalesValue = $sales->sum('total');
        $salesCount = $sales->count();
        $hasSales = $totalSalesValue > 0 && $salesCount > 0;

        // Processar produtos vendidos
        $productsData = $sales->pluck('products')->flatten();
        $productsQuantity = $productsData->sum('quantity');

        $groupedProducts = $productsData->groupBy('product_id')->map(function ($products) {
            $first = $products->first();
            return [
                'name' => $first->product->name,
                'sale_value' => $first->product->sale_value,
                'total_quantity' => $products->sum('quantity'),
                'total_value' => $products->sum(fn($p) => $p->quantity * $p->product->sale_value),
            ];
        });

        // Dados por marca
        $groupedByBrand = DB::table('product_sales')
            ->join('products', 'product_sales.product_id', '=', 'products.id')
            ->join('sales', 'product_sales.sale_id', '=', 'sales.id')
            ->whereBetween('sales.created_at', [$startDate, $endDate])
            ->select(
                'products.name',
                DB::raw('SUM(product_sales.quantity) as total_quantity'),
                DB::raw('SUM(product_sales.quantity * products.sale_value) as total_value')
            )
            ->groupBy('products.name')
            ->get();

        // Vendas por dia
        $salesByDay = DB::table('sales')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->select(
                DB::raw('DATE(created_at) as sale_date'),
                DB::raw('COUNT(id) as total_orders'),
                DB::raw('SUM(total) as total_value')
            )
            ->groupBy('sale_date')
            ->orderBy('sale_date', 'asc')
            ->get();

        // Destaques
        $bestSellingProduct = $this->getBestSellingProduct($startDate, $endDate);
        $bestSellingBrand = $this->getBestSellingBrand($startDate, $endDate);
        $highestRevenueDay = $this->getHighestRevenueDay($startDate, $endDate);

        // Análises calculadas
        $analysis = [
            'daily_avg' => $hasSales ? $totalSalesValue / $salesByDay->count() : 0,
            'days_without_sales' => $daysInPeriod - $salesByDay->count(),
            'days_in_period' => $daysInPeriod,
            'sales_efficiency' => $salesByDay->count() > 0 ? ($salesByDay->count() / $daysInPeriod) * 100 : 0,
            'avg_ticket' => $hasSales ? $totalSalesValue / $salesCount : 0,
            'top_product' => $groupedProducts->isNotEmpty()
                ? $groupedProducts->sortByDesc('total_quantity')->first()
                : null
        ];

        // Resumo executivo
        $summary = [
            'best_selling_product' => $bestSellingProduct ? $bestSellingProduct->name : 'N/A',
            'best_selling_brand' => $bestSellingBrand ? $bestSellingBrand->name : 'N/A',
            'highest_revenue_day' => $highestRevenueDay ? [
                'date' => \Carbon\Carbon::parse($highestRevenueDay->sale_date)->format('d/m/Y'),
                'revenue' => $highestRevenueDay->total_revenue,
            ] : ['date' => 'N/A', 'revenue' => 0],
            'total_sales' => $totalSalesValue,
            'total_orders' => $salesCount,
            'total_products' => $productsQuantity
        ];

        return PDF::loadView('reports.sales', [
            'sales' => $sales,
            'groupedProducts' => $groupedProducts,
            'groupedByBrand' => $groupedByBrand,
            'salesByDay' => $salesByDay,
            'summary' => $summary,
            'analysis' => $analysis,
            'period' => [
                'start' => $startDate->format('d/m/Y'),
                'end' => $endDate->format('d/m/Y'),
                'days' => $daysInPeriod
            ],
            'report_date' => now()->format('d/m/Y \à\s H:i')
        ])->stream('Relatório de Vendas Mensal.pdf');
    }

    // Métodos auxiliares
    private function getBestSellingProduct($startDate, $endDate)
    {
        return DB::table('product_sales')
            ->join('products', 'product_sales.product_id', '=', 'products.id')
            ->join('sales', 'product_sales.sale_id', '=', 'sales.id')
            ->whereBetween('sales.created_at', [$startDate, $endDate])
            ->select(
                'products.name',
                DB::raw('SUM(product_sales.quantity * products.sale_value) as total_value')
            )
            ->groupBy('products.name')
            ->orderByDesc('total_value')
            ->first();
    }

    private function getBestSellingBrand($startDate, $endDate)
    {
        return DB::table('product_sales')
            ->join('products', 'product_sales.product_id', '=', 'products.id')
            ->join('brands', 'products.brand_id', '=', 'brands.id')
            ->join('sales', 'product_sales.sale_id', '=', 'sales.id')
            ->whereBetween('sales.created_at', [$startDate, $endDate])
            ->select(
                'brands.name',
                DB::raw('SUM(product_sales.quantity) as total_quantity')
            )
            ->groupBy('brands.name')
            ->orderByDesc('total_quantity')
            ->first();
    }

    private function getHighestRevenueDay($startDate, $endDate)
    {
        return DB::table('sales')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->select(
                DB::raw('DATE(created_at) as sale_date'),
                DB::raw('SUM(total) as total_revenue')
            )
            ->groupBy('sale_date')
            ->orderByDesc('total_revenue')
            ->first();
    }
    
    public function generateCustomersReport()
    {
        // Obter todos os clientes ou filtrar conforme necessário
        $customers = Customer::with(['sales' => function ($query) {
            $query->select('id', 'customer_id', 'total', 'created_at');
        }])
            ->orderBy('name')
            ->get();
        // Estatísticas dos clientes
        $stats = [
            'total_customers' => $customers->count(),
            'active_customers' => $customers->filter(function ($customer) {
                return $customer->sales->count() > 0;
            })->count(),
            'top_spender' => $customers->sortByDesc(function ($customer) {
                return $customer->sales->sum('total');
            })->first(),
            'most_frequent' => $customers->sortByDesc(function ($customer) {
                return $customer->sales->count();
            })->first()
        ];

        return PDF::loadView('reports.customers', [
            'customers' => $customers,
            'stats' => $stats,
            'reportDate' => now()->format('d/m/Y H:i')
        ])
            ->stream('Relatório de Clientes.pdf');
    }
}
