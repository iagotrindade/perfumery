<?php

namespace App\Filament\Resources\CustomerResource\Widgets;

use Filament\Tables;
use App\Models\Customer;
use Filament\Widgets\TableWidget;
use Filament\Tables\Columns\TextColumn;


class CustomerSales extends TableWidget
{
    public ?Customer $record = null;

    protected static ?string $heading = 'Histórico de Vendas';

    protected int | string | array $columnSpan = 'full';

    protected function getTableQuery(): \Illuminate\Database\Eloquent\Builder
    {
        return $this->record->sales()->with('products')->getQuery();
    }

    protected function getTableColumns(): array
    {
        return [
            TextColumn::make('id')
                ->label('ID'),
            TextColumn::make('customer.name')
                ->label('Cliente')
                ->formatStateUsing(function ($record) {
                    // Acessa o cliente relacionado à venda
                    return $record->customer->name;
                }),
            TextColumn::make('due_date')
                ->label('Data de Vencimento')
                ->date('d/m/Y'),
            TextColumn::make('created_at')
                ->label('Data da Venda')
                ->date(),
            TextColumn::make('parcels')
                ->label('Parcelas')
                ->formatStateUsing(fn($state) => $state . 'x'),
            TextColumn::make('products.name')
                ->label('Produtos')
                ->formatStateUsing(function ($record) {
                    // Acessa os produtos relacionados à venda
                    return $record->products->map(function ($product) {
                        return $product->name . ' (' . $product->pivot->quantity . 'x)';
                    })->join(', ');
                })
                ->limit(40),
        ];
    }
}
