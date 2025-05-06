<?php

namespace App\Filament\Resources\CategoryResource\Pages;

use App\Models\User;
use Filament\Actions;
use Illuminate\Support\Facades\Auth;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;
use App\Filament\Resources\CategoryResource;

class CreateCategory extends CreateRecord
{
    protected static string $resource = CategoryResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $authUser = Auth::user();
        $recipients = User::all();

        Notification::make()
            ->title('Categoria cadastrada')
            ->icon('heroicon-o-tag')
            ->body($authUser->name . ' cadastrou a categoria ' . $data['name'] . '.')
            ->success()
            ->sendToDatabase($recipients);

        return $data;
    }
}
