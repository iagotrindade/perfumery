<?php

namespace App\Filament\Resources\SaleResource\Pages;

use Carbon\Carbon;
use App\Models\User;
use Filament\Actions;
use App\Models\Product;
use App\Models\ProductSale;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Model;
use App\Filament\Resources\SaleResource;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;

class EditSale extends EditRecord
{
    protected static string $resource = SaleResource::class;

    protected function afterSave(): void
    {
        $this->record->installments->each(function ($installment) {
            if ($installment->status === 'paid') {
                $installment->payment_date = Carbon::now()->toDateString();
            } else {
                $installment->payment_date = null;
            }
            $installment->save();
        });
    }

    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        if (!empty($data['partial_payment']) && $data['partial_payment'] != 0) {
            $partialPayment = $data['partial_payment'];

            // Filtra uma única vez as parcelas não pagas
            $openInstallments = $record->installments->where('status', '!=', 'paid');

            $installmentsCount = $openInstallments->count();

            if ($installmentsCount > 0) {
                $partialPaymentPerInstallment = $partialPayment / $installmentsCount;

                $openInstallments->each(function ($installment) use ($partialPaymentPerInstallment) {
                    $installment->amount -= $partialPaymentPerInstallment;
                    $installment->save();
                });

                $data['description'] = 'Pagamento parcial de R$' . $partialPayment.' no dia ' . now()->format('d/m/Y') . '. ' . $data['description'];
            }
        }

        // Atualiza os dados do record
        $record->update($data);

        // Enviar notificação para todos os usuários
        $recipients = User::all();
        $authUser = Auth::user();

        Notification::make()
            ->title('Venda editada')
            ->icon('heroicon-o-currency-dollar')
            ->body($authUser->name . ' editou a venda para o cliente ' . $record->customer->name . '.')
            ->success()
            ->sendToDatabase($recipients);

        return $record;
    }


    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make()->before(
                function () {
                    // Verificar os produtos vendidos e devolver a quantidade ao estoque

                    $record = $this->record;

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
            ),

            // Criar action que gere um extrato da venda em PDF

            Actions\Action::make('gerar-extrato')
                ->label('Baixar Extrato')
                ->icon('heroicon-o-arrow-down-tray')
                ->url(fn() => route('report.extract', $this->record->id))
                ->openUrlInNewTab()

        ];
    }
}
