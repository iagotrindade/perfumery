<?php

namespace App\Filament\Resources\SaleResource\Pages;

use App\Filament\Resources\SaleResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Filament\Resources\Components\Tab;
use Illuminate\Database\Eloquent\Builder;

class ListSales extends ListRecords
{
    protected static string $resource = SaleResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }

    protected function getHeaderWidgets(): array
    {
        return [
            SaleResource\Widgets\SaleOverview::class,
        ];
    }

    public function getTabs(): array
    {
        return [
            'all' => Tab::make('Todos'),

            'Em atraso' => Tab::make('Em atraso')
                ->modifyQueryUsing(
                    fn(Builder $query) =>
                    $query->whereHas(
                        'installments',
                        fn($q) =>
                        $q->where('status', 'overdue')
                    )
                ),

            'Cancelados' => Tab::make('Cancelados')
                ->modifyQueryUsing(
                    fn(Builder $query) =>
                    $query->whereDoesntHave(
                        'installments',
                        fn($q) =>
                        $q->where('status', 'overdue')
                    )->whereHas(
                        'installments',
                        fn($q) =>
                        $q->where('status', 'canceled')
                    )
                ),

            'Pendentes' => Tab::make('Pendentes')
                ->modifyQueryUsing(
                    fn(Builder $query) =>
                    $query->whereDoesntHave(
                        'installments',
                        fn($q) =>
                        $q->whereIn('status', ['overdue', 'canceled'])
                    )->whereHas(
                        'installments',
                        fn($q) =>
                        $q->where('status', 'pending')
                    )
                ),

            'Pagos' => Tab::make('Pagos')
                ->modifyQueryUsing(
                    fn(Builder $query) =>
                    $query->whereDoesntHave(
                        'installments',
                        fn($q) =>
                        $q->whereIn('status', ['pending', 'overdue', 'canceled'])
                    )->whereHas(
                        'installments',
                        fn($q) =>
                        $q->where('status', 'paid')
                    )
                ),
        ];
    }
}
