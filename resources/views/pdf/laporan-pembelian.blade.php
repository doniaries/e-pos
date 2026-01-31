<!DOCTYPE html>
<html>

<head>
    <title>Laporan Pembelian</title>
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
        <h3 style="margin-top: 10px;">Laporan Pembelian</h3>
        <p>Periode: {{ \Carbon\Carbon::parse($start)->translatedFormat('d F Y') }} - {{ \Carbon\Carbon::parse($end)->translatedFormat('d F Y') }}</p>
    </div>

    <table>
        <thead>
            <tr>
                <th>No</th>
                <th>Tanggal</th>
                <th>No Pembelian</th>
                <th>Distributor</th>
                <th>Admin</th>
                <th style="text-align: right;">Total</th>
            </tr>
        </thead>
        <tbody>
            @php $grandTotal = 0; @endphp
            @foreach($records as $index => $row)
            <tr>
                <td>{{ $index + 1 }}</td>
                <td>{{ \Carbon\Carbon::parse($row->tanggal_pembelian)->format('d/m/Y') }}</td>
                <td>{{ $row->nomor_pembelian }}</td>
                <td>{{ $row->distributor->nama_distributor ?? 'Umum' }}</td>
                <td>{{ $row->user->name ?? '-' }}</td> <!-- Assumes created_by linked manually or via relation but standard is user usually -->
                <td style="text-align: right;">Rp {{ number_format($row->total_harga, 0, ',', '.') }}</td>
            </tr>
            @php $grandTotal += $row->total_harga; @endphp
            @endforeach
            <tr class="total-row">
                <td colspan="5" style="text-align: right;">GRAND TOTAL</td>
                <td style="text-align: right;">Rp {{ number_format($grandTotal, 0, ',', '.') }}</td>
            </tr>
        </tbody>
    </table>
</body>

</html>