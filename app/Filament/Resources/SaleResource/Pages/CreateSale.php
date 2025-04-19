<?php

namespace App\Filament\Resources\SaleResource\Pages;

use App\Models\Sale;
use Filament\Actions;
use App\Models\Product;
use Filament\Forms\Form;
use Illuminate\Database\Eloquent\Model;
use App\Filament\Resources\SaleResource;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Auth;
use Filament\Notifications\Notification;
use App\Models\User;


class CreateSale extends CreateRecord
{
    protected static string $resource = SaleResource::class;

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
