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
        // Lucro do mês
        $monthlyProfit = Sale::where('created_at', '>=', now()->startOfMonth())->sum('total');

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

        $totalMonthlyCost = Product::where('created_at', '>=', now()->startOfMonth())
            ->sum('cost_value');

        $productsSoldThisMonth = Product::whereHas('sale', function ($query) {
            $query->where('created_at', '>=', now()->startOfMonth());
        })->get();

        // Lucro = total vendido - total gasto
        $totalRevenue = $productsSoldThisMonth->sum(function ($product) {
            return $product->sale_value * $product->quantity;
        });

        $totalCost = $productsSoldThisMonth->sum(function ($product) {
            return $product->cost_value * $product->quantity;
        });

        $profit = $totalRevenue - $totalCost;

        return [
            Stat::make('Vendas este mês', $monthlyProfit)
                ->icon('heroicon-o-currency-dollar')
                ->description('Comparado a ontem')
                ->descriptionIcon($trendIcon)
                ->chart($chartData)
                ->color('success'),

            Stat::make('Custo de aquisições', $totalMonthlyCost)
                ->icon('heroicon-o-banknotes')
                ->description('Total gasto com aquisições de produtos este mês')
                ->color('warning'),

            Stat::make('Lucro bruto do mês', $profit)
                ->icon('heroicon-o-currency-dollar')
                ->description('Receita - custo dos produtos vendidos')
                ->descriptionIcon($profit >= 0 ? 'heroicon-o-arrow-trending-up' : 'heroicon-o-arrow-trending-down')
                ->color($profit >= 0 ? 'success' : 'danger')
        ];
    }
}
