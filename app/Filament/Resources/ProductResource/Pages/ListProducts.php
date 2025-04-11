<?php

namespace App\Filament\Resources\ProductResource\Pages;

use Carbon\Carbon;
use Filament\Actions;
use Filament\Resources\Components\Tab;
use Filament\Resources\Pages\ListRecords;
use App\Filament\Resources\ProductResource;
use Illuminate\Database\Eloquent\Builder;

class ListProducts extends ListRecords
{
    protected static string $resource = ProductResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }

    protected function getHeaderWidgets(): array
    {
        return [
            ProductResource\Widgets\ProductsOverview::class,
        ];
    }

    public function getTabs(): array
    {
        $lastMonth = Carbon::now()->subMonth();
        $oneMonthAgo = Carbon::now()->subMonth()->startOfMonth();

        return [
            'all' => Tab::make('Todos'),

            // Tab para clientes com compras no último mês
            'Mais vendidos' => Tab::make('Mais vendidos')
                ->modifyQueryUsing(fn(Builder $query) => $query->whereHas('sales', function ($query) use ($lastMonth) {
                    $query->where('sales.created_at', '>=', $lastMonth);
                })),
            // Tab para clientes que não compram há mais de um mês
            'Menos vendidos' => Tab::make('Menos vendidos')
                ->modifyQueryUsing(fn(Builder $query) => $query->whereDoesntHave('sales', function ($query) use ($oneMonthAgo) {
                    $query->where('sales.created_at', '>=', $oneMonthAgo);
                })),
            'Alta quantidade' => Tab::make('Alta quantidade')
                ->modifyQueryUsing(fn(Builder $query) => $query->where('quantity', '>', 20)),
            'Baixa quantidade' => Tab::make('Baixa quantidade')
                ->modifyQueryUsing(fn(Builder $query) => $query->where('quantity', '<=', 5)),
            'Fora de estoque' => Tab::make('Fora de estoque')
                ->modifyQueryUsing(fn(Builder $query) => $query->where('quantity', '=', 0)),
        ];
    }
}
