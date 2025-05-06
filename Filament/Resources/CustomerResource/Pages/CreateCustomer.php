<?php

namespace App\Filament\Resources\CustomerResource\Pages;

use App\Models\User;
use Filament\Actions;
use Illuminate\Support\Facades\Auth;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;
use App\Filament\Resources\CustomerResource;

class CreateCustomer extends CreateRecord
{
    protected static string $resource = CustomerResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $authUser = Auth::user();
        $recipients = User::all();

        Notification::make()
            ->title('Cliente cadastrado')
            ->icon('heroicon-o-user-group')
            ->body($authUser->name . ' cadastrou o cliente ' . $data['name'] . '.')
            ->success()
            ->sendToDatabase($recipients);

        return $data;
    }
}
