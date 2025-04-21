<?php

namespace App\Filament\Resources;

use Filament\Forms;
use App\Models\User;
use Filament\Tables;
use App\Models\Brand;
use App\Models\Product;
use App\Models\Category;
use Filament\Forms\Form;
use Filament\Tables\Table;
use Filament\Support\RawJs;
use Filament\Resources\Resource;
use Illuminate\Support\Facades\Auth;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Textarea;
use Filament\Tables\Columns\TextColumn;
use Illuminate\Database\Eloquent\Model;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\ImageColumn;
use Filament\Forms\Components\FileUpload;
use Illuminate\Database\Eloquent\Builder;
use App\Filament\Resources\ProductResource\Pages;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\Resources\ProductResource\RelationManagers;

class ProductResource extends Resource
{
    protected static ?string $model = Product::class;

    protected static ?string $navigationIcon = 'heroicon-o-squares-plus';

    protected static ?string $modelLabel = 'Produtos';

    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make()
                    ->schema([
                        FileUpload::make('image')
                            ->image()
                            ->label('Imagem')
                            ->acceptedFileTypes(['image/jpeg', 'image/jpg'])
                            ->imageEditor()
                            ->acceptedFileTypes([
                                'image/jpeg',
                                'image/jpg',
                                'image/png',
                                'image/webp',
                            ])
                            ->directory('products')
                    ]),
                Section::make()
                    ->schema([
                        TextInput::make('id')
                            ->label('ID')
                            ->disabled()
                            ->hidden(),
                        TextInput::make('name')
                            ->label('Nome')
                            ->required(),

                        Select::make('category_id')
                            ->label('Categoria')
                            ->options(Category::all()->pluck('name', 'id'))
                            ->searchable(),

                        Select::make('brand_id')
                            ->label('Marca')
                            ->options(Brand::all()->pluck('name', 'id'))
                            ->searchable(),

                        TextInput::make('quantity')
                            ->label('Quantidade em Estoque')
                            ->numeric()
                            ->minValue(0)
                            ->required(),

                        TextInput::make('cost_value')
                            ->label('Valor de Custo')
                            ->stripCharacters([','])
                            ->numeric()
                            ->prefix('R$')
                            ->inputMode('decimal')
                            ->required(),

                        TextInput::make('sale_value')
                            ->label('Valor de Venda')
                            ->stripCharacters([','])
                            ->numeric()
                            ->prefix('R$')
                            ->inputMode('decimal')
                            ->required(),


                        TextArea::make('description')
                            ->label('Descrição')
                            ->columnSpan(2),

                        Toggle::make('show_on_catalog')
                            ->label('Mostrar no catálogo')
                            ->default(true)
                            ->inline(false)
                            ->required(),

                    ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            ImageColumn::make('image')
                ->label('Imagem')
                ->circular()
                ->size(50)
                ->toggleable(),
            TextColumn::make('name')
                ->label('Nome')
                ->limit(20)
                ->searchable()
                ->sortable()
                ->toggleable(),
            TextColumn::make('quantity')
                ->label('Qtd em Estoque')
                ->sortable()
                ->searchable()
                ->toggleable(),
            TextColumn::make('cost_value')
                ->money('BRL')
                ->label('Custo')
                ->sortable()
                ->searchable()
                ->toggleable(),
            TextColumn::make('sale_value')
                ->money('BRL')
                ->label('Valor Venda')
                ->sortable()
                ->searchable()
                ->toggleable(),
            TextColumn::make('show_on_catalog')
                ->label('Mostrar no Catálogo')
                ->formatStateUsing(function ($state) {
                    return $state ? 'Sim' : 'Não';
                }),
            TextColumn::make('created_at')
                ->label('Criado em')
                ->dateTime('d M Y')
                ->sortable()
                ->toggleable()
        ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make()->before(function ($record) {
                    $authUser = Auth::user();
                    $recipients = User::all();

                    Notification::make()
                        ->title('Produto deletado')
                        ->icon('heroicon-o-squares-plus')
                        ->body($authUser->name . ' deletou o produto ' . $record->name . '.')
                        ->danger()
                        ->sendToDatabase($recipients);
                }),
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
        return ['name', 'description'];
    }

    public static function getGlobalSearchResultDetails(Model $record): array
    {
        return [
            'Estoque' => $record->quantity,
        ];
    }


    public static function getPages(): array
    {
        return [
            'index' => Pages\ListProducts::route('/'),
            'create' => Pages\CreateProduct::route('/create'),
            'edit' => Pages\EditProduct::route('/{record}/edit'),
        ];
    }
}
