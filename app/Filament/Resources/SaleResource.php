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
use Filament\Tables\Columns\BadgeColumn;
use Illuminate\Database\Eloquent\Builder;
use Filament\Forms\Components\Placeholder;
use App\Filament\Resources\SaleResource\Pages;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\Resources\SaleResource\RelationManagers;
use App\Models\Customer;
use Filament\Tables\Actions\DeleteBulkAction;
use Filament\Tables\Actions\DeleteAction;
use Illuminate\Support\Facades\Auth;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\Page;
use App\Models\User;
use Filament\Tables\Columns\Summarizers\Sum;

class SaleResource extends Resource
{
    protected static ?string $model = Sale::class;

    protected static ?string $navigationIcon = 'heroicon-o-currency-dollar';

    protected static ?string $modelLabel = 'Vendas';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Section::make('Dados da Venda')
                ->schema([
                    TextInput::make('customer_id')
                        ->label('Cliente')
                        ->disabled()
                        ->readOnly()
                        ->formatStateUsing(
                            function ($state) {
                                $product = Customer::find($state);
                                return $product ? $product->name : 'Cliente não encontrado';
                            }
                        ),

                    TextInput::make('total')
                        ->numeric()
                        ->prefix('R$')
                        ->label('Valor Total')
                        ->disabled()
                        ->readOnly(), // impede edição manual, já que é calculado

                    Repeater::make('products')
                        ->label('Produtos')
                        ->relationship()
                        ->schema([
                            TextInput::make('product_id')
                                ->label('Produto')
                                ->readOnly()
                                ->formatStateUsing(
                                    function ($state) {
                                        $product = Product::find($state);
                                        return $product ? $product->name : 'Produto não encontrado';
                                    }
                                ),
                            TextInput::make('quantity')
                                ->label('Quantidade')
                                ->numeric()
                                ->default(1)
                                ->readOnly()
                        ])
                        ->columns(2)
                        ->addable(false)
                        ->deletable(false)
                        ->disabled(),

                    Repeater::make('installments')
                        ->relationship()
                        ->label('Parcelas')
                        ->live()
                        ->schema([
                            TextInput::make('installment_number')
                                ->label('Nº')
                                ->disabled()
                                ->readOnly(),

                            DatePicker::make('due_date')
                                ->disabled()
                                ->label('Vencimento'),

                            TextInput::make('amount')
                                ->label('Valor')
                                ->prefix('R$')
                                ->numeric()
                                ->disabled(),

                            Select::make('status')
                                ->label('Status')
                                ->options([
                                    'pending' => 'Pendente',
                                    'paid' => 'Pago',
                                    'canceled' => 'Cancelado',
                                    'overdue' => 'Atrasado',
                                ])
                                ->default('pending')
                                ->required(),
                        ])
                        ->columns(4)
                        ->addable(false)
                        ->deletable(false),
                ]),
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
                ->formatStateUsing(function ($record) {
                    $next = $record->installments
                        ->where('status', 'pending')
                        ->sortBy('due_date')
                        ->first();

                    return $next ? Carbon::parse($next->due_date)->format('d M Y') : 'Sem vencimento';
                }),

            TextColumn::make('products')
                ->formatStateUsing(
                    function ($state, $record) {
                        $count = 0;
                        foreach ($record->products as $productSale) {
                            $count += $productSale->quantity;
                        }
                        return $count;
                    }
                )
                ->label('Itens')
                ->sortable()
                ->searchable(),

            TextColumn::make('total')
                ->label('Valor')
                ->prefix('R$')
                ->sortable()
                ->searchable()
                ->formatStateUsing(function ($state) {
                    return number_format($state, 2, ',', '.');
                })
                ->summarize(
                    Sum::make()
                        ->money('BRL')
                        ->label('Total')
                ),

            TextColumn::make('installments_count')
                ->counts('installments')
                ->label('Parcelas')
                ->sortable()
                ->searchable()
                ->formatStateUsing(function ($state) {
                    return $state . 'x';
                }),

            TextColumn::make('status_geral')
                ->label('Situação')
                ->sortable()
                ->searchable()
                ->badge()
                ->color(function ($state) {
                    return match ($state) {
                        'Pendente' => 'gray',
                        'Pago' => 'success',
                        'Cancelado' => 'danger',
                        'Atrasado' => 'danger',
                        default => 'secondary',
                    };
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
        ])
            ->filters([
                Tables\Filters\Filter::make('created_at')
                    ->form([
                        Forms\Components\DatePicker::make('created_from')->label('De'),
                        Forms\Components\DatePicker::make('created_until')->label('Até'),
                    ])
                    ->query(function (Builder $query, array $data) {
                        return $query
                            ->when($data['created_from'], fn($query) => $query->whereDate('created_at', '>=', $data['created_from']))
                            ->when($data['created_until'], fn($query) => $query->whereDate('created_at', '<=', $data['created_until']));
                    })
                    ->label('Data de Criação'),

                // Filtro de Status através das parcelas (installments)
                Tables\Filters\Filter::make('status_geral')
                    ->form([
                        Forms\Components\Select::make('status')
                            ->label('Situação')
                            ->options([
                                'pending' => 'Pendente',
                                'paid' => 'Pago',
                                'overdue' => 'Atrasado',
                                'canceled' => 'Cancelado',
                            ]),
                    ])
                    ->query(function (Builder $query, array $data) {
                        if (!empty($data['status'])) {
                            $query->whereHas('installments', function (Builder $subQuery) use ($data) {
                                $subQuery->where('status', $data['status']);
                            });
                        }
                    })
                    ->label('Situação')
            ])
            ->actions([
                //Mandar o usuário para a página editsalestatus ao inves da edição padrão
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make()
                    ->before(
                        function ($record) {
                            // Verificar os produtos vendidos e devolver a quantidade ao estoque

                            foreach ($record->products as $productSale) {
                                $product = Product::find($productSale->product_id); // Busca o produto relacionado
                                if ($product) {
                                    $product->quantity += $productSale->quantity; // Atualiza o estoque
                                    $product->save();
                                }
                            }

                            // Enviar notificação para todos os usuários
                            $recipients = User::all();
                            $authUser = Auth::user();

                            Notification::make()
                                ->title('Venda excluída')
                                ->icon('heroicon-o-currency-dollar')
                                ->body($authUser->name . ' excluiu a venda para o cliente ' . $record->customer->name . '.')
                                ->danger()
                                ->sendToDatabase($recipients);
                        }
                    )
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
