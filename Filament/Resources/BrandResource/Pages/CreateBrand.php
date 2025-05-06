<?php

namespace App\Filament\Resources\BrandResource\Pages;

use App\Models\User;
use Filament\Actions;
use Illuminate\Support\Facades\Auth;
use Filament\Notifications\Notification;
use App\Filament\Resources\BrandResource;
use Filament\Resources\Pages\CreateRecord;

class CreateBrand extends CreateRecord
{
    protected static string $resource = BrandResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $authUser = Auth::user();
        $recipients = User::all();

        Notification::make()
            ->title('Marca cadastrada')
            ->icon('heroicon-o-rectangle-stack')
            ->body($authUser->name . ' cadastrou a marca ' . $data['name'] . '.')
            ->success()
            ->sendToDatabase($recipients);

        return $data;
    }
}
