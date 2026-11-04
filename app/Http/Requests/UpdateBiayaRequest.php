<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Request untuk validasi update data Biaya.
 */
class UpdateBiayaRequest extends FormRequest
{
    /**
     * Menentukan apakah user diizinkan melakukan request ini.
     */
    public function authorize(): bool
    {
        // Semua user diizinkan, bisa disesuaikan dengan role/permission
        return true;
    }

    /**
     * Aturan validasi untuk update Biaya.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'nama' => [
                'required',
                Rule::unique('biayas', 'nama')->ignore($this->biaya),
            ],
            'jumlah' => ['required', 'numeric'],
        ];
    }

    /**
     * Normalisasi input sebelum divalidasi.
     */
    protected function prepareForValidation(): void
    {
        $this->merge([
            'jumlah' => $this->jumlah ? str_replace('.', '', $this->jumlah) : null,
        ]);
    }

    /**
     * Pesan error kustom untuk validasi.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'nama.required'   => 'Nama biaya wajib diisi.',
            'nama.unique'     => 'Nama biaya sudah digunakan.',
            'jumlah.required' => 'Jumlah biaya wajib diisi.',
            'jumlah.numeric'  => 'Jumlah biaya harus berupa angka.',
        ];
    }
}
