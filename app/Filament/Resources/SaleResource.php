<?php

namespace App\Filament\Resources;

use Filament\Forms;
use App\Models\Sale;
use Filament\Tables;
use App\Models\Product;
use Filament\Forms\Form;
use Filament\Tables\Table;
use Filament\Resources\Resource;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Repeater;
use Filament\Tables\Columns\TextColumn;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\DatePicker;
use Illuminate\Database\Eloquent\Builder;
use Filament\Forms\Components\Placeholder;
use App\Filament\Resources\SaleResource\Pages;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\Resources\SaleResource\RelationManagers;

class SaleResource extends Resource
{
    protected static ?string $model = Sale::class;

    protected static ?string $navigationIcon = 'heroicon-o-currency-dollar';

    protected static ?string $modelLabel = 'Vendas';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Select::make('customer_id')
                ->relationship('customer', 'name')
                ->searchable()
                ->required()
                ->label('Cliente'),

            DatePicker::make('due_date')->label('Data de Vencimento')->required(),

            TextInput::make('parcels')
                ->label('Número de Parcelas')
                ->numeric()
                ->required()
                ->default(1),

            Repeater::make('products')
                ->label('Produtos da Venda')
                ->relationship()
                ->schema([
                    Select::make('product_id')
                        ->label('Produto')
                        ->options(Product::all()->pluck('name', 'id'))
                        ->searchable()
                        ->required(),

                    TextInput::make('quantity')
                        ->label('Quantidade')
                        ->numeric()
                        ->default(1)
                        ->required(),
                ])
                ->defaultItems(1)
                ->columns(2)
                ->collapsible(),

            Placeholder::make('total')
                ->label('Total da Venda')
                ->content(function ($record) {
                    if (! $record) return 'R$ 0,00';

                    $total = 0;
                    foreach ($record->products as $product) {
                        $total += $product->pivot->quantity * $product->sale_value;
                    }
                    return 'R$ ' . number_format($total, 2, ',', '.');
                }),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            TextColumn::make('customer.name')->label('Cliente'),
            TextColumn::make('due_date')->label('Vencimento')->date(),
            TextColumn::make('parcels')->label('Parcelas'),
            TextColumn::make('products_count')->counts('products')->label('Itens'),
        ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListSales::route('/'),
            'create' => Pages\CreateSale::route('/create'),
            'edit' => Pages\EditSale::route('/{record}/edit'),
        ];
    }
}
