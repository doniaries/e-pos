<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PelangganResource\Pages;
use App\Models\Pelanggan;
use App\Models\PembayaranHutangMember;
use App\Filament\Resources\PelangganResource\RelationManagers\PembayaranHutangRelationManager;
use App\Filament\Resources\PelangganResource\RelationManagers\PenjualansRelationManager;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class PelangganResource extends Resource
{
    protected static ?string $model = Pelanggan::class;

    protected static ?string $navigationIcon = 'heroicon-o-users';
    protected static ?string $navigationLabel = 'Pelanggan';
    protected static ?string $pluralLabel = 'Pelanggan';
    protected static ?string $modelLabel = 'Pelanggan';
    protected static ?string $navigationGroup = 'Master Data';
    protected static ?int $navigationSort = 5;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Data Member')
                    ->description('Informasi identitas pelanggan tetap (member).')
                    ->schema([
                        Forms\Components\TextInput::make('kode_member')
                            ->label('ID Member')
                            ->placeholder('Otomatis')
                            ->disabled()
                            ->dehydrated(false)
                            ->visible(fn($record) => $record !== null),
                        Forms\Components\TextInput::make('nama')
                            ->label('Nama Lengkap')
                            ->unique(ignoreRecord: true)
                            ->required()
                            ->dehydrateStateUsing(fn($state) => strtoupper($state))
                            ->maxLength(50),
                        Forms\Components\DatePicker::make('tanggal_bergabung')
                            ->label('Tanggal Bergabung')
                            ->default(now())
                            ->required()
                            ->displayFormat('d F Y')
                            ->locale('id')
                            ->native(false),
                        Forms\Components\TextInput::make('kontak')
                            ->label('No. Telepon / WhatsApp')
                            ->placeholder('08xxx')
                            ->unique(ignoreRecord: true)
                            ->maxLength(20),
                        Forms\Components\Textarea::make('alamat')
                            ->label('Alamat Rumah')
                            ->placeholder('Jl. ...')
                            ->maxLength(255)
                            ->columnSpanFull(),
                    ])->columns(2),

                Forms\Components\Section::make('Status Keuangan')
                    ->description('Informasi hutang atau saldo member.')
                    ->schema([
                        Forms\Components\TextInput::make('hutang')
                            ->label('Total Hutang')
                            ->currencyMask(
                                thousandSeparator: '.',
                                decimalSeparator: ',',
                                precision: 0
                            )
                            ->default(0)
                            ->prefix('Rp')
                            ->helperText('Jumlah hutang yang belum dibayar oleh member.')
                            ->numeric()
                    ])->columns(1),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('kode_member')
                    ->label('ID')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('nama')
                    ->label('Nama Member')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('kontak')
                    ->label('Telepon')
                    ->searchable(),
                Tables\Columns\TextColumn::make('hutang')
                    ->label('Hutang')
                    ->numeric(thousandsSeparator: '.', decimalSeparator: ',')
                    ->formatStateUsing(fn($state) => number_format($state, 0, ',', '.') . ',-')
                    ->alignRight()
                    ->color(fn($state) => $state > 0 ? 'danger' : 'success')
                    ->weight('bold')
                    ->default(0)
                    ->summarize([
                        Tables\Columns\Summarizers\Sum::make()
                            ->formatStateUsing(fn($state) => number_format($state, 0, ',', '.') . ',-')
                    ])
                    ->sortable(),
                Tables\Columns\TextColumn::make('tanggal_hutang_terakhir')
                    ->label('Tgl Hutang Terakhir')
                    ->dateTime('d/m/Y H:i')
                    ->placeholder('-')
                    ->sortable(),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->getStateUsing(fn(Pelanggan $record): string => $record->hutang > 0 ? 'Berhutang' : 'Lunas')
                    ->color(fn(string $state): string => match ($state) {
                        'Berhutang' => 'danger',
                        'Lunas' => 'success',
                    }),
            ])
            ->defaultSort('tanggal_hutang_terakhir', 'desc')
            ->filters([
                Tables\Filters\Filter::make('mempunyai_hutang')
                    ->label('Hanya Yang Berhutang')
                    ->query(fn(Builder $query): Builder => $query->where('hutang', '>', 0)),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\Action::make('bayar_hutang')
                    ->label('Bayar Hutang')
                    ->icon('heroicon-o-banknotes')
                    ->color('success')
                    ->visible(fn(Pelanggan $record) => $record->hutang > 0)
                    ->form([
                        Forms\Components\TextInput::make('nama')
                            ->default(fn(Pelanggan $record) => $record->nama)
                            ->disabled(),
                        Forms\Components\TextInput::make('total_hutang')
                            ->label('Total Hutang Saat Ini')
                            ->default(fn(Pelanggan $record) => $record->hutang)
                            ->prefix('Rp')
                            ->disabled()
                            ->numeric()
                            ->formatStateUsing(fn($state) => number_format((float) $state, 0, ',', '.')),
                        Forms\Components\TextInput::make('jumlah_bayar')
                            ->label('Jumlah Bayar')
                            ->required()
                            ->currencyMask(
                                thousandSeparator: '.',
                                decimalSeparator: ',',
                                precision: 0
                            )
                            ->prefix('Rp')
                            ->minValue(1)
                            ->maxValue(fn(Pelanggan $record) => $record->hutang)
                            ->hint(fn(Pelanggan $record) => 'Maks: Rp ' . number_format((float) $record->hutang, 0, ',', '.')),
                        Forms\Components\DateTimePicker::make('tanggal_bayar')
                            ->label('Tanggal Bayar')
                            ->default(now())
                            ->required()
                            ->displayFormat('d F Y H:i')
                            ->locale('id')
                            ->native(false),
                        Forms\Components\Select::make('metode_pembayaran')
                            ->label('Metode Pembayaran')
                            ->options([
                                'tunai' => 'Tunai',
                                'transfer' => 'Transfer',
                                'qris' => 'QRIS',
                                'debit' => 'Kartu Debit',
                            ])
                            ->default('tunai')
                            ->required(),
                        Forms\Components\Textarea::make('catatan')
                            ->label('Catatan')
                            ->placeholder('Misal: Pembayaran cicilan ke-1'),
                    ])
                    ->action(function (array $data, Pelanggan $record) {
                        DB::beginTransaction();
                        try {
                            $jumlahBayar = $data['jumlah_bayar'];
                            $sisaHutang = $record->hutang - $jumlahBayar;

                            // 1. Catat riwayat pembayaran
                            PembayaranHutangMember::create([
                                'pelanggan_id' => $record->id,
                                'jumlah_bayar' => $jumlahBayar,
                                'tanggal_bayar' => $data['tanggal_bayar'],
                                'sisa_hutang' => $sisaHutang,
                                'metode_pembayaran' => $data['metode_pembayaran'],
                                'catatan' => $data['catatan'],
                                'user_id' => Auth::id(),
                            ]);

                            // 2. Update hutang di tabel pelanggans
                            $record->update([
                                'hutang' => $sisaHutang,
                            ]);

                            DB::commit();

                            Notification::make()
                                ->title('Pembayaran Berhasil')
                                ->body("Berhasil mencatat pembayaran sebesar Rp " . number_format($jumlahBayar, 0, ',', '.') . " untuk member {$record->nama}.")
                                ->success()
                                ->send();
                        } catch (\Exception $e) {
                            DB::rollBack();
                            Notification::make()
                                ->title('Terjadi Kesalahan')
                                ->body($e->getMessage())
                                ->danger()
                                ->send();
                        }
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
            PenjualansRelationManager::class,
            PembayaranHutangRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPelanggans::route('/'),
            'create' => Pages\CreatePelanggan::route('/create'),
            'edit' => Pages\EditPelanggan::route('/{record}/edit'),
        ];
    }
}
