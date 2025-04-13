<?php

namespace App\Filament\Resources\CategoryResource\Pages;

use App\Models\User;
use Filament\Actions;
use App\Models\Category;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Model;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use App\Filament\Resources\CategoryResource;

class EditCategory extends EditRecord
{
    protected static string $resource = CategoryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make()->before(function (Category $record) {
                $authUser = Auth::user();
                $recipients = User::all();

                Notification::make()
                    ->title('Categoria deletada')
                    ->icon('heroicon-o-tag')
                    ->body($authUser->name . ' deletou a categoria ' . $record->name . '.')
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
            ->title('Categoria atualizada')
            ->icon('heroicon-o-user-group')
            ->body($authUser->name . ' atualizou a categoria ' . $record->name . '.')
            ->success()
            ->sendToDatabase($recipients);
        
        return $record;    
    }
}
