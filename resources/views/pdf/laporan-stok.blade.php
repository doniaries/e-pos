<!DOCTYPE html>
<html>

<head>
    <title>Laporan Riwayat Stok</title>
    <style>
        body {
            font-family: sans-serif;
            font-size: 11pt;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        th,
        td {
            border: 1px solid #000;
            padding: 5px;
            text-align: left;
        }

        th {
            background-color: #f2f2f2;
        }

        .header {
            text-align: center;
            margin-bottom: 20px;
        }

        .success {
            color: green;
            font-weight: bold;
        }

        .danger {
            color: red;
            font-weight: bold;
        }

        .meta {
            margin-bottom: 20px;
        }
    </style>
</head>

<body>
    <div class="header" style="position: relative; padding-top: 10px;">
        <img src="{{ $logoPath ?? public_path('images/logo e-pos.png') }}" style="position: absolute; left: 0; top: 0; width: 80px; height: auto;">
        <h2 style="margin: 0;">{{ strtoupper($storeName) }}</h2>
        <p style="margin: 5px 0;">{{ $storeAddress }}</p>
        <hr>
    </div>

    <div class="meta">
        <p><strong>Periode:</strong> {{ \Carbon\Carbon::parse($start)->translatedFormat('d F Y') }} - {{ \Carbon\Carbon::parse($end)->translatedFormat('d F Y') }}</p>
        <p><strong>Tanggal Cetak:</strong> {{ now()->translatedFormat('d F Y H:i') }}</p>
    </div>

    <table>
        <thead>
            <tr>
                <th style="width: 15%">Tanggal</th>
                <th style="width: 25%">Produk</th>
                <th style="width: 10%">Jenis</th>
                <th style="width: 10%; text-align: center">Mutasi</th>
                <th style="width: 10%; text-align: center">Sisa</th>
                <th style="width: 15%">Keterangan</th>
                <th style="width: 15%">Admin/User</th>
            </tr>
        </thead>
        <tbody>
            @foreach($records as $row)
            <tr>
                <td>{{ $row->created_at->format('d/m/Y H:i') }}</td>
                <td>{{ $row->produk->nama ?? '-' }}</td>
                <td>{{ ucfirst($row->jenis) }}</td>
                <td style="text-align: center">
                    <span class="{{ $row->jenis == 'pembelian' ? 'success' : 'danger' }}">
                        {{ $row->jenis == 'pembelian' ? '+' : '-' }}{{ number_format($row->jumlah) }}
                    </span>
                </td>
                <td style="text-align: center">{{ number_format($row->stok_akhir) }}</td>
                <td>{{ $row->keterangan }}</td>
                <td>{{ $row->user->name ?? 'System' }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    @if(isset($storeStatuses) && $storeStatuses->count() > 0)
    <div style="margin-top: 20px;">
        <h4 style="margin-bottom: 5px; color: #ef4444;">Riwayat Toko Tutup / Libur:</h4>
        <table style="margin-top: 0; border: 1px solid #ef4444;">
            <thead style="background-color: #fee2e2;">
                <tr>
                    <th style="width: 20%; color: #b91c1c; border-color: #fca5a5;">Tanggal</th>
                    <th style="color: #b91c1c; border-color: #fca5a5;">Keterangan / Alasan</th>
                </tr>
            </thead>
            <tbody>
                @foreach($storeStatuses as $status)
                <tr>
                    <td style="border-color: #fca5a5;">{{ \Carbon\Carbon::parse($status->tanggal)->translatedFormat('d F Y') }}</td>
                    <td style="border-color: #fca5a5;">{{ $status->catatan ?? 'Toko Libur / Tutup' }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @endif
</body>

</html>