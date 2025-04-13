<?php

namespace App\Filament\Resources\BrandResource\Pages;

use App\Models\User;
use App\Models\Brand;
use Filament\Actions;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Model;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use App\Filament\Resources\BrandResource;

class EditBrand extends EditRecord
{
    protected static string $resource = BrandResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make()->before(function (Brand $record) {
                $authUser = Auth::user();
                $recipients = User::all();

                Notification::make()
                    ->title('Marca deletada')
                    ->icon('heroicon-o-rectangle-stack')
                    ->body($authUser->name . ' deletou a marca ' . $record->name . '.')
                    ->danger()
                    ->sendToDatabase($recipients);
            })->requiresConfirmation(),
        ];
    }

    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        $authUser = Auth::user();
        $recipients = User::all();

        $record->update($data);

        Notification::make()
            ->title('Marca atualizada')
            ->icon('heroicon-o-user-group')
            ->body($authUser->name . ' atualizou a marca ' . $record->name . '.')
            ->success()
            ->sendToDatabase($recipients);
        
        return $record;    
    }
}
