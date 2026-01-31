@extends('errors.layout')

@section('title', 'Kesalahan Server')
@section('code', '500')
@section('message', 'Terjadi Kesalahan Internal')
@section('description')
Maaf, terjadi kesalahan pada server kami.<br>
Tim teknis kami sedang bekerja untuk memperbaikinya.
@endsection

@section('image')
<img src="https://cdni.iconscout.com/illustration/premium/thumb/server-error-3305943-2757111.png" alt="500 Server Error" class="w-48 h-auto object-contain">
@endsection