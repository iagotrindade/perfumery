<?php

namespace App\Filament\Resources\ProductResource\Widgets;

use App\Models\Product;
use Illuminate\Support\Carbon;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;

class ProductsOverview extends BaseWidget
{
    protected function getStats(): array
    {
        $lastMonth = Carbon::now()->subMonth();
        $oneMonthAgo = Carbon::now()->subMonth()->startOfMonth();

        return [
            Stat::make('Todos', Product::all()->count())
                ->icon('heroicon-o-squares-plus'),

            // Clientes com compras no último mês
            Stat::make('Valor de Custo', Product::where('created_at', '>=', $lastMonth)->sum('cost_value'))
                ->icon('heroicon-o-currency-dollar'),

            // Clientes que não compram há mais de um mês
            Stat::make('Valor de Venda', Product::where('created_at', '>=', $oneMonthAgo)->sum('sale_value'))
                ->icon('heroicon-o-currency-dollar'),
        ];
    }
}
