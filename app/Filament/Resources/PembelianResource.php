<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PembelianResource\Pages;
use App\Filament\Resources\PembelianResource\RelationManagers;
use App\Models\Pembelian;
use Filament\Forms;
use Filament\Forms\Form as Schema;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Barryvdh\DomPDF\Facade\Pdf;

class PembelianResource extends Resource
{
    protected static ?string $model = Pembelian::class;

    protected static ?string $navigationIcon = 'heroicon-o-shopping-bag';
    protected static ?string $navigationLabel = 'Pembelian Produk';
    protected static ?string $navigationGroup = 'Transaksi';
    protected static ?int $navigationSort = 2;

    public static function shouldRegisterNavigation(): bool
    {
        return !(\App\Models\Setting::first()?->is_toko_tutup ?? false);
    }

    public static function canViewAny(): bool
    {
        return !(\App\Models\Setting::first()?->is_toko_tutup ?? false);
    }

    public static function form(Schema $schema): Schema
    {
        return \App\Filament\Resources\PembelianResource\Schemas\PembelianSchema::form($schema);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('nomor_pembelian')
                    ->label('Nomor Pembelian')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('distributor.nama_distributor')
                    ->label('Distributor')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('tanggal_pembelian')
                    ->label('Tanggal Pembelian')
                    ->date('d M Y')
                    ->sortable(),
                Tables\Columns\TextColumn::make('total_harga')
                    ->label('Total Pembelian')
                    ->money('IDR')
                    ->sortable(),
                Tables\Columns\TextColumn::make('creator.name')
                    ->label('Dibuat Oleh')
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('shift.nama')
                    ->label('Shift')
                    ->badge()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('created_at')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('deleted_at')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('tanggal_pembelian', 'desc')
            ->filters([
                Tables\Filters\Filter::make('tanggal_pembelian')
                    ->form([
                        Forms\Components\DatePicker::make('dari')
                            ->label('Dari Tanggal')
                            ->default(today())
                            ->displayFormat('d F Y')
                            ->locale('id')
                            ->native(false)
                            ->closeOnDateSelection()
                            ->maxDate(now()),
                        Forms\Components\DatePicker::make('sampai')
                            ->label('Sampai Tanggal')
                            ->default(today())
                            ->displayFormat('d F Y')
                            ->locale('id')
                            ->native(false)
                            ->closeOnDateSelection()
                            ->maxDate(now()),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when($data['dari'], fn($query, $date) => $query->whereDate('tanggal_pembelian', '>=', $date))
                            ->when($data['sampai'], fn($query, $date) => $query->whereDate('tanggal_pembelian', '<=', $date));
                    }),
                Tables\Filters\SelectFilter::make('distributor_id')
                    ->label('Distributor')
                    ->relationship('distributor', 'nama_distributor')
                    ->searchable()
                    ->preload(),
                Tables\Filters\SelectFilter::make('shift_id')
                    ->label('Shift')
                    ->relationship('shift', 'nama')
                    ->preload(),
                Tables\Filters\TrashedFilter::make(),
            ])
            ->headerActions([
                Tables\Actions\Action::make('cetak')
                    ->label('Cetak PDF')
                    ->icon('heroicon-o-printer')
                    ->color('success')
                    ->form([
                        Forms\Components\DatePicker::make('dari')
                            ->required()
                            ->default(today())
                            ->displayFormat('d F Y')
                            ->locale('id')
                            ->format('Y-m-d')
                            ->native(false)
                            ->closeOnDateSelection()
                            ->maxDate(now()),
                        Forms\Components\DatePicker::make('sampai')
                            ->required()
                            ->default(today())
                            ->displayFormat('d F Y')
                            ->locale('id')
                            ->format('Y-m-d')
                            ->native(false)
                            ->closeOnDateSelection()
                            ->maxDate(now()),
                        Forms\Components\Select::make('shift_id')
                            ->label('Filter Shift')
                            ->relationship('shift', 'nama')
                            ->placeholder('Semua Shift'),
                    ])
                    ->action(function ($data) {
                        try {
                            $query = Pembelian::with('distributor', 'creator', 'shift')
                                ->whereBetween('tanggal_pembelian', [$data['dari'], $data['sampai']]);

                            if (!empty($data['shift_id'])) {
                                $query->where('shift_id', $data['shift_id']);
                            }

                            $records = $query->get();
                            $start = $data['dari'];
                            $end = $data['sampai'];

                            $setting = \App\Models\Setting::first();
                            $storeName = $setting->nama_perusahaan ?? 'POS System';
                            $storeAddress = $setting->alamat ?? 'Alamat Toko Belum Diatur';
                            $userName = auth()->user()?->name ?? 'System';

                            $logoPath = public_path('images/logo e-pos.png');
                            if ($setting && $setting->logo && file_exists(storage_path('app/public/' . $setting->logo))) {
                                $logoPath = storage_path('app/public/' . $setting->logo);
                            }

                            $storeStatuses = \App\Models\StoreStatus::whereBetween('tanggal', [$data['dari'], $data['sampai']])
                                ->where('is_tutup', true)
                                ->get();

                            $pdf = Pdf::loadView('pdf.laporan-pembelian', compact('records', 'start', 'end', 'storeName', 'storeAddress', 'userName', 'logoPath', 'storeStatuses'))
                                ->setPaper('a4', 'landscape');
                            return response()->streamDownload(fn() => print($pdf->output()), 'laporan-pembelian.pdf');
                        } catch (\Exception $e) {
                            \Filament\Notifications\Notification::make()
                                ->title('Gagal Cetak PDF')
                                ->body($e->getMessage())
                                ->danger()
                                ->send();
                        }
                    }),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
                Tables\Actions\RestoreAction::make(),
                Tables\Actions\ForceDeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\RestoreBulkAction::make(),
                    Tables\Actions\ForceDeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPembelians::route('/'),
            'create' => Pages\CreatePembelian::route('/create'),
            'view' => Pages\ViewPembelian::route('/{record}'),
            'edit' => Pages\EditPembelian::route('/{record}/edit'),
        ];
    }

    private static function parseCurrency($number): int
    {
        if (empty($number)) return 0;
        if (is_int($number)) return $number;
        // Strip thousand separator (.) and handle decimal separator if any (,)
        return (int) str_replace(['.', ','], ['', '.'], $number);
    }
}
