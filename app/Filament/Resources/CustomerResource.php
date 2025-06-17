<?php

namespace App\Filament\Resources;

use Carbon\Carbon;
use Filament\Forms;
use App\Models\User;
use Filament\Tables;
use App\Models\Customer;
use Filament\Forms\Form;
use Filament\Tables\Table;
use Filament\Resources\Resource;
use Filament\Tables\Filters\Filter;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Textarea;
use Filament\Tables\Columns\TextColumn;
use Illuminate\Database\Eloquent\Model;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Forms\Components\DatePicker;
use Illuminate\Database\Eloquent\Builder;
use App\Filament\Resources\CustomerResource\Pages;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\Resources\CustomerResource\RelationManagers;

class CustomerResource extends Resource
{
    protected static ?string $model = Customer::class;

    protected static ?string $navigationIcon = 'heroicon-o-user-group';

    protected static ?string $recordTitleAttribute = 'name';

    protected static ?string $modelLabel = 'Clientes';


    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Informações pessoais, Vendas e de Contato')
                    ->schema([
                        TextInput::make('name')
                            ->label('Nome')
                            ->prefixIcon('heroicon-m-user-circle')
                            ->required(),

                        TextInput::make('cpf')
                            ->label('CPF')
                            ->prefixIcon('heroicon-m-identification')
                            ->required(),

                        TextInput::make('email')
                            ->label('Email')
                            ->email()
                            ->prefixIcon('heroicon-m-envelope')
                            ->required(),

                        TextInput::make('phone')
                            ->label('Telefone')
                            ->prefixIcon('heroicon-m-device-phone-mobile')
                            ->required(),

                        Textarea::make('description')
                            ->label('Descrição')
                            ->columnSpan(2),

                        TextInput::make('sale_limit')
                            ->label('Valor máximo de compras')
                            ->prefixIcon('heroicon-m-currency-dollar')
                            ->numeric()
                            ->helperText('Valor máximo em compras que o cliente pode gastar.')
                            ->columnSpan(2),
                    ])->columns(2),

                Section::make('Informações de Endereço')
                    ->schema([
                        Repeater::make('addresses')
                            ->relationship() // isso é o mais importante!
                            ->label('Endereços')
                            ->schema([
                                Select::make('label')
                                    ->label('Tipo')
                                    ->prefixIcon('heroicon-m-home')
                                    ->options([
                                        'residential' => 'Residencial',
                                        'commercial' => 'Comercial',
                                        'billing' => 'Cobrança',
                                        'shipping' => 'Entrega',
                                    ])
                                    ->default('residential')
                                    ->required(),

                                TextInput::make('postal_code')
                                    ->label('CEP')
                                    ->prefixIcon('heroicon-m-map-pin')
                                    ->placeholder('00000-000')
                                    ->required()
                                    ->live()
                                    ->afterStateUpdated(function ($state, callable $set, callable $get) {
                                        if (! $state) return;

                                        $cep = preg_replace('/[^0-9]/', '', $state);

                                        if (strlen($cep) !== 8) return;

                                        $response = Http::get("https://viacep.com.br/ws/{$cep}/json/");

                                        if ($response->successful() && !$response->json('erro')) {
                                            $data = $response->json();

                                            $set('street', $data['logradouro'] ?? '');
                                            $set('neighborhood', $data['bairro'] ?? '');
                                            $set('city', $data['localidade'] ?? '');
                                            $set('state', $data['uf'] ?? '');
                                        }
                                    }),

                                TextInput::make('street')
                                    ->label('Rua')
                                    ->prefixIcon('heroicon-m-arrows-right-left')
                                    ->required(),

                                TextInput::make('number')
                                    ->label('Número')
                                    ->prefixIcon('heroicon-m-numbered-list')
                                    ->required(),

                                TextInput::make('complement')
                                    ->prefixIcon('heroicon-m-chat-bubble-bottom-center-text')
                                    ->label('Complemento'),

                                TextInput::make('neighborhood')
                                    ->label('Bairro')
                                    ->prefixIcon('heroicon-m-rectangle-group')
                                    ->required(),

                                TextInput::make('city')
                                    ->label('Cidade')
                                    ->prefixIcon('heroicon-m-map')
                                    ->required(),

                                TextInput::make('state')
                                    ->label('Estado')
                                    ->prefixIcon('heroicon-m-flag')
                                    ->default('RS')
                                    ->required(),

                                TextInput::make('country')
                                    ->label('País')
                                    ->prefixIcon('heroicon-m-globe-americas')
                                    ->default('Brasil')
                                    ->required(),

                                Toggle::make('is_primary')
                                    ->label('Endereço principal')
                                    ->default(true),
                            ])
                            ->columns(3)
                    ])
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('Nome')
                    ->searchable()
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('cpf')
                    ->label('CPF')
                    ->searchable()
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('email')
                    ->label('Email')
                    ->searchable()
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('phone')
                    ->label('Telefone')
                    ->searchable()
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('sale_limit')
                    ->label('Limite de compras')
                    ->money('BRL', true),
            ])
            ->filters([
                Filter::make('data')
                    ->form([
                        DatePicker::make('Data'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['Data'],
                                fn(Builder $query, $date): Builder => $query->whereDate('updated_at', '>=', $date),
                            );
                    }),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make()->before(function ($record) {
                    $authUser = Auth::user();
                    $recipients = User::all();

                    Notification::make()
                        ->title('Cliente deletado')
                        ->icon('heroicon-o-user-group')
                        ->body($authUser->name . ' deletou o cliente ' . $record->name . '.')
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
        return ['name', 'email'];
    }

    public static function getGlobalSearchResultDetails(Model $record): array
    {
        return [
            'Email' => $record->email,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCustomers::route('/'),
            'view' => Pages\ViewCustomer::route('/{record}/view'),
            'create' => Pages\CreateCustomer::route('/create'),
            'edit' => Pages\EditCustomer::route('/{record}/edit'),
        ];
    }
}
