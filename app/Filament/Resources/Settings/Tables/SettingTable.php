<?php

namespace App\Filament\Resources\Settings\Tables;

use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Columns;
use Filament\Tables\Actions;
use Filament\Tables\Filters;

class SettingTable
{
    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('nama_perusahaan')
                    ->searchable(),
                Tables\Columns\TextColumn::make('alamat')
                    ->searchable(),
                Tables\Columns\TextColumn::make('kontak')
                    ->searchable(),
                Tables\Columns\TextColumn::make('pimpinan')
                    ->searchable(),
                Tables\Columns\TextColumn::make('tipe_toko')
                    ->searchable(),
                Tables\Columns\ImageColumn::make('logo')
                    ->label('Logo')
                    ->disk('public')
                    ->circular(),
                Tables\Columns\TextColumn::make('nama_bank')
                    ->searchable()
                    ->label('Nama Bank'),
                Tables\Columns\TextColumn::make('no_rekening')
                    ->searchable()
                    ->label('No. Rekening'),
                Tables\Columns\ImageColumn::make('qr_code_image')
                    ->label('QR Code')
                    ->disk('public')
                    ->visibility('public'),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                //
            ])
            ->headerActions([
                Tables\Actions\Action::make('tambah_printer')
                    ->label(fn() => Setting::first() ? 'Setting Printer' : 'Tambah Printer')
                    ->icon(fn() => Setting::first() ? 'heroicon-o-cog-6-tooth' : 'heroicon-o-plus')
                    ->color('primary')
                    ->modalHeading('Konfigurasi Printer')
                    ->fillForm(fn(): array => Setting::first()?->toArray() ?? [])
                    ->form([
                        Forms\Components\Section::make('Pengaturan Printer')
                            ->schema([
                                Forms\Components\Select::make('printer_tipe')
                                    ->label('Tipe Printer')
                                    ->options([
                                        'thermal' => 'Thermal (Receipt)',
                                        'standard' => 'Standard (Inkjet/Laser)',
                                    ])
                                    ->default('thermal')
                                    ->required(),

                                Forms\Components\Select::make('printer_lebar_kertas')
                                    ->label('Lebar Kertas')
                                    ->options([
                                        '80mm' => '80mm (3 inch)',
                                    ])
                                    ->default('80mm')
                                    ->visible(fn(Forms\Get $get) => $get('printer_tipe') === 'thermal')
                                    ->selectablePlaceholder(false),

                                Forms\Components\Select::make('printer_koneksi')
                                    ->label('Metode Koneksi')
                                    ->options([
                                        'usb' => '🔌 USB Direct Print (Local Shared Printer)',
                                        'browser' => '🌐 Bawaan Browser (Pop-up PDF)',
                                    ])
                                    ->default('usb')
                                    ->required()
                                    ->selectablePlaceholder(false),

                                Forms\Components\Select::make('printer_nama')
                                    ->label('Pilih Printer (PC)')
                                    ->placeholder('Pilih printer...')
                                    ->options(function () {
                                        try {
                                            $output = [];
                                            if (PHP_OS_FAMILY === 'Windows') {
                                                exec('powershell -command "Get-Printer | Select-Object Name | ConvertTo-Json -Compress"', $output);
                                                $json = implode('', $output);
                                                $printers = json_decode($json, true);

                                                $options = [];
                                                if ($printers) {
                                                    $data = isset($printers['Name']) ? [$printers] : $printers;
                                                    foreach ($data as $printer) {
                                                        if (isset($printer['Name'])) {
                                                            $name = $printer['Name'];
                                                            $options[$name] = $name;
                                                        }
                                                    }
                                                }
                                                return $options;
                                            }
                                        } catch (\Exception $e) {
                                        }
                                        return ['manual' => 'Masukkan nama printer manual...'];
                                    })
                                    ->searchable()
                                    ->required()
                                    ->helperText('Pilih printer yang terinstal di Windows.'),

                                // Auto Print Removed

                                Forms\Components\Textarea::make('printer_footer')
                                    ->label('Footer Struk')
                                    ->placeholder('Terima kasih atas kunjungan Anda')
                                    ->rows(3)
                                    ->columnSpanFull(),
                            ])->columns(2),
                    ])
                    ->action(function (array $data) {
                        // Ensure data consistency - force usb if not set
                        $data['printer_koneksi'] = 'usb';

                        $setting = Setting::first();
                        if ($setting) {
                            $setting->update($data);
                        } else {
                            Setting::create($data);
                        }

                        \Filament\Notifications\Notification::make()
                            ->title('Pengaturan printer disimpan')
                            ->success()
                            ->send();
                    }),
                Tables\Actions\Action::make('test_print')
                    ->label('Tes Print')
                    ->icon('heroicon-o-printer')
                    ->color('success')
                    ->url(route('pos.test-print'))
                    ->openUrlInNewTab(),
            ])
            ->actions([
                // ViewAction dan EditAction dihapus karena menggunakan Custom Header Action
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }
}
