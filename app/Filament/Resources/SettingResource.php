<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SettingResource\Pages;
use App\Filament\Resources\SettingResource\RelationManagers;
use App\Models\Setting;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

use Filament\Infolists\Infolist;
use Filament\Infolists\Components;

class SettingResource extends Resource
{
    protected static ?string $model = Setting::class;

    protected static ?string $navigationLabel = 'Pengaturan Toko';
    protected static ?string $navigationIcon = 'heroicon-o-adjustments-horizontal';
    protected static ?string $navigationGroup = 'Pengaturan';
    protected static ?int $navigationSort = 1;

    public static function canCreate(): bool
    {
        return Setting::count() < 1;
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('nama_perusahaan')
                    ->label('Nama Perusahaan')
                    ->autofocus()
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('alamat')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('kontak')
                    ->tel()
                    ->maxLength(255),
                Forms\Components\TextInput::make('pimpinan')
                    ->required()
                    ->unique(ignoreRecord: true)
                    ->minLength(3)
                    ->maxLength(50),
                Forms\Components\Select::make('tipe_toko')
                    ->options([
                        'toko umum' => 'Toko Umum',
                        'restoran' => 'Restoran',
                        'katering' => 'Katering',
                        'bengkel' => 'Bengkel',
                    ])
                    ->required(),
                Forms\Components\FileUpload::make('logo')
                    ->label('Logo Toko')
                    ->directory('logos')
                    ->disk('public')
                    ->visibility('public')
                    ->image()
                    ->maxSize(2048)
                    ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/jpg'])
                    ->imageResizeMode('cover')
                    ->imageCropAspectRatio('1:1')
                    ->imageResizeTargetWidth('500')
                    ->imageResizeTargetHeight('500')
                    ->helperText('Format: JPG, PNG. Maksimal 2MB. Disarankan 500x500px.'),
                Forms\Components\TextInput::make('nama_bank')
                    ->label('Nama Bank')
                    ->maxLength(255),
                Forms\Components\TextInput::make('no_rekening')
                    ->label('Nomor Rekening')
                    ->maxLength(255),
                Forms\Components\FileUpload::make('qr_code_image')
                    ->label('QR Code Image')
                    ->directory('qr_codes')
                    ->disk('public')
                    ->visibility('public')
                    ->image()
                    ->maxSize(1024)
                    ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/jpg'])
                    ->imageResizeMode('cover')
                    ->imageCropAspectRatio('1:1')
                    ->imageResizeTargetWidth('300')
                    ->imageResizeTargetHeight('300')
                    ->helperText('Format: JPG, PNG. Maksimal 1MB. Disarankan 300x300px.'),

                Forms\Components\Section::make('Pengaturan Printer')
                    ->schema([
                        Forms\Components\Select::make('printer_tipe')
                            ->label('Tipe Printer')
                            ->options([
                                'thermal' => 'Thermal (Receipt)',
                                // 'standard' => 'Standard (Inkjet/Laser)',
                            ])
                            ->default('thermal')
                            ->required(),
                        Forms\Components\Select::make('printer_lebar_kertas')
                            ->label('Lebar Kertas')
                            ->options([
                                // '58mm' => '58mm',
                                '80mm' => '80mm',
                            ])
                            ->visible(fn(Forms\Get $get) => $get('printer_tipe') === 'thermal')
                            ->default('80mm'),

                        // User Request: Only USB connection
                        Forms\Components\Select::make('printer_koneksi')
                            ->label('Koneksi Printer')
                            ->helperText('Metode koneksi dibatasi hanya USB Direct Print sesuai permintaan.')
                            ->options([
                                'usb' => '🔌 USB Direct Print (Local Shared Printer)',
                            ])
                            ->default('usb')
                            // ->required()
                            ->selectablePlaceholder(false), // Force selection

                        // User Request: List PC printers
                        Forms\Components\Select::make('printer_nama')
                            ->label('Nama Printer (PC)')
                            ->placeholder('Pilih printer terinstal...')
                            ->options(function () {
                                try {
                                    $output = [];
                                    // Use specific powershell command to get list of printers
                                    if (PHP_OS_FAMILY === 'Windows') {
                                        exec('powershell -command "Get-Printer | Select-Object Name | ConvertTo-Json -Compress"', $output);
                                        $json = implode('', $output);
                                        $printers = json_decode($json, true);

                                        $options = [];
                                        if ($printers) {
                                            // Iterate, handling both single object and array of objects
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
                                    // Fallback
                                }
                                return [];
                            })
                            ->searchable()
                            // ->required()
                            ->helperText('Pilih nama printer yang terinstal di komputer Windows ini.'),

                        Forms\Components\TextInput::make('printer_share_name')
                            ->label('Nama Share Printer (Windows Sharing)')
                            ->placeholder('Contoh: BP-LITE-80D')
                            ->helperText('Wajib diisi! Masukkan nama "Share name" dari tab Sharing di Printer Properties. Sesuai screenshot Anda: "BP-LITE 80D"')
                            ->visible(fn(Forms\Get $get) => $get('printer_koneksi') === 'usb')
                            ->required(fn(Forms\Get $get) => $get('printer_koneksi') === 'usb'),

                        // User Request: Remove Auto Print toggle (implied removed or forced off? request said "dihilangkan saja")
                        // We will remove the field from UI. If logic depends on it, we assume false or handle in backend.

                        Forms\Components\Textarea::make('printer_footer')
                            ->label('Footer Struk')
                            ->placeholder('Terima Kasih Atas Kunjungan Anda')
                            ->rows(3)
                            ->columnSpanFull(),

                    ])->columns(2),

                Forms\Components\Section::make('Status Operasional Toko')
                    ->description('Atur apakah toko sedang buka atau libur. Jika ditutup, sistem POS tidak bisa digunakan.')
                    ->schema([
                        Forms\Components\Toggle::make('is_toko_tutup')
                            ->label('Tutup Toko (Libur)')
                            ->helperText('Aktifkan ini untuk menutup akses transaksi di POS.')
                            ->live()
                            ->onColor('danger'),
                        Forms\Components\TextInput::make('pesan_tutup')
                            ->label('Pesan Libur / Tutup')
                            ->placeholder('Contoh: Toko sedang libur renovasi, buka kembali tanggal 5.')
                            ->visible(fn(Forms\Get $get) => $get('is_toko_tutup'))
                            ->required()
                            ->maxLength(255)
                            ->columnSpanFull(),
                    ])->columns(1),
            ]);
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Components\Section::make('Informasi Toko')
                    ->schema([
                        Components\TextEntry::make('nama_perusahaan')
                            ->label('Nama Perusahaan'),
                        Components\TextEntry::make('alamat')
                            ->label('Alamat'),
                        Components\TextEntry::make('kontak')
                            ->label('Kontak'),
                        Components\TextEntry::make('pimpinan')
                            ->label('Pimpinan'),
                        Components\TextEntry::make('tipe_toko')
                            ->label('Tipe Toko')
                            ->badge(),
                        Components\ImageEntry::make('logo')
                            ->label('Logo')
                            ->disk('public')
                            ->circular(),
                    ])->columns(2),

                Components\Section::make('Informasi Pembayaran')
                    ->schema([
                        Components\TextEntry::make('nama_bank')
                            ->label('Nama Bank'),
                        Components\TextEntry::make('no_rekening')
                            ->label('Nomor Rekening')
                            ->copyable(),
                        Components\ImageEntry::make('qr_code_image')
                            ->label('QR Code')
                            ->disk('public')
                            ->columnSpanFull(),
                    ])->columns(2),

                Components\Section::make('Pengaturan Printer')
                    ->schema([
                        Components\TextEntry::make('printer_tipe')
                            ->label('Tipe Printer')
                            ->badge(),
                        Components\TextEntry::make('printer_lebar_kertas')
                            ->label('Lebar Kertas')
                            ->visible(fn($record) => $record->printer_tipe === 'thermal'),
                        Components\TextEntry::make('printer_koneksi')
                            ->label('Metode Koneksi')
                            ->badge()
                            ->color('warning'), // Always USB now
                        Components\TextEntry::make('printer_nama')
                            ->label('Nama Printer')
                            ->default('-'),
                        // Removed Auto Cetak display
                        Components\TextEntry::make('printer_footer')
                            ->label('Footer Struk')
                            ->columnSpanFull()
                            ->default('Terima Kasih'),
                    ])->columns(2),
            ]);
    }

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

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListSettings::route('/'),
            // 'create' => Pages\CreateSetting::route('/create'),
            // 'view' => Pages\ViewSetting::route('/{record}'),
            'edit' => Pages\EditSetting::route('/{record}/edit'),
        ];
    }
}
