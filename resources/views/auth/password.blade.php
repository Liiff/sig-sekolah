@extends('layouts.app')
@section('title', 'Ubah Kata Sandi')
@section('content')
@php $cls = 'w-full border rounded px-3 py-2'; @endphp
<h1 class="text-2xl font-bold mb-4">Ubah Kata Sandi</h1>
<form method="POST" action="{{ route('password.update') }}"
      class="bg-white rounded shadow p-6 max-w-lg space-y-4">
    @csrf
    @method('PUT')
    @foreach ([
        'current_password' => 'Kata Sandi Saat Ini',
        'password' => 'Kata Sandi Baru',
        'password_confirmation' => 'Ulangi Kata Sandi Baru',
    ] as $name => $label)
        <div>
            <label for="{{ $name }}" class="block font-medium">{{ $label }}</label>
            <input type="password" id="{{ $name }}" name="{{ $name }}"
                   class="{{ $cls }}" autocomplete="{{ $name === 'current_password' ? 'current-password' : 'new-password' }}">
            @error($name)<p class="text-red-600 text-sm">{{ $message }}</p>@enderror
        </div>
    @endforeach
    <button class="px-5 py-2 bg-blue-700 text-white rounded">Simpan</button>
</form>
@endsection