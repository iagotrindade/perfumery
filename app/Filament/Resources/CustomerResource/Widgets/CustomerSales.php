<?php

namespace App\Filament\Resources\CustomerResource\Widgets;

use Filament\Tables;
use App\Models\Customer;
use Filament\Widgets\TableWidget;
use Filament\Tables\Columns\TextColumn;
use Illuminate\Contracts\View\View;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Support\Carbon;


class CustomerSales extends TableWidget
{
    public ?Customer $record = null;

    protected static ?string $heading = 'Histórico de Vendas';

    protected int | string | array $columnSpan = 'full';

    protected function getTableQuery(): \Illuminate\Database\Eloquent\Builder
    {
        return $this->record->sales()->with('products')->getQuery();
    }

    protected function getTableHeaderActions(): array
    {
        return [
            Tables\Actions\Action::make('Extrato de vendas')
                ->url(fn() => route('report.customer.extract', $this->record->id))
                ->icon('heroicon-o-arrow-down-tray')
                ->openUrlInNewTab(),
        ];
    }

    protected function getTableColumns(): array
    {
        return [
            TextColumn::make('id')
                ->label('ID'),
            TextColumn::make('customer.name')
                ->label('Cliente')
                ->formatStateUsing(fn($record) => $record->customer->name),
            TextColumn::make('created_at')
                ->label('Data da Venda')
                ->dateTime('d/m/Y'),
            TextColumn::make('installments.due_date')
                ->label('Próximo vencimento')
                ->sortable()
                ->formatStateUsing(function ($record) {
                    $next = $record->installments
                        ->where('status', 'pending')
                        ->sortBy('due_date')
                        ->first();

                    return $next ? Carbon::parse($next->due_date)->format('d M Y') : 'Sem vencimento';
                }),
            TextColumn::make('installments')
                ->label('Parcelas')
                ->formatStateUsing(fn($record) => $record->installments->count() . 'x'),
            TextColumn::make('total')
                ->label('Valor')
                ->prefix('R$')
                ->formatStateUsing(fn($state) => number_format($state, 2, ',', '.')),
            TextColumn::make('status_geral')
                ->label('Situação')
                ->badge()
                ->color(fn($state) => match ($state) {
                    'Pendente' => 'gray',
                    'Pago' => 'success',
                    'Cancelado' => 'danger',
                    'Atrasado' => 'danger',
                    default => 'secondary',
                })
                ->getStateUsing(function ($record) {
                    $statuses = $record->installments->pluck('status')->toArray();

                    if (in_array('overdue', $statuses)) {
                        return 'Atrasado';
                    }

                    if (in_array('canceled', $statuses)) {
                        return 'Cancelado';
                    }

                    if (in_array('pending', $statuses)) {
                        return 'Pendente';
                    }

                    if (count($statuses) > 0 && count(array_unique($statuses)) === 1 && $statuses[0] === 'paid') {
                        return 'Pago';
                    }

                    return 'Desconhecido';
                }),
            Tables\Columns\TextColumn::make('Visualizar')
                ->icon('heroicon-m-eye')
                ->url(fn($record) => route('filament.admin.resources.sales.edit', ['record' => $record->id]))
                ->tooltip('Editar venda')
                ->default('Visualizar')
                ->openUrlInNewTab(false),
        ];
    }
}
