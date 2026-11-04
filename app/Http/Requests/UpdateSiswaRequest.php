<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Form Request untuk validasi data saat memperbarui data siswa.
 *
 * Mengatur aturan validasi untuk setiap field yang dikirim dari form edit siswa.
 * Validasi mencakup: keberadaan, format, keunikan (dengan pengecualian record sendiri),
 * dan konsistensi dengan pilihan yang tersedia di UI (dropdown).
 *
 * @author  SIMPONI Team
 * @date    5 April 2025
 * @updated 23 Oktober 2025 — Tambah validasi field `status`
 */
class UpdateSiswaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $id = $this->route('siswa');

        return [
            'wali_id' => [
                'nullable',
                'exists:users,id',
            ],
            'nama' => [
                'required',
                'string',
                'max:255',
            ],
            'nisn' => [
                'required',
                'digits:10',
                Rule::unique('siswas', 'nisn')->ignore($id),
            ],
            'kelas' => [
                'required',
                Rule::in(['VII', 'VIII', 'IX']),
            ],
            'angkatan' => [
                'required',
                'integer',
                'digits:4',
                'between:2020,' . (date('Y') + 2),
            ],
            'status' => [ // 🔹 TAMBAHAN BARU
                'required',
                Rule::in(['aktif', 'lulus', 'tidak_aktif']),
            ],
            'foto' => [
                'nullable',
                'image',
                'mimes:jpg,jpeg,png',
                'max:5000',
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'kelas.in'         => 'Kelas hanya boleh diisi dengan: VII, VIII, atau IX.',
            'nisn.unique'      => 'NISN ini sudah digunakan oleh siswa lain.',
            'angkatan.between' => 'Tahun angkatan harus antara 2020 dan ' . (date('Y') + 2) . '.',
            'foto.max'         => 'Ukuran foto maksimal 5MB.',
            'foto.mimes'       => 'Format foto hanya mendukung: JPG, JPEG, PNG.',
            'status.required'  => 'Status siswa wajib dipilih.',
            'status.in'        => 'Status siswa tidak valid. Pilih: Aktif, Lulus, atau Tidak Aktif.',
        ];
    }
}
