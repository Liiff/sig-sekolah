<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Masuk - SIG Sekolah</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="min-h-screen bg-gray-100 flex items-center justify-center p-4">

    <div class="w-full max-w-md bg-white rounded-lg shadow-lg p-8">

        <!-- Header -->
        <div class="mb-6">
            <h1 class="text-2xl font-bold text-blue-800">
                SIG Sekolah
            </h1>

            <p class="text-gray-600 mt-1">
                Masuk untuk mengelola data sekolah.
            </p>
        </div>

        <!-- Form Login -->
        <form method="POST"
              action="{{ route('login') }}"
              class="space-y-4">

            @csrf

            <!-- Email -->
            <div>
                <label for="email" class="block font-medium mb-1">
                    Email
                </label>

                <input
                    type="email"
                    id="email"
                    name="email"
                    value="{{ old('email') }}"
                    required
                    autofocus
                    autocomplete="username"
                    class="w-full border border-gray-300 rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
                >

                @error('email')
                    <p class="text-red-600 text-sm mt-1">
                        {{ $message }}
                    </p>
                @enderror
            </div>

            <!-- Password -->
            <div>
                <label for="password" class="block font-medium mb-1">
                    Kata Sandi
                </label>

                <input
                    type="password"
                    id="password"
                    name="password"
                    required
                    autocomplete="current-password"
                    class="w-full border border-gray-300 rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
                >

                @error('password')
                    <p class="text-red-600 text-sm mt-1">
                        {{ $message }}
                    </p>
                @enderror
            </div>

            <!-- Remember Me -->
            <div>
                <label class="flex items-center gap-2 text-sm text-gray-700">
                    <input
                        type="checkbox"
                        name="remember"
                        value="1"
                        class="rounded border-gray-300"
                    >

                    Ingat saya
                </label>
            </div>

            <!-- Button Login -->
            <button
                type="submit"
                class="w-full py-2 bg-blue-700 text-white rounded hover:bg-blue-800 transition duration-200"
            >
                Masuk
            </button>

        </form>

        <!-- Back to Public Map -->
        <div class="mt-4">
            <a
                href="{{ url('/') }}"
                class="block text-sm text-blue-700 hover:text-blue-900 hover:underline"
            >
                Kembali ke peta publik
            </a>
        </div>

    </div>

</body>
</html>
