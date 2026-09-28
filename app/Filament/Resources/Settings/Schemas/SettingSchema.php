<?php

namespace App\Filament\Resources\Settings\Schemas;

use Filament\Forms;
use Filament\Forms\Form as Schema;

class SettingSchema
{
    public static function form(Schema $schema): Schema
    {
        return $schema->schema([
            Forms\Components\Grid::make(3)->schema([
                Forms\Components\Group::make()->schema([
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
                    ])->columns(2),

                    Forms\Components\Section::make('Informasi Aplikasi')->schema([
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
                    ])->columns(2),
                ])->columnSpan(['sm' => 3, 'md' => 2]),

                Forms\Components\Group::make()->schema([
                    Forms\Components\Section::make('Pengaturan Printer')->schema([
                        Forms\Components\Select::make('printer_lebar_kertas')
                            ->label('Lebar Kertas Struk')
                            ->options([
                                '58mm' => '58mm (Kertas Kecil)',
                                '80mm' => '80mm (Kertas Besar)',
                            ])
                            ->default('58mm')
                            ->required()
                            ->helperText('Pilih ukuran kertas printer thermal Anda.'),
                        Forms\Components\Textarea::make('printer_footer')
                            ->label('Footer Struk')
                            ->placeholder('Terima Kasih Atas Kunjungan Anda')
                            ->rows(3),
                    ]),

                    Forms\Components\Section::make('Status Operasional')->schema([
                        Forms\Components\Toggle::make('is_toko_tutup')
                            ->label('Tutup Toko (Libur)')
                            ->helperText('Aktifkan ini untuk menutup akses transaksi.')
                            ->live()
                            ->onColor('danger'),
                        Forms\Components\TextInput::make('pesan_tutup')
                            ->label('Pesan Libur / Tutup')
                            ->placeholder('Contoh: Toko sedang libur renovasi...')
                            ->visible(fn (Forms\Get $get) => $get('is_toko_tutup'))
                            ->required()
                            ->maxLength(255),
                    ]),

                    Forms\Components\Section::make('Logo & Media')->schema([
                        Forms\Components\FileUpload::make('logo')
                            ->label('Logo Toko')
                            ->directory('logos')
                            ->disk('public')
                            ->visibility('public')
                            ->image()
                            ->maxSize(2048)
                            ->imageResizeMode('cover')
                            ->imageCropAspectRatio('1:1')
                            ->helperText('Format: JPG, PNG. Maks. 2MB. (Rekomendasi 500x500px)'),
                        Forms\Components\FileUpload::make('qr_code_image')
                            ->label('QR Code Pembayaran')
                            ->directory('qr_codes')
                            ->disk('public')
                            ->visibility('public')
                            ->image()
                            ->maxSize(1024)
                            ->helperText('Format: JPG, PNG. Maks. 1MB. (Rekomendasi 300x300px)'),
                    ])->collapsed(),

                    Forms\Components\Section::make('Rekening Bank')->schema([
                        Forms\Components\TextInput::make('nama_bank')
                            ->label('Nama Bank')
                            ->maxLength(255),
                        Forms\Components\TextInput::make('no_rekening')
                            ->label('Nomor Rekening')
                            ->maxLength(255),
                    ])->collapsed(),
                ])->columnSpan(['sm' => 3, 'md' => 1]),
            ]),
        ]);
    }
}
