<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Test Print</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        @page {
            size: auto;
            margin: 0mm;
        }

        body {
            font-family: 'Courier New', monospace;
            font-size: 11px;
            line-height: 1.4;

            width: {
                    {
                    ($setting->printer_lebar_kertas ?? '58mm')=='80mm' ? '80mm': '58mm'
                }
            }

            ;
            padding: 2mm;
            /* padding-bottom: 20mm;  Removed to avoid extra length if not needed, relying on content */
            background: white;
            height: auto;
        }

        .header {
            margin-bottom: 10px;
            border-bottom: 1px dashed #000;
            padding-bottom: 5px;
        }

        .bold {
            font-weight: bold;
        }

        .text-center {
            text-align: center;
        }

        .text-right {
            text-align: right;
        }

        .items {
            border-top: 1px dashed #000;
            border-bottom: 1px dashed #000;
            padding: 5px 0;
            margin: 5px 0;
        }

        .footer {
            margin-top: 10px;
            text-align: center;
            font-size: 10px;
            border-top: 1px dashed #000;
            padding-top: 5px;
        }

        @media print {
            body {
                width: {
                        {
                        ($setting->printer_lebar_kertas ?? '58mm')=='80mm' ? '80mm': '58mm'
                    }
                }

                ;
            }

            .no-print {
                display: none;
            }
        }

        .print-button {
            position: fixed;
            top: 10px;
            right: 10px;
            padding: 8px 15px;
            background: #4CAF50;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 12px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
        }
    </style>
</head>

<body>
    <button class="print-button no-print" onclick="window.print()">🖨️ Print</button>

    <div class="header text-center">
        <h2 class="bold">TEST PRINT</h2>
        <p>{{ $setting->nama_perusahaan ?? 'TOKO' }}</p>
        <p>{{ now()->format('d/m/Y H:i:s') }}</p>
    </div>

    <div class="items text-center">
        <p>Printer OK!</p>
        <p>Lebar Kertas: {{ $setting->printer_lebar_kertas ?? '58mm' }}</p>
        <p>Koneksi: {{ $setting->printer_koneksi ?? 'browser' }}</p>
        <br>
        <p>Jika Anda bisa membaca ini,</p>
        <p>berarti printer Anda berfungsi.</p>
    </div>

    <div class="footer">
        @if($setting->printer_footer)
        {!! nl2br(e($setting->printer_footer)) !!}
        @else
        <p class="bold">Terima Kasih</p>
        @endif
    </div>

    <script>
        window.onload = function() {
            window.print();
        };
    </script>
</body>

</html>