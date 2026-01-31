<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>POS System</title>

    @vite(['resources/css/app.css'])
    @livewireStyles
    <style>
        [x-cloak] {
            display: none !important;
        }
    </style>
</head>

<body>
    @php
    $setting = \App\Models\Setting::first();
    $namaToko = $setting->nama_perusahaan ?? 'Nama Toko';
    $alamatToko = $setting->alamat ?? 'Alamat Toko';
    @endphp

    <!-- Fixed Header -->
    <div class="fixed top-0 z-50 w-full px-6 py-3 text-white bg-blue-600">
        <div class="flex items-center justify-between">
            <div class="w-1/3 flex justify-start">
                <div class="bg-blue-800/80 backdrop-blur-md px-5 py-2 rounded-2xl border border-white/20 shadow-lg inline-block">
                    <h1 class="text-xl font-black text-white leading-tight">{{ $namaToko }}</h1>
                    <p class="text-[10px] text-blue-200 font-bold uppercase tracking-widest opacity-90">{{ $alamatToko }}</p>
                </div>
            </div>
            <div class="w-1/3 text-center">
                <img src="{{ route('logo') }}?v={{ $setting->updated_at->timestamp ?? time() }}" alt="Logo" class="h-10 mx-auto mb-1 bg-white rounded p-1 object-contain">
            </div>
            <div class="w-1/3 flex justify-end">
                <div class="bg-blue-800/80 backdrop-blur-md px-5 py-2.5 rounded-2xl border border-white/20 shadow-lg inline-block text-right">
                    <div id="current-date" class="text-[11px] text-blue-200 font-bold uppercase tracking-wider mb-0.5"></div>
                    <div id="current-time" class="text-3xl font-black tracking-tighter text-white leading-none"></div>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Content -->
    <div class="pt-20">
        {{ $slot }}
    </div>

    @livewireStyles
    @filamentScripts

    <script>
        function updateDateTime() {
            const now = new Date();
            const options = {
                weekday: 'long',
                year: 'numeric',
                month: 'long',
                day: 'numeric'
            };

            const currentTimeEl = document.getElementById('current-time');
            const currentDateEl = document.getElementById('current-date');

            if (currentTimeEl) {
                currentTimeEl.textContent = now.toLocaleTimeString('id-ID');
            }
            if (currentDateEl) {
                currentDateEl.textContent = now.toLocaleDateString('id-ID', options);
            }
        }

        setInterval(updateDateTime, 1000);
        updateDateTime();
    </script>
</body>

</html>