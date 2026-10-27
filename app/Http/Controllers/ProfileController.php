<?php

namespace App\Http\Controllers;

use App\Http\Requests\ChangePasswordRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class ProfileController extends Controller
{
    public function showChangePassword()
    {
        return view('profile.change-password');
    }

    public function updatePassword(ChangePasswordRequest $request)
    {
        Auth::user()->update([
            'password' => Hash::make($request->password_baru),
        ]);

        return redirect()
            ->back()
            ->with('success', 'Password berhasil diubah.');
    }
}
