<?php

namespace App\Filament\Widgets;

use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use App\Models\Sale;
use App\Models\Product;

class FinancialOverview extends BaseWidget
{
    protected static ?string $pollingInterval = '10s';

    protected function getStats(): array
    {
        // Total de vendas do mês
        $monthlyProfit = Sale::where('created_at', '>=', now()->startOfMonth())->sum('total');

        // Total gasto com aquisições de produtos no mês
        $totalMonthlyCost = Product::where('created_at', '>=', now()->startOfMonth())
            ->get()
            ->sum(function ($product) {
                return $product->cost_value * $product->quantity;
            });

        // Cálculo do lucro do mês
        $monthlyNetProfit = $monthlyProfit - $totalMonthlyCost;

        // Lucro de hoje e ontem
        $today = Sale::whereDate('created_at', now())->sum('total');
        $yesterday = Sale::whereDate('created_at', now()->subDay())->sum('total');

        // Ícone de tendência
        $trendIcon = $today >= $yesterday
            ? 'heroicon-o-arrow-trending-up'
            : 'heroicon-o-arrow-trending-down';

        // Gerar gráfico com todos os dias do mês até hoje
        $startOfMonth = now()->startOfMonth();
        $daysInMonthSoFar = now()->day;

        $chartData = collect(range(1, $daysInMonthSoFar))->map(function ($day) use ($startOfMonth) {
            $date = $startOfMonth->copy()->day($day);
            return Sale::whereDate('created_at', $date)->sum('total');
        })->toArray();

        return [
            Stat::make('Lucro do mês', 'R$ ' . number_format($monthlyNetProfit, 2, ',', '.'))
                ->icon('heroicon-o-chart-bar')
                ->description('Lucro líquido do mês')
                ->color($monthlyNetProfit >= 0 ? 'success' : 'danger'),

            Stat::make('Vendas este mês', 'R$ ' . number_format($monthlyProfit, 2, ',', '.'))
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
