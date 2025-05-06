<?php

namespace App\Filament\Resources;

use Filament\Forms;
use App\Models\User;
use Filament\Tables;
use App\Models\Brand;
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
use App\Filament\Resources\BrandResource\Pages;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\Resources\BrandResource\RelationManagers;
use Filament\Forms\Components\Toggle;

class BrandResource extends Resource
{
    protected static ?string $model = Brand::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?string $recordTitleAttribute = 'name';

    protected static ?string $modelLabel = 'Marcas';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Informações da Marca')
                    ->schema([
                        TextInput::make('name')
                            ->label('Nome')
                            ->required(),
                        Textarea::make('description')
                            ->label('Descrição'),
                        Toggle::make('show_on_catalog')
                            ->label('Mostrar no catálogo')
                            ->default(true)
                            ->inline(false)
                            ->required(),
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
                // contagem de produtos
                TextColumn::make('products_count')
                    ->label('Produtos')
                    ->counts('products')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('show_on_catalog')
                    ->label('Mostrar no catálogo')
                    ->formatStateUsing(function ($state) {
                        return $state ? 'Sim' : 'Não';
                    })
                    ->sortable()
                    ->searchable(),
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
                Tables\Actions\DeleteAction::make()->before(function (Brand $record) {
                    $authUser = Auth::user();
                    $recipients = User::all();

                    Notification::make()
                        ->title('Marca deletada')
                        ->icon('heroicon-o-rectangle-stack')
                        ->body($authUser->name . ' deletou a marca ' . $record->name . '.')
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
            'index' => Pages\ListBrands::route('/'),
            'create' => Pages\CreateBrand::route('/create'),
            'edit' => Pages\EditBrand::route('/{record}/edit'),
        ];
    }
}
