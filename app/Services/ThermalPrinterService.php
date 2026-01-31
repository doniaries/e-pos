<?php

namespace App\Services;

use App\Models\Penjualan;
use App\Models\Setting;
use Illuminate\Support\Facades\Log;
use Mike42\Escpos\Printer;
use Mike42\Escpos\PrintConnectors\WindowsPrintConnector;
use Mike42\Escpos\PrintConnectors\NetworkPrintConnector;
use Mike42\Escpos\PrintConnectors\DummyPrintConnector;
use Exception;

class ThermalPrinterService
{
    protected $setting;
    protected $printerWidth;

    public function __construct()
    {
        $this->setting = Setting::first();

        // PERBAIKAN UTAMA:
        // Ubah dari 64 ke 48.
        // Standar kertas 80mm dengan Font Normal (Font A) adalah 48 Karakter.
        $this->printerWidth = 48;
    }

    protected function formatReceipt(Penjualan $penjualan, Printer $printer, bool $isReprint = false): void
    {
        $width = $this->printerWidth;

        // ================= HEADER =================
        $printer->setJustification(Printer::JUSTIFY_CENTER);

        // Nama Toko (Besar & Bold)
        $printer->setEmphasis(true);
        $printer->setTextSize(2, 2);
        $printer->text(($this->setting->nama_perusahaan ?? 'TOKO ABANG') . "\n");
        $printer->setTextSize(1, 1);
        $printer->setEmphasis(false);

        // Slogan
        if ($this->setting->slogan) {
            $printer->setEmphasis(true);
            $printer->text(strtoupper($this->setting->slogan) . "\n");
            $printer->setEmphasis(false);
        }

        // Alamat
        if ($this->setting->alamat) {
            $printer->text(($this->setting->alamat) . "\n");
        }

        // Kontak
        if ($this->setting->kontak) {
            $printer->text("No. Telp " . $this->setting->kontak . "\n");
        }

        // Garis (Sekarang panjangnya pas 48 char)
        $printer->text(str_repeat('-', $width) . "\n");

        // No. Struk centered
        $printer->setJustification(Printer::JUSTIFY_CENTER);
        $printer->text("No. Struk : " . $penjualan->nomor . "\n");

        // Date and Cashier on one line (split)
        $date = $penjualan->created_at->format('d.m.Y-H:i:s');
        $kasir = "KASIR: " . strtoupper($penjualan->kasir->name ?? 'Admin');
        $this->printDoubleColumn($printer, $date, $kasir, $width);

        // Garis pemisah setelah tanggal/kasir
        $printer->text(str_repeat('-', $width) . "\n");

        // ================= DAFTAR ITEM =================
        foreach ($penjualan->details as $detail) {
            $printer->setJustification(Printer::JUSTIFY_LEFT);

            // Baris 1: Nama Barang
            $namaProduk = $detail->produk->nama ?? 'Item';
            $printer->text($namaProduk . "\n");

            // Baris 2: Qty x Harga (Kiri) ....... Total (Kanan)

            $qty = number_format((float)$detail->jumlah, 0, ',', '.');
            // Jika ada satuan pakai ini, jika tidak kosongkan
            $satuan = $detail->produk->satuan->nama ?? 'Kg';
            $harga = number_format((float)$detail->harga, 0, ',', '.');
            $subtotalItem = number_format((float)$detail->subtotal, 0, ',', '.');

            // Format Kiri: "4.000 Kg X 12.500"
            $leftText = "$qty $satuan X $harga";

            // Format Kanan: "Rp. 50.000"
            $rightText = "Rp. " . $subtotalItem;

            $this->printDoubleColumn($printer, $leftText, $rightText, $width);
        }

        // Garis Penutup Item
        $printer->text(str_repeat('-', $width) . "\n");

        // ================= TOTAL ITEM & TOTALAN =================
        $printer->setJustification(Printer::JUSTIFY_RIGHT);

        // Add Total Item count (Right Aligned)
        // Manual spacing or just right align text
        $totalItem = $penjualan->details->count();
        $printer->text("Total Item: " . $totalItem . "\n");

        $subtotal = number_format((float)$penjualan->subtotal, 0, ',', '.');
        $bayar = number_format((float)$penjualan->bayar, 0, ',', '.');
        $kembali = number_format((float)$penjualan->kembali, 0, ',', '.');

        // Helper untuk print baris total rata kanan
        $printer->setEmphasis(true);
        $printer->text($this->formatRightAlignedRow("Total", $subtotal, $width));
        $printer->setEmphasis(false);

        if ($penjualan->diskon_nilai > 0) {
            $diskon = "-" . number_format((float)$penjualan->diskon_nilai, 0, ',', '.');
            $printer->text($this->formatRightAlignedRow("Diskon", $diskon, $width));
        }

        // Bayar: Tebalkan saja (Tanpa Double Size agar tidak berantakan)
        $printer->selectPrintMode(Printer::MODE_EMPHASIZED);
        $printer->text($this->formatRightAlignedRow("Bayar", $bayar, $width));
        $printer->selectPrintMode();

        // Kembali: Tebalkan saja
        $printer->selectPrintMode(Printer::MODE_EMPHASIZED);
        $printer->text($this->formatRightAlignedRow("Kembali", $kembali, $width));
        $printer->selectPrintMode(); // Default
        $printer->setEmphasis(false);

        // GARIS PEMBATAS FOOTER
        $printer->setJustification(Printer::JUSTIFY_CENTER);
        $printer->text(str_repeat("-", $width) . "\n");

        $footerMsg = $this->setting->printer_footer ?? "TERIMA KASIH";

        // Manual spacing for "wide" look if it's the default "TERIMA KASIH"
        if (strtoupper($footerMsg) === 'TERIMA KASIH') {
            $printer->text("T E R I M A  K A S I H\n");
        } else {
            $printer->text(strtoupper($footerMsg) . "\n");
        }

        $printer->text("ATAS KUNJUNGAN ANDA\n");

        if ($isReprint) {
            $printer->text("*** PRINT ULANG ***\n");
        }

        $printer->feed(1); // Reduced from 3 to 1
        $printer->cut();
    }

