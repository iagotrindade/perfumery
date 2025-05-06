<?php

namespace App\Filament\Resources\CustomerResource\Pages;

use App\Models\Customer;
use Filament\Tables\Components\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ViewColumn;
use Filament\Tables\Contracts\HasTable;
use Filament\Resources\Pages\ViewRecord;
use Filament\Tables\Columns\Layout\Grid;
use Filament\Tables\Columns\Layout\Split;
use Filament\Tables\Columns\Layout\Stack;
use Filament\Infolists\Components\Section;
use App\Filament\Resources\CustomerResource;
use Filament\Infolists\Components\TextEntry;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Infolists\Components\RepeatableEntry;

class ViewCustomer extends ViewRecord implements HasTable
{
    use InteractsWithTable;

    protected static string $resource = CustomerResource::class;

    protected function getFooterWidgets(): array
    {
        return [
            CustomerResource\Widgets\CustomerSales::class,
        ];
    }

    protected function getHeaderActions(): array
    {
        return [];
    }

    protected function getTableQuery()
    {
        return $this->record->sales()->getQuery();
    }

    protected function getTableColumns(): array
    {
        return [
            

        ];
    }

    protected function getInfolistSchema(): array
    {
        return [
            Section::make('Dados do Cliente')
                ->schema([
                    Grid::make(2)
                        ->schema([
                            TextEntry::make('name')->label('Nome'),
                            TextEntry::make('email')->label('Email')->default('Não informado'),
                            TextEntry::make('phone')->label('Telefone'),
                            TextEntry::make('description')->label('Descrição'),
                        ]),
                ]),

            Section::make('Endereços')
                ->schema([
                    RepeatableEntry::make('addresses')
                        ->label('')
                        ->schema([
                            TextEntry::make('label')->label('Rótulo'),
                            TextEntry::make('street')->label('Rua'),
                            TextEntry::make('number')->label('Número'),
                            TextEntry::make('complement')->label('Complemento'),
                            TextEntry::make('neighborhood')->label('Bairro'),
                            TextEntry::make('city')->label('Cidade'),
                            TextEntry::make('state')->label('Estado'),
                            TextEntry::make('postal_code')->label('CEP'),
                            TextEntry::make('country')->label('País'),
                            TextEntry::make('is_primary')
                                ->label('Principal')
                                ->formatStateUsing(fn($state) => $state ? 'Sim' : 'Não'),
                        ])
                        ->columns(2),
                ]),
        ];
    }
}
