<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title')</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Inter', sans-serif;
        }
    </style>
</head>

<body class="bg-gray-100 h-screen flex items-center justify-center p-4">
    <div class="max-w-md w-full bg-white rounded-2xl shadow-xl overflow-hidden text-center">
        <div class="p-8">
            <div class="mb-6 flex justify-center">
                @yield('image')
            </div>

            <h2 class="text-2xl font-bold text-gray-800 mb-2">@yield('code')</h2>
            <h3 class="text-xl font-semibold text-gray-700 mb-2">@yield('message')</h3>
            <p class="text-gray-500 mb-8">
                @yield('description', 'Maaf, terjadi kesalahan saat memproses permintaan Anda.')
            </p>

            <div class="space-y-3">
                <a href="{{ url('/') }}" class="block w-full py-3 px-4 bg-amber-500 hover:bg-amber-600 text-white font-semibold rounded-lg transition duration-200">
                    Kembali ke Beranda
                </a>
                @yield('actions')
            </div>
        </div>
        <div class="bg-gray-50 py-4 border-t border-gray-100">
            <p class="text-xs text-gray-400">@yield('footer', 'POS System Security')</p>
        </div>
    </div>
</body>

</html>