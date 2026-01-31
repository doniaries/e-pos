<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>Barcode Label 103</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        @page {
            size: 210mm 161mm;
            margin: 0;
        }

        body {
            font-family: Arial, sans-serif;
            background: white;
        }

        .page {
            width: 210mm;
            height: 161mm;
            padding-top: 9mm;
            /* Margin atas 0.9cm sesuai standar */
            padding-left: 3mm;
            /* Margin samping 0.3cm */
            padding-right: 3mm;
        }

        .label-container {
            display: table;
            width: 100%;
        }

        .label-row {
            display: table-row;
        }

        .label-cell {
            display: table-cell;
            width: 68mm;
            /* Pitch horizontal 6.8cm */
            height: 38mm;
            /* Pitch vertikal 3.8cm */
            vertical-align: middle;
            text-align: center;
            padding: 0;
        }

        .label {
            width: 64mm;
            /* Lebar label 6.4cm */
            height: 32mm;
            /* Tinggi label 3.2cm */
            border: 1px dashed #ccc;
            /* Border untuk visualisasi saat preview */
            display: inline-block;
            margin: 0 auto;
            padding: 0;
            page-break-inside: avoid;
            text-align: center;
            position: relative;
        }

        .label-content {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            width: 90%;
        }

        .barcode-img {
            width: auto;
            max-width: 56mm;
            height: 16mm;
            object-fit: contain;
            margin-bottom: 1mm;
        }

        .product-name {
            font-size: 8pt;
            font-weight: bold;
            text-align: center;
            line-height: 1.2;
            overflow: hidden;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            line-clamp: 2;
            -webkit-box-orient: vertical;
            width: 100%;
        }

        .barcode-code {
            font-size: 7pt;
            text-align: center;
            margin-top: 0.5mm;
            font-family: 'Courier New', monospace;
        }

        .product-price {
            font-size: 10pt;
            font-weight: bold;
            text-align: center;
            margin-bottom: 0.5mm;
            display: block;
        }

        @media print {
            .label {
                border: none;
                /* Hide border saat print */
            }

            body {
                print-color-adjust: exact;
                -webkit-print-color-adjust: exact;
            }
        }
    </style>
</head>

<body>
    <div class="page">
        <div class="label-container">
            @foreach($barcodes->chunk(3) as $rowIndex => $row)
            @if($rowIndex < 4)
                <div class="label-row">
                @foreach($row as $barcode)
                <div class="label-cell">
                    <div class="label">
                        <div class="label-content">
                            <div class="product-price">{{ $barcode['price'] }}</div>
                            <img src="data:image/png;base64,{{ $barcode['image'] }}" class="barcode-img" alt="Barcode">
                            <div class="product-name">{{ \Illuminate\Support\Str::limit($barcode['name'], 30) }}</div>
                            <div class="barcode-code">{{ $barcode['code'] }}</div>
                        </div>
                    </div>
                </div>
                @endforeach
                {{-- Fill empty cells if row has less than 3 items --}}
                @for($i = $row->count(); $i < 3; $i++)
                    <div class="label-cell">
                    <div class="label"></div>
        </div>
        @endfor
    </div>
    @endif
    @endforeach
    </div>
    </div>
</body>

</html>