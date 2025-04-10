<?php

namespace App\Filament\Resources;

use Filament\Forms;
use Filament\Tables;
use App\Models\Product;
use Filament\Forms\Form;
use Filament\Tables\Table;
use Filament\Resources\Resource;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Support\RawJs;
use Illuminate\Database\Eloquent\Builder;
use App\Filament\Resources\ProductResource\Pages;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\Resources\ProductResource\RelationManagers;

class ProductResource extends Resource
{
    protected static ?string $model = Product::class;

    protected static ?string $navigationIcon = 'heroicon-o-squares-plus';

    protected static ?string $modelLabel = 'Produtos';

    public static function form(Form $form): Form
    {
        return $form->schema([
            TextInput::make('name')->required(),

            TextInput::make('quantity')
                ->label('Quantidade em Estoque')
                ->numeric()
                ->minValue(0)
                ->required(),

            TextInput::make('cost_value')
                ->label('Valor de Custo')
                ->mask(RawJs::make(<<<'JS'
                    $money($input, ',')
                JS))
                ->stripCharacters([','])
                ->numeric()
                ->inputMode('decimal'),

            TextInput::make('sale_value')
                ->label('Valor de Venda')
                ->mask(RawJs::make(<<<'JS'
                    $money($input, ',')
                JS))
                ->stripCharacters([','])
                ->numeric()
                ->inputMode('decimal'),
            Textarea::make('description')->rows(3),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            Tables\Columns\TextColumn::make('name'),
            Tables\Columns\TextColumn::make('quantity'),
            Tables\Columns\TextColumn::make('sale_value')->money('BRL'),
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
            'index' => Pages\ListProducts::route('/'),
            'create' => Pages\CreateProduct::route('/create'),
            'edit' => Pages\EditProduct::route('/{record}/edit'),
        ];
    }
}
