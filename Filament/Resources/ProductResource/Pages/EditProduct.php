<?php

namespace App\Filament\Resources\ProductResource\Pages;

use App\Models\User;
use Filament\Actions;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Model;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use App\Filament\Resources\ProductResource;

class EditProduct extends EditRecord
{
    protected static string $resource = ProductResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make()->before(function ($record) {
                $authUser = Auth::user();
                $recipients = User::all();

                $productImage = $record->image;

                if ($productImage) {

                    $productImage = 'storage/' . $record->image . '';

                    // Verificar se o arquivo existe antes de tentar excluir
                    if (file_exists(public_path($productImage))) {
                        unlink(public_path($productImage));
                    }
                }

                Notification::make()
                    ->title('Produto deletado')
                    ->icon('heroicon-o-squares-plus')
                    ->body($authUser->name . ' deletou o produto ' . $record->name . '.')
                    ->danger()
                    ->sendToDatabase($recipients);
            }),
        ];
    }

    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        $authUser = Auth::user();
        $recipients = User::all();

        if ($data['image'] != $record->image) {
            $productImage = $record->image;

            if ($productImage) {
                $productImage = 'storage/' . $record->image . '';
                // Verificar se o arquivo existe antes de tentar excluir
                if (file_exists(public_path($productImage))) {
                    unlink(public_path($productImage));
                }
            }
        }

        $record->update($data);

        Notification::make()
            ->title('Produto atualizado')
            ->icon('heroicon-o-squares-plus')
            ->body($authUser->name . ' atualizou o produto ' . $record->name . '.')
            ->success()
            ->sendToDatabase($recipients);
        return $record;
    }
}
