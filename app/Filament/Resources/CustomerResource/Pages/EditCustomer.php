<?php

namespace App\Filament\Resources\CustomerResource\Pages;

use App\Models\User;
use Filament\Actions;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Model;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use App\Filament\Resources\CustomerResource;

class EditCustomer extends EditRecord
{
    protected static string $resource = CustomerResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make()->before(function ($record) {
                $authUser = Auth::user();
                $recipients = User::all();

                Notification::make()
                    ->title('Cliente deletado')
                    ->icon('heroicon-o-user-group')
                    ->body($authUser->name . ' deletou o cliente ' . $record->name . '.')
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
            ->title('Cliente atualizado')
            ->icon('heroicon-o-user-group')
            ->body($authUser->name . ' atualizou o cliente ' . $record->name . '.')
            ->success()
            ->sendToDatabase($recipients);
        
        return $record;    
    }
}
