<?php

namespace App\Filament\Resources\CustomerResource\Widgets;

use App\Models\Customer;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Carbon;


class CustomerOverview extends BaseWidget
{
    protected function getStats(): array
    {
        $lastMonth = Carbon::now()->subMonth();
        $oneMonthAgo = Carbon::now()->subMonth()->startOfMonth();

        return [
            Stat::make('Todos', Customer::all()->count()),

            // Clientes com compras no último mês
            Stat::make('Compras recentes', Customer::whereHas('sales', function ($query) use ($lastMonth) {
                $query->where('created_at', '>=', $lastMonth);
            })->count()),

            // Clientes que não compram há mais de um mês
            Stat::make('Inativos', Customer::whereDoesntHave('sales', function ($query) use ($oneMonthAgo) {
                $query->where('created_at', '>=', $oneMonthAgo);
            })->count()),
        ];
    }
}
