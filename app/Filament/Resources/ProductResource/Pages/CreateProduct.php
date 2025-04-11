<?php

namespace App\Filament\Resources\ProductResource\Pages;

use App\Models\User;
use Filament\Actions;
use Illuminate\Support\Facades\Auth;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;
use App\Filament\Resources\ProductResource;

class CreateProduct extends CreateRecord
{
    protected static string $resource = ProductResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $authUser = Auth::user();
        $recipients = User::all();

        Notification::make()
            ->title('Produto cadastrado')
            ->icon('heroicon-o-squares-plus')
            ->body($authUser->name . ' cadastrou o produto ' . $data['name'] . '.')
            ->success()
            ->sendToDatabase($recipients);
            
        return $data;
    }
}
