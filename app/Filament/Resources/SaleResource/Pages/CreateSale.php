<?php

namespace App\Filament\Resources\SaleResource\Pages;

use Carbon\Carbon;
use App\Models\Sale;
use App\Models\User;
use App\Models\Product;
use Filament\Actions;
use Filament\Forms\Form;
use Illuminate\Support\Facades\Auth;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Wizard\Step;
use Filament\Resources\Pages\CreateRecord;
use App\Filament\Resources\SaleResource;

class CreateSale extends CreateRecord
{
    protected static string $resource = SaleResource::class;

    use CreateRecord\Concerns\HasWizard;

    protected function getSteps(): array
    {
        return [
            Step::make('Cliente e Produtos')
                ->description('Selecione o cliente e os produtos')
                ->schema([
                    Select::make('customer_id')
                        ->relationship('customer', 'name')
                        ->prefixIcon('heroicon-m-user-circle')
                        ->searchable()
                        ->required()
                        ->label('Cliente')
                        ->live()
                        ->afterStateUpdated(function ($set, $get, $state) {
                            if (!$state) return;

                            $customer = \App\Models\Customer::with('sales.installments')->find($state);
                            if (!$customer) return;

                            $set('summary_name', $customer->name);

                            $totalPending = 0;
                            foreach ($customer->sales as $sale) {
                                foreach ($sale->installments as $installment) {
                                    if (in_array($installment->status, ['pending', 'overdue'])) {
                                        $totalPending += floatval($installment->amount);
                                    }
                                }
                            }

                            if (isset($customer->sale_limit) && $customer->sale_limit != 0) {
                                $availableLimit = floatval($customer->sale_limit) - $totalPending;
                                $set('available_limit', number_format($availableLimit, 2, '.', ''));
                            } else {
                                $set('available_limit', 'Cliente não possui limite cadastrado!');
                            }
                        }),

                    TextInput::make('available_limit')
                        ->label('Limite disponível')
                        ->prefix('R$')
                        ->disabled()
                        ->formatStateUsing(fn($state) => number_format($state, 2, ',', '.')),

                    Repeater::make('products')
                        ->label('Produtos')
                        ->relationship()
                        ->schema([
                            Select::make('product_id')
                                ->searchable()
                                ->label('Produto')
                                ->prefixIcon('heroicon-m-squares-plus')
                                ->options(Product::all()->pluck('name', 'id'))
                                ->live()
                                ->required(),

                            TextInput::make('quantity')
                                ->label('Quantidade')
                                ->numeric()
                                ->default(1)
                                ->required()
                                ->live(),
                        ])
                        ->columns(2)
                        ->required()
                        ->live()
                        ->afterStateUpdated(function ($set, $get, $state) {
                            if (!$state) return;

                            $total = 0;
                            foreach ($state as $item) {
                                if (!isset($item['product_id'], $item['quantity'])) continue;
                                $product = Product::find($item['product_id']);
                                if (!$product) continue;
                                $total += $product->sale_value * (int) $item['quantity'];
                            }

                            $set('raw_total', $total);
                            $discount = floatval($get('discount') ?? 0);
                            $finalTotal = max($total - $discount, 0);
                            $set('total', $finalTotal);

                            // Atualiza parcelas
                            $count = (int) $get('installments_count');
                            if ($count && $finalTotal) {
                                $dueDate = Carbon::now()->addMonth();
                                $amount = round($finalTotal / $count, 2);

                                $installments = [];
                                for ($i = 0; $i < $count; $i++) {
                                    $installments[] = [
                                        'installment_number' => $i + 1,
                                        'due_date' => $dueDate->copy()->addMonths($i)->toDateString(),
                                        'amount' => $amount,
                                    ];
                                }

                                $set('installments', $installments);
                                $set('summary_installments', $installments);
                            }
                        }),
                ]),

            Step::make('Pagamento e Parcelamento')
                ->description('Selecione o parcelamento')
                ->schema([
                    TextInput::make('installments_count')
                        ->numeric()
                        ->minValue(1)
                        ->default(1)
                        ->label('Número de Parcelas')
                        ->live()
                        ->afterStateUpdated(function ($set, $get, $state) {
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
                            $set('summary_installments', $installments);
                        }),

                    Repeater::make('installments')
                        ->relationship()
                        ->label('Parcelas')
                        ->live()
                        ->schema([
                            TextInput::make('installment_number')->label('Nº')->readOnly(),
                            DatePicker::make('due_date')->label('Vencimento'),
                            TextInput::make('amount')->label('Valor')->prefix('R$')->numeric()
                                ->formatStateUsing(fn($state) => number_format($state, 2, ',', '.')),
                        ])
                        ->addable(false)
                        ->columns(3),

                    TextInput::make('discount')
                        ->label('Desconto')
                        ->prefix('R$')
                        ->default(0)
                        ->afterStateUpdated(function ($set, $get, $state) {
                            $rawTotal = floatval($get('raw_total') ?? 0);
                            $discount = floatval($state);
                            $finalTotal = max($rawTotal - $discount, 0);
                            $set('total', $finalTotal);

                            $count = (int) $get('installments_count');
                            if ($count && $finalTotal) {
                                $dueDate = Carbon::now()->addMonth();
                                $amount = round($finalTotal / $count, 2);

                                $installments = [];
                                for ($i = 0; $i < $count; $i++) {
                                    $installments[] = [
                                        'installment_number' => $i + 1,
                                        'due_date' => $dueDate->copy()->addMonths($i)->toDateString(),
                                        'amount' => $amount,
                                    ];
                                }

                                $set('installments', $installments);
                                $set('summary_installments', $installments);
                                $set('discount_applied', $discount);
                            }
                        }),

                    TextArea::make('description')->label('Observações')->placeholder('Observações sobre a venda'),
                ]),

            Step::make('Resumo')
                ->description('Confira os dados da venda')
                ->schema([
                    TextInput::make('summary_name')
                        ->label('Cliente')
                        ->prefixIcon('heroicon-m-user-circle')
                        ->readOnly(),

                    TextInput::make('raw_total')
                        ->numeric()
                        ->prefix('R$')
                        ->label('Total Bruto')
                        ->readOnly()
                        ->formatStateUsing(fn($state) => number_format($state, 2, ',', '.')),

                    TextInput::make('discount_applied')
                        ->default(0)
                        ->prefix('R$')
                        ->label('Desconto Aplicado')
                        ->readOnly()
                        ->formatStateUsing(fn($state) => number_format($state, 2, ',', '.')),

                    TextInput::make('total')
                        ->numeric()
                        ->prefix('R$')
                        ->label('Total com Desconto')
                        ->readOnly()
                        ->formatStateUsing(fn($state) => number_format($state, 2, ',', '.')),

                    Repeater::make('summary_installments')
                        ->label('Parcelas')
                        ->schema([
                            TextInput::make('installment_number')
                                ->label('Nº')
                                ->prefixIcon('heroicon-m-numbered-list')
                                ->disabled()
                                ->readOnly(),

                            DatePicker::make('due_date')
                                ->label('Vencimento')
                                ->prefixIcon('heroicon-m-calendar-days')
                                ->disabled()
                                ->readOnly(),

                            TextInput::make('amount')
                                ->label('Valor')
                                ->prefix('R$')
                                ->disabled()
                                ->readOnly(),
                        ])
                        ->addable(false)
                        ->deletable(false)
                        ->columns(3),
                ])
        ];
    }

    protected function afterCreate(): void
    {
        $authUser = Auth::user();
        $recipients = User::all();

        $record = Sale::orderBy('id', 'desc')->first();

        foreach ($record->products as $productSale) {
            $product = Product::find($productSale->product_id);
            if ($product) {
                $product->quantity -= $productSale->quantity;
                $product->save();
            }
        }

        Notification::make()
            ->title('Venda realizada')
            ->icon('heroicon-o-currency-dollar')
            ->body($authUser->name . ' criou a venda para o cliente ' . $record->customer->name . '.')
            ->success()
            ->sendToDatabase($recipients);
    }
}
