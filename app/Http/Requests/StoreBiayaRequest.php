<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Request untuk validasi penyimpanan data Biaya baru.
 */
class StoreBiayaRequest extends FormRequest
{
    /**
     * Menentukan apakah user diizinkan melakukan request ini.
     */
    public function authorize(): bool
    {
        // Untuk saat ini semua user diizinkan.
        // Ubah sesuai kebutuhan (misalnya cek role/permission).
        return true;
    }

    /**
     * Aturan validasi untuk menyimpan data Biaya.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'nama'   => ['required', 'unique:biayas,nama'],
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
