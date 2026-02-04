<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class ProductTemplateExport implements FromArray, WithHeadings, WithStyles, ShouldAutoSize
{
    public function array(): array
    {
        return [
            [
                '8991234567890',  // Barcode
                'SLOPE',          // Kode Tambahan
                'Contoh Produk',  // Nama Produk
                'Makanan',        // Kategori
                'PCS',            // Satuan
                10000,            // Harga Beli
                12000,            // Harga Jual
                11000,            // Harga Grosir
                100,              // Stok Awal
                10                // Stok Minimum
            ],
        ];
    }

    public function headings(): array
    {
        return [
            'Barcode',
            'Kode Tambahan',
            'Nama Produk',
            'Kategori',
            'Satuan',
            'Harga Beli',
            'Harga Jual',
            'Harga Grosir',
            'Stok Awal',
            'Stok Minimum'
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            // Style the first row as bold text.
            1    => ['font' => ['bold' => true]],
        ];
    }
}
