<?php

namespace App\Filament\Resources\UserResource\Pages;

use App\Models\User;
use Filament\Actions;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Model;
use App\Filament\Resources\UserResource;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;

class EditUser extends EditRecord
{
    protected static string $resource = UserResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make()->before(function ($record) {
                $authUser = Auth::user();
                $recipients = User::all();

                Notification::make()
                    ->title('Usuário deletado')
                    ->icon('heroicon-o-users')
                    ->body($authUser->name . ' deletou o usuário ' . $record->name . '.')
                    ->danger()
                    ->sendToDatabase($recipients);
            }),
        ];
    }

    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        $authUser = Auth::user();
        $recipients = User::all();

        $record->update($data);
        
        Notification::make()
            ->title('Usuário atualizado')
            ->icon('heroicon-o-users')
            ->body($authUser->name . ' atualizou o usuário ' . $record->name . '.')
            ->success()
            ->sendToDatabase($recipients);
        return $record;
    }
}
