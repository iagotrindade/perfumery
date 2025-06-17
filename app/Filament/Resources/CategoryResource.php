<?php

namespace App\Filament\Resources;

use Filament\Forms;
use App\Models\User;
use Filament\Tables;
use App\Models\Category;
use Filament\Forms\Form;
use Filament\Tables\Table;
use Filament\Resources\Resource;
use Illuminate\Support\Facades\Auth;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Textarea;
use Filament\Tables\Columns\TextColumn;
use Illuminate\Database\Eloquent\Model;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Illuminate\Database\Eloquent\Builder;
use App\Filament\Resources\CategoryResource\Pages;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\Resources\CategoryResource\RelationManagers;

class CategoryResource extends Resource
{
    protected static ?string $model = Category::class;

    protected static ?string $navigationIcon = 'heroicon-o-tag';

    protected static ?string $recordTitleAttribute = 'name';

    protected static ?string $modelLabel = 'Categorias';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Informações da Categoria')
                    ->schema([
                        TextInput::make('name')
                            ->label('Nome')
                            ->prefixIcon('heroicon-m-tag')
                            ->required(),
                        Textarea::make('description')
                            ->label('Descrição')
                    ])
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('Nome')
                    ->sortable()
                    ->searchable(),

                TextColumn::make('description')
                    ->label('Descrição')
                    ->limit(50)
                    ->sortable()
                    ->searchable(),
           
                TextColumn::make('products_count')
                    ->label('Produtos')
                    ->counts('products')
                    ->sortable(),

                TextColumn::make('created_at')
                    ->label('Criado em')
                    ->dateTime('d M Y \à\s H:i')
                    ->sortable()
                    ->searchable(),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make()->before(function (Category $record) {
                    $authUser = Auth::user();
                    $recipients = User::all();

                    Notification::make()
                        ->title('Categoria deletada')
                        ->icon('heroicon-o-tag')
                        ->body($authUser->name . ' deletou a categoria ' . $record->name . '.')
                        ->danger()
                        ->sendToDatabase($recipients);
                })->requiresConfirmation(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getGloballySearchableAttributes(): array
    {
        return ['name'];
    }

    public static function getGlobalSearchResultDetails(Model $record): array
    {
        return [
            'Produtos' => $record->products->count(),
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCategories::route('/'),
            'create' => Pages\CreateCategory::route('/create'),
            'edit' => Pages\EditCategory::route('/{record}/edit'),
        ];
    }
}
