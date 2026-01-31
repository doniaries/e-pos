@extends('errors.layout')

@section('title', 'Dalam Pemeliharaan')
@section('code', '503')
@section('message', 'Layanan Sedang Dalam Pemeliharaan')
@section('description')
Maaf, layanan sedang tidak tersedia untuk sementara waktu.<br>
Silakan kembali lagi nanti.
@endsection

@section('image')
<img src="https://cdni.iconscout.com/illustration/premium/thumb/maintenance-3305943-2757111.png" alt="503 Maintenance" class="w-48 h-auto object-contain">
@endsection