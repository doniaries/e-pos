<!DOCTYPE html>
<html>

<head>
    <title>Laporan Penjualan</title>
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
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }

        th {
            background-color: #f2f2f2;
        }

        .header {
            text-align: center;
            margin-bottom: 20px;
        }

        .total-row {
            font-weight: bold;
            background-color: #fafafa;
        }
    </style>
</head>

<body>
    <div class="header" style="position: relative; padding-top: 10px;">
        <img src="{{ $logoPath ?? public_path('images/logo e-pos.png') }}" style="position: absolute; left: 0; top: 0; width: 80px; height: auto;">
        <h2 style="margin: 0;">{{ strtoupper($storeName) }}</h2>
        <p style="margin: 5px 0;">{{ $storeAddress }}</p>
        <hr>
        <h3 style="margin-top: 10px;">Laporan Penjualan</h3>
        <p>Periode: {{ \Carbon\Carbon::parse($start)->translatedFormat('d F Y') }} - {{ \Carbon\Carbon::parse($end)->translatedFormat('d F Y') }}</p>
    </div>

    <table>
        <thead>
            <tr>
                <th style="width: 5%;">No</th>
                <th style="width: 15%;">Tanggal</th>
                <th style="width: 15%;">No Faktur</th>
                <th style="width: 15%;">Pelanggan</th>
                <th style="width: 10%;">Kasir</th>
                <th style="width: 10%;">Metode</th>
                <th style="width: 10%;">Status</th>
                <th style="text-align: right; width: 20%;">Total</th>
            </tr>
        </thead>
        <tbody>
            @php $grandTotal = 0; @endphp
            @foreach($records as $index => $row)
            <tr>
                <td>{{ $index + 1 }}</td>
                <td>{{ $row->created_at->format('d/m/Y H:i') }}</td>
                <td>{{ $row->nomor }}</td>
                <td>{{ $row->pelanggan->nama_pelanggan ?? 'Umum' }}</td>
                <td>{{ $row->kasir->name ?? '-' }}</td>
                <td>{{ ucfirst($row->metode_pembayaran) }}</td>
                <td>{{ ucfirst($row->status) }}</td>
                <td style="text-align: right;">Rp {{ number_format($row->total, 0, ',', '.') }}</td>
            </tr>
            @php $grandTotal += $row->total; @endphp
            @endforeach
            <tr class="total-row">
                <td colspan="7" style="text-align: right;">GRAND TOTAL</td>
                <td style="text-align: right;">Rp {{ number_format($grandTotal, 0, ',', '.') }}</td>
            </tr>
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

    <div style="margin-top: 50px; float: right; width: 200px; text-align: center;">
        <p>{{ now()->translatedFormat('d F Y') }}</p>
        <p>Kasir Bertugas,</p>
        <br><br><br>
        <p style="font-weight: bold; text-decoration: underline;">{{ strtoupper($userName) }}</p>
    </div>
</body>

</html>