    /**
     * Helper: Mencetak teks Kiri dan Kanan dalam satu baris
     * Mengelola spasi agar tidak wrap ke bawah
     */
    private function printDoubleColumn($printer, $left, $right, $maxWidth)
    {
        $lenLeft = strlen($left);
        $lenRight = strlen($right);

        $spaces = $maxWidth - $lenLeft - $lenRight;

        // Jika teks terlalu panjang, sisakan 1 spasi (ini akan wrap jika over, tapi lebih aman)
        if ($spaces < 1) {
            $spaces = 1;
        }

        $printer->text($left . str_repeat(' ', $spaces) . $right . "\n");
    }

    /**
     * Helper: Format Rata Kanan (Simulasi Tabel)
     * Output: "       Subtotal   Rp. 200.000"
     */
    private function formatRightAlignedRow($label, $value, $maxWidth)
    {
        // Lebar area "Rp. Nominal". Di 48 char, kita kasih sekitar 18-20 char.
        $valueWidth = 18;

        // Buat string nominal: "Rp.        200.000"
        // str_pad LEFT akan mendorong angka ke kanan
        $formattedValue = "Rp. " . str_pad($value, $valueWidth - 4, " ", STR_PAD_LEFT);

        // Gabungkan Label + Spasi + Value
        // Karena kita sudah setJustification(RIGHT), kita hanya perlu spasi antara label dan value
        // Tapi agar lurus "Rp"-nya, kita bisa akali:

        return $label . "   " . $formattedValue . "\n";
    }

    public function getReceiptBinary(Penjualan $penjualan, bool $isReprint = false)
    {
        $connector = new DummyPrintConnector();
        $printer = new Printer($connector);
        $printer->setPrintLeftMargin(0);
        $this->formatReceipt($penjualan, $printer, $isReprint);
        $data = $connector->getData();
        $printer->close();
        return base64_encode($data);
    }
    public function printReceipt(Penjualan $penjualan, bool $isReprint = false): array
    {
        try {
            $connector = $this->getConnector();
            $printer = new Printer($connector);
            $printer->setPrintLeftMargin(0);
            $this->formatReceipt($penjualan, $printer, $isReprint);
            $printer->close();
            return ['success' => true, 'message' => 'Print berhasil'];
        } catch (Exception $e) {
            return ['success' => false, 'message' => 'Print gagal: ' . $e->getMessage()];
        }
    }

    protected function getConnector()
    {
        if (!$this->setting) {
            throw new Exception('Pengaturan printer belum dikonfigurasi');
        }

        $koneksi = $this->setting->printer_koneksi ?? 'usb';

        switch ($koneksi) {
            case 'usb':
            case 'bluetooth': // Treat bluetooth specific setting same as direct windows print if using shared name
                // Windows printer name
                if (empty($this->setting->printer_nama)) {
                    throw new Exception('Nama printer belum dikonfigurasi');
                }

                $printerName = $this->setting->printer_nama;
                // Prioritize explicit share name if provided check settings or fall back to printer name
                $shareName = $this->setting->printer_share_name ?? $this->setting->printer_nama;

                // Allow simple COM/LPT ports or existing smb:// paths to pass through
                if (!preg_match('/^(LPT|COM)\d+$/i', $printerName) && !str_starts_with($printerName, 'smb://')) {
                    // Force SMB format for standard Windows printer names
                    // This REQUIRES the printer to be SHARED in Windows with this exact name
                    $printerName = "smb://localhost/" . $shareName;
                }

                // Use CustomWindowsPrintConnector if available, or fallback to library's WindowsPrintConnector
                if (class_exists(\App\Services\CustomWindowsPrintConnector::class)) {
                    return new \App\Services\CustomWindowsPrintConnector($printerName);
                }

                return new WindowsPrintConnector($printerName);

            case 'network':
                // Network printer
                $ip = $this->setting->printer_ip_address ?? null;
                $port = $this->setting->printer_port ?? 9100;

                if (empty($ip)) {
                    throw new Exception('IP address printer belum dikonfigurasi');
                }

                return new NetworkPrintConnector($ip, $port);

            default:
                // Fallback for browser or unknown
                if (!empty($this->setting->printer_nama)) {
                    return new WindowsPrintConnector($this->setting->printer_nama);
                }
                return new DummyPrintConnector();
        }
    }
}
