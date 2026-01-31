<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Struk {{ $penjualan->nomor }}</title>
    <style>
        @page {
            margin: 0;
            size: 80mm auto;
        }

        body {
            font-family: 'Courier New', Courier, monospace;
            font-size: 11px;
            /* Ukuran font disesuaikan agar muat */
            margin: 0 auto;
            padding: 0;
            width: 72mm;
            /* 80mm kertas - margin hardware kiri kanan */
            color: #000;
        }

        .text-center {
            text-align: center;
        }

        .text-right {
            text-align: right;
        }

        .bold {
            font-weight: bold;
        }

        .uppercase {
            text-transform: uppercase;
        }

        .dashed-line {
            border-top: 1px dashed #000;
            margin: 1px 0;
            width: 100%;
        }

        .header {
            margin-bottom: 2px;
        }

        .store-name {
            font-size: 16px;
            font-weight: 800;
            margin-bottom: 0px;
        }

        .store-info {
            font-size: 10px;
        }

        /* Meta Info: Flexbox untuk Kiri-Kanan Sejajar */
        .meta-info {
            display: flex;
            justify-content: space-between;
            font-size: 10px;
            margin-bottom: 5px;
        }

        .item-container {
            margin-bottom: 3px;
        }

        .item-name {
            display: block;
            margin-bottom: 2px;
        }

        .item-details {
            display: flex;
            justify-content: space-between;
        }

        .totals-section {
            display: flex;
            justify-content: flex-end;
            /* Rata Kanan */
            margin-top: 3px;
        }

        .totals-table {
            width: 100%;
            border-collapse: collapse;
        }

        .totals-table td {
            text-align: right;
            padding: 1px 0;
        }

        /* Kolom Label (Subtotal) */
        .col-label {
            width: 60%;
        }

        /* Kolom Rupiah dan Angka */
        .col-rp {
            width: 10%;
            text-align: left !important;
        }

        .col-val {
            width: 30%;
        }

        .footer {
            margin-top: 5px;
            font-size: 10px;
        }
    </style>
</head>

<body onload="window.print()">

    <div class="header text-center">
        <div class="store-name uppercase">{{ $setting->nama_perusahaan ?? 'TOKO ABANG' }}</div>
        <div class="store-info uppercase bold">{{ $setting->slogan ?? 'GROSIR SEMBAKO' }}</div>
        <div class="store-info">{{ $setting->alamat ?? '-' }}</div>
    </div>

    <div class="dashed-line"></div>

    <!-- Meta Info: Split Layout -->
    <div style="margin-bottom: 8px; font-size: 10px;">
        <div style="text-align: center; margin-bottom: 4px;">No. Struk : {{ $penjualan->nomor }}</div>
        <div style="display: flex; justify-content: space-between;">
            <div>{{ $penjualan->created_at->format('d.m.Y-H:i:s') }}</div>
            <div style="text-transform: uppercase;">KASIR: {{ $penjualan->kasir->name ?? 'Admin' }}</div>
        </div>
    </div>

    <div class="dashed-line"></div>

    <div class="items-list">
        @foreach($penjualan->details as $detail)
        <div class="item-container">
            <div class="item-name">{{ $detail->produk->nama ?? 'Item' }}</div>
            <div class="item-details">
                <div>
                    {{ number_format($detail->jumlah, 0, ',', '.') }}
                    {{ $detail->produk->satuan->nama ?? 'Kg' }} X
                    {{ number_format($detail->harga, 0, ',', '.') }}
                </div>
                <div style="white-space: nowrap;">
                    Rp. {{ number_format($detail->subtotal, 0, ',', '.') }}
                </div>
            </div>
        </div>
        @endforeach
    </div>

    <div class="dashed-line"></div>

    <!-- Total Item: Standalone Block (Right Aligned) -->
    <div style="text-align: right; width: 100%; font-size: 10px; margin-bottom: 2px;">
        Total Item: {{ $penjualan->details->count() }}
    </div>

    <div class="totals-section">
        <table class="totals-table">
            <tr>
                <td class="col-label bold">Total</td>
                <td class="col-rp">Rp.</td>
                <td class="col-val">{{ number_format($penjualan->subtotal, 0, ',', '.') }}</td>
            </tr>

            @if($penjualan->diskon_nilai > 0)
            <tr>
                <td class="col-label bold">Diskon</td>
                <td class="col-rp">Rp.</td>
                <td class="col-val">-{{ number_format($penjualan->diskon_nilai, 0, ',', '.') }}</td>
            </tr>
            @endif

            <tr class="bold">
                <td class="col-label">Bayar</td>
                <td class="col-rp">Rp.</td>
                <td class="col-val" style="font-weight: 900;">{{ number_format($penjualan->bayar, 0, ',', '.') }}</td>
            </tr>

            <tr>
                <td class="col-label">Kembali</td>
                <td class="col-rp">Rp.</td>
                <td class="col-val">{{ number_format($penjualan->kembali, 0, ',', '.') }}</td>
            </tr>
        </table>
    </div>

    <div class="dashed-line"></div>

    <div class="footer text-center">
        <div style="letter-spacing: 2px;">{{ strtoupper($setting->printer_footer ?? 'TERIMA KASIH') }}</div>
    </div>

</body>

</html>