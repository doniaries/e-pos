<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title ?? 'Struk' }}</title>
    <style>
        /* Reset total */
        html,
        body {
            margin: 0 !important;
            padding: 0 !important;
            height: auto;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        @page {
            size: auto;
            margin: 0;
        }

        body {
            font-family: 'Courier New', Courier, monospace;
            font-size: 11px;
            line-height: 1.1;

            width: {{ $setting->printer_lebar_kertas ?? '58mm' }};
            background: white;
            color: black;
            overflow-wrap: break-word;
            word-wrap: break-word;
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

        .header {
            margin-bottom: 2px;
            padding: 0;
            margin-top: 0;
            display: block;
        }

        .header h1 {
            font-size: 14px;
            margin: 0;
            padding: 0;
            line-height: 1.0;
        }

        .header p {
            font-size: 10px;
            margin: 0;
            padding: 0;
            line-height: 1.0;
        }

        .separator {
            border-top: 1px dashed #000;
            margin: 2px 0;
        }

        .info {
            margin: 2px 0;
        }

        .info-row {
            display: flex;
            justify-content: space-between;
        }

        .items {
            margin: 5px 0;
        }

        .item {
            margin-bottom: 5px;
        }

        .item-name {
            font-weight: bold;
            display: block;
            margin-bottom: 2px;
            white-space: normal;
        }

        .item-details {
            display: flex;
            justify-content: space-between;
            padding-left: 5px;
        }

        .totals {
            margin-top: 2px;
        }

        .total-row {
            display: flex;
            justify-content: space-between;
            margin: 2px 0;
        }

        .total-row.grand-total {
            font-weight: bold;
            font-size: 13px;
            border-top: 1px dashed #000;
            padding-top: 2px;
        }

        .footer {
            margin-top: 10px;
            text-align: center;
            font-size: 10px;
        }

        .reprint-badge {
            font-style: italic;
            font-size: 8px;
            margin-bottom: 5px;
        }

        @media print {
            .no-print {
                display: none !important;
            }

            body {
                width: {{ ($setting->printer_lebar_kertas ?? '58mm') === '80mm' ? '72mm' : '52mm' }};
                margin: 0 !important;
                padding: 0 !important;
                padding-left: 2mm !important;
                padding-right: 2mm !important;
            }

            /* Hide browser headers/footers */
            @page {
                margin: 0;
            }
        }

        .btn-print {
            position: fixed;
            top: 10px;
            right: 10px;
            background: #2563eb;
            color: white;
            padding: 5px 10px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-weight: bold;
            font-family: sans-serif;
            font-size: 12px;
            z-index: 9999;
        }

        ::-webkit-scrollbar {
            display: none;
        }
    </style>
</head>

<body onload="handlePrint()">
    {{ $slot }}

    <script>
        function handlePrint() {
            setTimeout(() => {
                window.print();
            }, 300);
        }

        document.addEventListener('keydown', function(event) {
            if (event.key === 'Enter') {
                window.print();
            }
        });
    </script>
</body>

</html>