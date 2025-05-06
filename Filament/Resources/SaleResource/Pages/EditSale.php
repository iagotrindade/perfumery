<?php

namespace App\Filament\Resources\SaleResource\Pages;

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

    protected function handleRecordUpdate(Model $record, array $data): Model
    {
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
        ];
    }
}
