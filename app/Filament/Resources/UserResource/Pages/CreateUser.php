<?php

namespace App\Filament\Resources\UserResource\Pages;

use App\Models\User;
use Filament\Actions;
use Illuminate\Support\Facades\Auth;
use App\Filament\Resources\UserResource;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;

class CreateUser extends CreateRecord
{
    protected static string $resource = UserResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $authUser = Auth::user();
        $recipients = User::all();

        Notification::make()
            ->title('Usuário cadastrado')
            ->icon('heroicon-o-users')
            ->body($authUser->name . ' cadastrou o usuário ' . $data['name'] . '.')
            ->success()
            ->sendToDatabase($recipients);
            
        return $data;
    }
}
