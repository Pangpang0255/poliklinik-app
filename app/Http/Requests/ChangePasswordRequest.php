<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class ChangePasswordRequest extends FormRequest
{
    public function authorize(): bool
    {
        return Auth::check();
    }

    public function rules(): array
    {
        return [
            'password_lama'    => ['required', 'string'],
            'password_baru'    => ['required', 'string', 'min:8', 'confirmed', 'different:password_lama'],
            'password_baru_confirmation' => ['required', 'string'],
        ];
    }

    public function messages(): array
    {
        return [
            'password_lama.required'    => 'Password lama wajib diisi.',
            'password_baru.required'    => 'Password baru wajib diisi.',
            'password_baru.min'         => 'Password baru minimal 8 karakter.',
            'password_baru.confirmed'   => 'Konfirmasi password baru tidak cocok.',
            'password_baru.different'   => 'Password baru tidak boleh sama dengan password lama.',
            'password_baru_confirmation.required' => 'Konfirmasi password baru wajib diisi.',
        ];
    }

    /**
     * Tambahkan validasi custom: password lama harus cocok
     */
    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            if (!Hash::check($this->password_lama, Auth::user()->password)) {
                $validator->errors()->add('password_lama', 'Password lama yang Anda masukkan tidak cocok.');
            }
        });
    }
}
