<?php

namespace App\Filament\Resources;

use Carbon\Carbon;
use Filament\Forms;
use App\Models\Sale;
use Filament\Tables;
use App\Models\Product;
use Filament\Forms\Form;
use Filament\Tables\Table;
use Filament\Resources\Resource;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Section;
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
            Section::make('Produtos da Venda')
                ->schema([
                    Repeater::make('products')
                        ->label('Produtos')
                        ->relationship()
                        ->schema([
                            Select::make('product_id')
                                ->label('Produto')
                                ->options(Product::all()->pluck('name', 'id'))
                                ->live()
                                ->required(),
                            TextInput::make('quantity')
                                ->label('Quantidade')
                                ->numeric()
                                ->default(1)
                                ->live()
                                ->required()
                        ])
                        ->columns(2)
                        ->required()
                        ->afterStateUpdated(function (\Filament\Forms\Set $set, \Filament\Forms\Get $get, ?array $state) {
                            if (!$state) return;

                            $total = 0;

                            foreach ($state as $item) {
                                if (!isset($item['product_id']) || !isset($item['quantity'])) {
                                    continue;
                                }

                                $product = Product::find($item['product_id']);
                                if (!$product) continue;

                                $total += $product->sale_value * (int) $item['quantity'];
                            }

                            // Atualiza o total
                            $set('total', number_format($total, 2, '.', ''));

                            // Atualiza parcelas também
                            $count = (int) $get('installments_count');
                            if (!$count || !$total) return;

                            $dueDate = Carbon::now()->addMonth();
                            $amount = round($total / $count, 2);

                            $installments = [];
                            for ($i = 0; $i < $count; $i++) {
                                $installments[] = [
                                    'installment_number' => $i + 1,
                                    'due_date' => $dueDate->copy()->addMonths($i)->toDateString(),
                                    'amount' => $amount,
                                ];
                            }

                            $set('installments', $installments);
                        }),
                ]),
            Section::make('Dados da Venda')
                ->schema([
                    Select::make('customer_id')
                        ->relationship('customer', 'name')
                        ->searchable()
                        ->required()
                        ->label('Cliente'),

                    TextInput::make('total')
                        ->numeric()
                        ->prefix('R$')
                        ->required()
                        ->label('Valor Total')
                        ->readOnly(), // impede edição manual, já que é calculado

                    TextInput::make('installments_count')
                        ->numeric()
                        ->minValue(1)
                        ->default(1)
                        ->label('Número de Parcelas')
                        ->live()
                        ->afterStateUpdated(function (\Filament\Forms\Set $set, \Filament\Forms\Get $get, $state) {
                            if (!$state || !$get('total')) return;

                            $count = (int) $state;
                            $total = floatval($get('total'));
                            $dueDate = Carbon::now()->addMonth();
                            $amount = round($total / $count, 2);

                            $installments = [];
                            for ($i = 0; $i < $count; $i++) {
                                $installments[] = [
                                    'installment_number' => $i + 1,
                                    'due_date' => $dueDate->copy()->addMonths($i)->toDateString(),
                                    'amount' => $amount,
                                ];
                            }

                            $set('installments', $installments);
                        }),
                ]),

            Section::make('Dados das Parcelas')
                ->schema([
                    Repeater::make('installments')
                        ->relationship()
                        ->label('Parcelas')
                        ->live()
                        ->schema([
                            TextInput::make('installment_number')
                                ->label('Nº')
                                ->readOnly(),

                            DatePicker::make('due_date')
                                ->label('Vencimento'),

                            TextInput::make('amount')
                                ->label('Valor')
                                ->prefix('R$')
                                ->numeric(),
                        ])
                        ->columns(3)

                ])
        ]);
    }


    public static function table(Table $table): Table
    {
        return $table->columns([
            TextColumn::make('customer.name')
                ->label('Cliente')
                ->searchable()
                ->sortable(),
            TextColumn::make('installments.due_date')
                ->label('Próximo vencimento')
                ->sortable()
                ->formatStateUsing(function ($state, $record) {
                    $next = $record->installments
                        ->where('status', 'pending')
                        ->sortBy('due_date')
                        ->first();

                    return $next ? Carbon::parse($next->due_date)->format('d M Y') : 'Sem vencimento';
                }),

            TextColumn::make('products_count')
                ->counts('products')
                ->label('Itens')
                ->sortable()
                ->searchable(),
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
