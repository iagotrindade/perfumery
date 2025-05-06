<?php

namespace App\Filament\Resources\CustomerResource\Pages;

use App\Filament\Resources\CustomerResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;
use Filament\Resources\Components\Tab;
use Illuminate\Support\Carbon;

class ListCustomers extends ListRecords
{
    protected static string $resource = CustomerResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }

    protected function getHeaderWidgets(): array
    {
        return [
            CustomerResource\Widgets\CustomerOverview::class,
        ];
    }

    public function getTabs(): array
    {
        $lastMonth = Carbon::now()->subMonth();
        $oneMonthAgo = Carbon::now()->subMonth()->startOfMonth();

        return [
            'all' => Tab::make('Todos'),

            // Tab para clientes com compras no último mês
            'Compras recentes' => Tab::make('Compras recentes')
                ->modifyQueryUsing(fn(Builder $query) => $query->whereHas('sales', function ($query) use ($lastMonth) {
                    $query->where('created_at', '>=', $lastMonth);
                })),

            // Tab para clientes que não compram há mais de um mês
            'Inativos' => Tab::make('Inativos')
                ->modifyQueryUsing(fn(Builder $query) => $query->whereDoesntHave('sales', function ($query) use ($oneMonthAgo) {
                    $query->where('created_at', '>=', $oneMonthAgo);
                })),

            'Melhores clientes' => Tab::make('Melhores clientes')
                ->modifyQueryUsing(function (Builder $query) {
                    $query->withCount('sales')
                        ->orderByDesc('sales_count');
                })
        ];
    }
}
