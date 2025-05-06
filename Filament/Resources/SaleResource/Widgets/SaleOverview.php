<?php

namespace App\Filament\Resources\SaleResource\Widgets;

use App\Models\Sale;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\DB;

class SaleOverview extends BaseWidget
{
    protected function getStats(): array
    {
        return [
            Stat::make('Vendas', Sale::all()->where('created_at', '>=', now()->subMonth())->count())
                ->icon('heroicon-o-currency-dollar'),

            Stat::make('Produtos Vendidos', function () {
                return DB::table('product_sales')
                    ->join('sales', 'product_sales.sale_id', '=', 'sales.id')
                    ->where('sales.created_at', '>=', now()->subMonth())
                    ->sum('product_sales.quantity');
            })
                ->icon('heroicon-o-cube'),

            Stat::make('Valor Total de Vendas', Sale::all()->where('created_at', '>=', now()->subMonth())->sum('total'))
                ->icon('heroicon-o-currency-dollar')
        ];
    }
}
