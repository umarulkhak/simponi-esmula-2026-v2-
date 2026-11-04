<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateBankSekolahRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // ✅ izinkan update
    }

    public function rules(): array
    {
        return [
            'nama_rekening'  => ['required', 'string', 'max:255'],
            'nomor_rekening' => ['required', 'numeric'],
        ];
    }
}
