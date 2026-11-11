
@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')

    <!-- Header Dashboard -->
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-800">
            Selamat datang, {{ $user->name }}
        </h1>

        <p class="text-gray-600">
            Masuk sebagai {{ $user->roleLabel() }}
        </p>
    </div>

    <!-- Dashboard Admin -->
    @if ($user->isAdmin())

        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">

            @foreach ($jumlahPerRole as $label => $jumlah)

                <div class="bg-white rounded-lg shadow p-5">
                    <p class="text-3xl font-bold text-blue-800">
                        {{ $jumlah }}
                    </p>

                    <p class="text-gray-600 mt-1">
                        Akun {{ $label }}
                    </p>
                </div>

            @endforeach

        </div>

    <!-- Dashboard Dinas -->
    @elseif ($user->isDinas())

        <div class="bg-white rounded-lg shadow p-5 w-full max-w-xs">
            <p class="text-3xl font-bold text-blue-800">
                {{ $jumlahSekolah }}
            </p>

            <p class="text-gray-600 mt-1">
                Sekolah terdata
            </p>
        </div>

    <!-- Dashboard Sekolah -->
    @elseif ($sekolah)

        <div class="bg-white rounded-lg shadow p-5 max-w-lg">

            <h2 class="text-xl font-bold text-gray-800">
                {{ $sekolah->nama }}
            </h2>

            <p class="text-gray-600 mt-1">
                NPSN {{ $sekolah->npsn }}
                - {{ $sekolah->jenjang }}
                {{ $sekolah->status }}
            </p>

            <!-- Kelengkapan Profil -->
            <div class="mt-4">

                <div class="flex justify-between items-center mb-1">
                    <p class="text-gray-700">
                        Kelengkapan profil
                    </p>

                    <strong class="text-blue-800">
                        {{ $kelengkapan }}%
                    </strong>
                </div>

                <div class="w-full bg-gray-200 rounded-full h-3 overflow-hidden">
                    <div
                        class="bg-blue-700 h-3 rounded-full transition-all duration-300"
                        style="width: {{ $kelengkapan }}%"
                    ></div>
                </div>

            </div>

        </div>

    <!-- Akun Belum Terhubung -->
    @else

        <div class="p-4 rounded-lg bg-yellow-100 text-yellow-800">
            <p class="font-medium">
                Akun Anda belum ditautkan ke sekolah.
            </p>

            <p class="text-sm mt-1">
                Silakan hubungi admin untuk menghubungkan akun Anda dengan data sekolah.
            </p>
        </div>

    @endif

@endsection
