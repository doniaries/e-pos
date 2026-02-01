<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>Laporan Harian - {{ $record->tanggal->format('d/m/Y') }}</title>
    <style>
        body {
            font-family: sans-serif;
            font-size: 11pt;
            color: #333;
        }

        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 2px solid #444;
            padding-bottom: 10px;
        }

        .header h1 {
            margin: 0;
            font-size: 20px;
            text-transform: uppercase;
        }

        .info {
            border: 1px solid #eee;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            background: #fafafa;
        }

        .info-table {
            width: 100%;
            border-collapse: collapse;
        }

        .info-table td {
            padding: 5px 0;
        }

        .section-title {
            font-weight: bold;
            background: #eee;
            padding: 8px;
            margin-top: 20px;
            border-radius: 4px;
        }

        .data-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }

        .data-table td {
            padding: 8px 0;
            border-bottom: 1px solid #eee;
        }

        .val {
            font-weight: bold;
            text-align: right;
        }

        .footer {
            margin-top: 50px;
            text-align: right;
            font-style: italic;
            font-size: 10px;
        }

        .signature-box {
            margin-top: 50px;
            width: 100%;
        }

        .signature {
            text-align: center;
            width: 200px;
            float: right;
        }

        .diff-danger {
            color: #d00;
        }

        .diff-success {
            color: #080;
        }
    </style>
</head>

<body>
    @php
    $setting = $setting ?? \App\Models\Setting::first();
    $logoPath = public_path('images/logo e-pos.png');
    if ($setting && $setting->logo && file_exists(storage_path('app/public/' . $setting->logo))) {
    $logoPath = storage_path('app/public/' . $setting->logo);
    }
    @endphp
    <div class="header" style="position: relative; padding-top: 10px;">
        <img src="{{ $logoPath }}" style="position: absolute; left: 0; top: 0; width: 60px; height: auto;">
        <h1>LAPORAN TRANSAKSI HARIAN</h1>
        <h2 style="margin: 5px 0 0 0; font-size: 16px; color: #444;">{{ $setting?->nama_perusahaan ?? 'POS System' }}</h2>
        <p style="margin: 5px 0 0 0; font-size: 11px; color: #666;">{{ $setting?->alamat ?? 'Alamat Toko Belum Diatur' }}</p>
    </div>

    <div class="info">
        <table class="info-table">
            <tr>
                <td width="120">Tanggal Laporan</td>
                <td width="10">:</td>
                <td><strong>{{ $record->tanggal->format('d F Y') }}</strong></td>
                <td width="100">Jam Tutup</td>
                <td width="10">:</td>
                <td align="right"><strong>{{ $record->waktu_tutup ? $record->waktu_tutup->format('H:i') : '-' }}</strong></td>
            </tr>
            <tr>
                <td>Kasir / User</td>
                <td>:</td>
                <td>{{ $record->user->name ?? 'System' }}</td>
                <td>Dibuat Pada</td>
                <td>:</td>
                <td align="right">{{ $record->created_at->format('d/m/Y H:i') }}</td>
            </tr>
        </table>
    </div>

    @if(isset($storeStatus) && $storeStatus->is_tutup)
    <div style="background-color: #fee2e2; border: 1px solid #ef4444; color: #b91c1c; padding: 10px; border-radius: 8px; margin-bottom: 20px; text-align: center;">
        <strong>PEMBERITAHUAN:</strong> Toko tercatat <strong>TUTUP / LIBUR</strong> pada tanggal ini.<br>
        <span style="font-size: 11px;">Alasan: {{ $storeStatus->catatan ?? '-' }}</span>
    </div>
    @endif

    <div class="section-title">RINGKASAN TRANSAKSI SISTEM</div>
    <table class="data-table">
        <tr>
            <td>Jumlah Transaksi (Selesai)</td>
            <td class="val">{{ number_format($record->jumlah_transaksi) }} Trx</td>
        </tr>
        <tr>
            <td>Total Omset (Seluruh Pembayaran)</td>
            <td class="val">Rp {{ number_format($record->total_omset) }}</td>
        </tr>
        <tr>
            <td>Total Transaksi Tunai (Sistem)</td>
            <td class="val">Rp {{ number_format($record->total_tunai) }}</td>
        </tr>
        <tr>
            <td>Total Transaksi Non-Tunai</td>
            <td class="val">Rp {{ number_format($record->total_nontunai) }}</td>
        </tr>
    </table>

    <div class="section-title">REKONSILIASI KAS (FISIK)</div>
    <table class="data-table">
        <tr>
            <td>Uang Fisik Diterima Kasir</td>
            <td class="val">Rp {{ number_format($record->uang_tunai_di_laci) }}</td>
        </tr>
        <tr style="background: #fff9f9;">
            <td><strong>Selisih (Sistem vs Fisik)</strong></td>
            <td class="val">
                <span class="{{ $record->selisih == 0 ? 'diff-success' : 'diff-danger' }}">
                    Rp {{ number_format($record->selisih) }}
                    @if($record->selisih > 0) (Kurang) @elseif($record->selisih < 0) (Lebih) @else (Pas) @endif
                        </span>
            </td>
        </tr>
    </table>

    @if($record->catatan)
    <div class="section-title">CATATAN</div>
    <div style="padding: 10px; border: 1px solid #eee; margin-top: 5px;">
        {{ $record->catatan }}
    </div>
    @endif

    <div class="signature-box">
        <div class="signature">
            <p>Petugas Kasir,</p>
            <br><br><br>
            <p><strong>( {{ $record->user->name ?? '....................' }} )</strong></p>
        </div>
    </div>

    <div class="footer">
        Dicetak otomatis oleh sistem pada {{ now()->format('d/m/Y H:i:s') }}
    </div>
</body>

</html>