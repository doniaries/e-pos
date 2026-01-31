@extends('errors.layout')

@section('title', 'Halaman Tidak Ditemukan')
@section('code', '404')
@section('message', 'Halaman Tidak Ditemukan')
@section('description')
Maaf, halaman yang Anda cari tidak dapat ditemukan.<br>
Mungkin halaman telah dihapus atau URL salah.
@endsection

@section('image')
<img src="https://cdni.iconscout.com/illustration/premium/thumb/404-error-3305943-2757111.png" alt="404 Not Found" class="w-48 h-auto object-contain">
@endsection