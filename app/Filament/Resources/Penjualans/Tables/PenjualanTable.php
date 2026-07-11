<?php

namespace App\Filament\Resources\Penjualans\Tables;
use App\Models\Penjualan;
use Barryvdh\DomPDF\Facade\Pdf;
use Filament\Forms;
use Illuminate\Database\Eloquent\Builder;

use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Columns;
use Filament\Tables\Actions;
use Filament\Tables\Filters;

class PenjualanTable
{
    public static function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn (Builder $query) => $query->with(['kasir', 'pelanggan', 'shift']))
            ->poll('3s') //hot reload data
            ->columns([
                Tables\Columns\TextColumn::make('nomor')
                    ->label('No Invoice')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('kasir.name')
                    ->label('Kasir')
                    ->sortable(),

                Tables\Columns\TextColumn::make('pelanggan.nama')
                    ->label('Pelanggan')
                    ->default('Umum')
                    ->searchable()
                    ->description(fn($record) => $record->pelanggan ? 'Member: ' . $record->pelanggan->kode_member : null),

                Tables\Columns\TextColumn::make('metode_pembayaran')
                    ->label('Cara Bayar')
                    ->badge()
                    ->color('info')
                    ->formatStateUsing(fn(string $state): string => match ($state) {
                        'tunai' => 'Tunai',
                        'transfer' => 'Transfer',
                        default => ucfirst($state),
                    }),
                Tables\Columns\TextColumn::make('shift.nama')
                    ->label('Shift')
                    ->badge()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('total')
                    ->money('IDR')
                    ->badge()
                    ->color('success')
                    ->sortable()
                    ->summarize(Tables\Columns\Summarizers\Sum::make()->money('IDR')),
                Tables\Columns\TextColumn::make('bayar')
                    ->label('Bayar')
                    ->money('IDR')
                    ->sortable()
                    ->summarize(Tables\Columns\Summarizers\Sum::make()->money('IDR')),
                Tables\Columns\TextColumn::make('kembali')
                    ->label('Kembali')
                    ->money('IDR')
                    ->sortable()
                    ->summarize(Tables\Columns\Summarizers\Sum::make()->money('IDR')),
                Tables\Columns\TextColumn::make('status_pembayaran')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'hutang' => 'danger',
                        'bayar_sebagian' => 'warning',
                        'lunas' => 'success',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Tanggal')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                Tables\Filters\Filter::make('created_at')
                    ->form([
                        Forms\Components\DatePicker::make('dari_tanggal')
                            ->label('Dari Tanggal')
                            ->default(today())
                            ->displayFormat('d F Y')
                            ->locale('id')
                            ->native(false)
                            ->closeOnDateSelection()
                            ->maxDate(now()),
                        Forms\Components\DatePicker::make('sampai_tanggal')
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
                Tables\Filters\TrashedFilter::make(),
            ])
            ->headerActions([
                Tables\Actions\Action::make('refresh')
                    ->label('Refresh')
                    ->icon('heroicon-o-arrow-path')
                    ->color('gray')
                    ->action(fn($livewire) => $livewire->dispatch('$refresh')),
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
                            $query = Penjualan::with('kasir', 'shift')
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
                            /** @var \App\Models\User|null $user */
                            $user = \Illuminate\Support\Facades\Auth::user();
                            $userName = $user?->name ?? 'System';

                            $logoPath = public_path('images/logo e-pos.png');
                            if ($setting && $setting->logo && file_exists(storage_path('app/public/' . $setting->logo))) {
                                $logoPath = storage_path('app/public/' . $setting->logo);
                            }

                            $storeStatuses = \App\Models\StoreStatus::whereBetween('tanggal', [$data['dari'], $data['sampai']])
                                ->where('is_tutup', true)
                                ->get();

                            $pdf = Pdf::loadView('pdf.laporan-penjualan', compact('records', 'start', 'end', 'storeName', 'storeAddress', 'userName', 'logoPath', 'storeStatuses'))
                                ->setPaper('a4', 'landscape');
                            $shiftName = 'semua-shift';
                            if (!empty($data['shift_id'])) {
                                $shift = \App\Models\Shift::find($data['shift_id']);
                                if ($shift) {
                                    $shiftName = 'shift-' . \Illuminate\Support\Str::slug($shift->nama);
                                }
                            }

                            $filename = 'laporan-penjualan-' . $start;
                            if ($start !== $end) {
                                $filename .= '-sd-' . $end;
                            }
                            $filename .= '-' . $shiftName . '.pdf';

                            return response()->streamDownload(fn() => print($pdf->output()), $filename);
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
                Tables\Actions\Action::make('print')
                    ->label('Struk')
                    ->icon('heroicon-o-printer')
                    ->color('info')
                    ->url(fn(Penjualan $record): string => route('pos.print-struk', $record->id))
                    ->openUrlInNewTab(),
                Tables\Actions\ViewAction::make(),
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
}
