<?php

namespace App\Filament\Widgets;

use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use App\Models\Sale;
use App\Models\Product;
use Illuminate\Support\Facades\DB;

class FinancialOverview extends BaseWidget
{
    protected static ?string $pollingInterval = '10s';

    protected function getStats(): array
    {
        $startOfMonth = now()->startOfMonth();
        $daysInMonthSoFar = now()->day;

        // === Cálculo do Lucro real baseado nas vendas ===
        $sales = Sale::where('created_at', '>=', $startOfMonth)->get();
        $monthlyProfit = 0;

        foreach ($sales as $sale) {
            $productSales = DB::table('product_sales')
                ->where('sale_id', $sale->id)
                ->get();

            $totalCost = 0;

            foreach ($productSales as $productSale) {
                $product = Product::find($productSale->product_id);

                if ($product) {
                    $totalCost += $product->cost_value * $productSale->quantity;
                }
            }

            $monthlyProfit += $sale->total - $totalCost;
        }

        // Total bruto de vendas do mês
        $monthlyGross = $sales->sum('total');

        // Total gasto com aquisição de novos produtos este mês
        $totalMonthlyCost = Product::where('created_at', '>=', $startOfMonth)
            ->get()
            ->sum(function ($product) {
                return $product->cost_value * $product->quantity;
            });

        // Vendas de hoje e ontem
        $today = Sale::whereDate('created_at', now())->sum('total');
        $yesterday = Sale::whereDate('created_at', now()->subDay())->sum('total');

        $trendIcon = $today >= $yesterday
            ? 'heroicon-o-arrow-trending-up'
            : 'heroicon-o-arrow-trending-down';

        // Gráfico com valores por dia do mês
        $chartData = collect(range(1, $daysInMonthSoFar))->map(function ($day) use ($startOfMonth) {
            $date = $startOfMonth->copy()->day($day);
            return Sale::whereDate('created_at', $date)->sum('total');
        })->toArray();

        return [
            Stat::make('Lucro do mês', 'R$ ' . number_format($monthlyProfit, 2, ',', '.'))
                ->icon('heroicon-o-chart-bar')
                ->description('Margem de lucro baseada nas vendas')
                ->color($monthlyProfit >= 0 ? 'success' : 'danger'),

            Stat::make('Vendas este mês', 'R$ ' . number_format($monthlyGross, 2, ',', '.'))
                ->icon('heroicon-o-currency-dollar')
                ->description('Comparado a ontem')
                ->descriptionIcon($trendIcon)
                ->chart($chartData)
                ->color('success'),

            Stat::make('Custo de aquisições', 'R$ ' . number_format($totalMonthlyCost, 2, ',', '.'))
                ->icon('heroicon-o-banknotes')
                ->description('Total gasto com aquisições de produtos este mês')
                ->color('warning'),
        ];
    }
}
