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

            // Pega as parcelas em aberto ordenadas por vencimento mais próximo
            $openInstallments = $record->installments
                ->where('status', '!=', 'paid')
                ->sortBy('due_date')
                ->values();

            if ($openInstallments->isNotEmpty()) {
                $first = $openInstallments->first();
                $firstOriginalAmount = $first->amount;

                // Marca a primeira como paga, independentemente de ter sido integralmente quitada
                $first->update([
                    'amount' => $partialPayment,
                    'status' => 'paid',
                    'payment_date' => now()->toDateString(),
                ]);

                $openInstallments = $openInstallments->slice(1); // Remove a primeira

                $remainingCount = $openInstallments->count();

                if ($remainingCount > 0) {
                    $diferenca = $firstOriginalAmount - $partialPayment;

                    // Se diferença > 0 → cliente pagou menos, dilui acréscimo nas próximas
                    // Se diferença < 0 → cliente pagou mais, dilui desconto nas próximas
                    $installmentAdjustment = $diferenca / $remainingCount;

                    foreach ($openInstallments as $installment) {
                        $installment->update([
                            'amount' => $installment->amount + $installmentAdjustment,
                        ]);
                    }
                }

                // Descrição do pagamento
                $data['description'] = 'Pagamento parcial de R$' . number_format($partialPayment, 2, ',', '.') .
                    ' no dia ' . now()->format('d/m/Y') . '. ' . ($data['description'] ?? '');
            }
        }

        // Atualiza os dados da venda
        $record->update($data);

        // Notifica todos os usuários
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
