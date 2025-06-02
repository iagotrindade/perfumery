<?php

namespace App\Filament\Resources\SaleResource\Pages;

use Carbon\Carbon;
use App\Models\Sale;
use App\Models\User;
use Filament\Actions;
use App\Models\Product;
use Filament\Forms\Form;
use Filament\Support\RawJs;
use Illuminate\Support\Facades\Auth;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Repeater;
use Filament\Tables\Columns\TextColumn;
use Illuminate\Database\Eloquent\Model;
use App\Filament\Resources\SaleResource;
use Filament\Forms\Components\TextInput;

use Filament\Notifications\Notification;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Wizard\Step;
use Filament\Resources\Pages\CreateRecord;


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
                        ->searchable()
                        ->required()
                        ->label('Cliente')
                        ->live()
                        ->afterStateUpdated(function (\Filament\Forms\Set $set, \Filament\Forms\Get $get, $state) {
                            if (!$state) return;
                            // Atualiza o nome do cliente no resumo
                            $customer = \App\Models\Customer::find($state);
                            if (!$customer) return;
                            $set('summary_name', $customer->name);
                        }),
                    Repeater::make('products')
                        ->label('Produtos')
                        ->relationship()
                        ->schema([
                            Select::make('product_id')
                                ->searchable()
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
                            $set('summary_installments', $installments);
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
                            $set('summary_installments', $installments);
                        }),

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
                                ->formatStateUsing(function ($state) {
                                    return number_format($state, 2, ',', '.');
                                })
                                ->prefix('R$')
                                ->numeric(),
                        ])
                        ->addable(false)
                        ->columns(3),
                    TextArea::make('description')
                        ->label('Observações')
                        ->placeholder('Observações sobre a venda')
                ]),
            Step::make('Resumo')
                ->description('Confira os dados da venda')
                ->schema([
                    TextInput::make('summary_name')
                        ->label('Cliente')
                        ->readOnly(),

                    TextInput::make('total')
                        ->numeric()
                        ->prefix('R$')
                        ->label('Valor Total')
                        ->readOnly(),

                    Repeater::make('summary_installments')
                        ->label('Parcelas')
                        ->schema([
                            TextInput::make('installment_number')
                                ->label('Nº')
                                ->disabled()
                                ->readOnly(),

                            DatePicker::make('due_date')
                                ->label('Vencimento')
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

        //BUsca o último sale criado
        $record = Sale::orderBy('id', 'desc')->first();

        // Itera sobre os registros da tabela pivot (ProductSale)
        foreach ($record->products as $productSale) {
            $product = Product::find($productSale->product_id); // Busca o produto relacionado
            if ($product) {
                $product->quantity -= $productSale->quantity; // Atualiza o estoque
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
