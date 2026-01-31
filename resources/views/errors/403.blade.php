@extends('errors.layout')

@section('title', 'Akses Ditolak')
@section('code', '403')
@section('message', 'Akses Ditolak')
@section('description')
Maaf, Anda tidak memiliki izin untuk mengakses halaman ini.<br>
Silakan hubungi administrator jika Anda merasa ini adalah kesalahan.
@endsection

@section('image')
<img src="https://cdni.iconscout.com/illustration/premium/thumb/access-denied-3305943-2757111.png" alt="403 Forbidden" class="w-48 h-auto object-contain">
@endsection