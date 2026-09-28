<div class="flex items-center gap-2">
    <img src="{{ asset('images/logo e-pos.png') }}" alt="Logo" class="h-10">
    <span class="font-bold text-xl">{{ \App\Models\AppInfo::first()?->nama_aplikasi ?? filament()->getBrandName() }}</span>
</div>