<?php

namespace App\Filament\Resources\Stoks\Tables;

use Filament\Forms;
use Illuminate\Database\Eloquent\Builder;

use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Columns;
use Filament\Tables\Actions;
use Filament\Tables\Filters;

class StokTable
{
    public static function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn (Builder $query) => $query->with(['produk', 'user', 'shift']))
            ->columns([
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Tanggal')
                    ->dateTime('d M Y H:i')
                    ->sortable(),
                Tables\Columns\TextColumn::make('produk.nama')
                    ->label('Produk')
                    ->searchable()
                    ->copyable()
                    ->sortable(),
                // Tables\Columns\TextColumn::make('jenis')
                //     ->badge()
                //     ->color(fn(string $state): string => match ($state) {
                //         'pembelian' => 'success',
                //         'penjualan' => 'danger',
                //     }),
                Tables\Columns\TextColumn::make('jumlah')
                    ->label('Jumlah')
                    ->numeric(thousandsSeparator: '.', decimalSeparator: ',')
                    ->formatStateUsing(fn($state) => ($state > 0 ? '+' : '') . number_format((float) $state, 0, ',', '.'))
                    ->color(fn(int $state): string => $state > 0 ? 'success' : 'danger')
                    ->weight('bold'),
                Tables\Columns\TextColumn::make('stok_awal')
                    ->label('Stok Awal')
                    ->numeric(thousandsSeparator: '.', decimalSeparator: ','),
                Tables\Columns\TextColumn::make('stok_akhir')
                    ->label('Sisa')
                    ->numeric(thousandsSeparator: '.', decimalSeparator: ',')
                    ->weight('bold')
                    ->badge()
                    ->color(fn(int $state): string => match (true) {
                        $state < 5 => 'danger',
                        $state < 10 => 'warning',
                        default => 'success',
                    }),
                // Tables\Columns\TextColumn::make('referensi_type')
                //     ->label('Ref')
                //     ->formatStateUsing(fn($state) => class_basename($state))
                //     ->badge()
                //     ->color('gray')
                //     ->toggleable(),
                // Tables\Columns\TextColumn::make('keterangan')
                //     ->limit(30)
                //     ->tooltip(fn($state) => $state),
                Tables\Columns\TextColumn::make('user.name')
                    ->label('Nama Pengguna'),
                Tables\Columns\TextColumn::make('shift.nama')
                    ->label('Shift')
                    ->badge()
                    ->toggleable(isToggledHiddenByDefault: true),
                // ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('jenis')
                    ->options([
                        'pembelian' => 'Pembelian',
                        'penjualan' => 'Penjualan',
                    ]),
                Tables\Filters\Filter::make('created_at')
                    ->form([
                        Forms\Components\DatePicker::make('dari_tanggal')
                            ->displayFormat('d F Y')
                            ->default(today())
                            ->locale('id')
                            ->native(false)
                            ->closeOnDateSelection()
                            ->maxDate(now()),
                        Forms\Components\DatePicker::make('sampai_tanggal')
                            ->displayFormat('d F Y')
                            ->default(today())
                            ->locale('id')
                            ->native(false)
                            ->closeOnDateSelection()
                            ->maxDate(now()),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['dari_tanggal'],
                                fn(Builder $query, $date) => $query->where('created_at', '>=', \Carbon\Carbon::parse($date)->startOfDay()),
                            )
                            ->when(
                                $data['sampai_tanggal'],
                                fn(Builder $query, $date) => $query->where('created_at', '<=', \Carbon\Carbon::parse($date)->endOfDay()),
                            );
                    }),
                Tables\Filters\SelectFilter::make('shift_id')
                    ->label('Shift')
                    ->relationship('shift', 'nama')
                    ->preload(),
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
                            $query = Stok::with('produk', 'user', 'shift')
                                ->whereBetween('created_at', [$data['dari'] . ' 00:00', $data['sampai'] . ' 23:59']);

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

                            $pdf = Pdf::loadView('pdf.laporan-stok', compact('records', 'start', 'end', 'storeName', 'storeAddress', 'userName', 'logoPath', 'storeStatuses'))
                                ->setPaper('a4', 'landscape');
                            return $pdf->download('laporan-stok.pdf');
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
                Tables\Actions\ViewAction::make(),
            ])
            ->bulkActions([]);
    }
}
