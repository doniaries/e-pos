<?php

namespace App\Filament\Resources\Settings\Schemas;

use Filament\Forms;
use Filament\Forms\Form as Schema;

class SettingSchema
{
    public static function form(Schema $schema): Schema
    {
        return $schema->schema([
                Forms\Components\Section::make('Informasi Aplikasi')
                    ->schema([
                        Forms\Components\TextInput::make('nama_aplikasi')
                            ->label('Nama Aplikasi')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('version')
                            ->label('Versi')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('pembuat')
                            ->label('Pembuat')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('tahun')
                            ->label('Tahun')
                            ->required()
                            ->maxLength(255),
                    ])
                    ->columns(2),
                Forms\Components\Section::make('Informasi Perusahaan')->schema([
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
                ]),
            ]);
    }
}
