<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class PasswordController extends Controller
{
    public function edit()
    {
        return view('auth.password');
    }

    public function update(Request $request)
    {
        $request->validate([
            'current_password' => ['required', 'current_password'],
            'password' => ['required', 'string', 'min:8', 'confirmed',
                           'different:current_password'],
        ]);

        // di-hash otomatis oleh cast "hashed" pada model User
        $request->user()->update(['password' => $request->password]);

        return back()->with('success', 'Kata sandi berhasil diubah.');
    }
}