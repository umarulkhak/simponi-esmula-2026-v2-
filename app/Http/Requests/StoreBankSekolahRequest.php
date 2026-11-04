<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreBankSekolahRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules()
    {
        return [
            'bank_id'        => 'required|exists:banks,id',
            'nama_rekening'  => 'required|string|max:255|unique:bank_sekolahs,nama_rekening',
            'nomor_rekening' => 'required|string|max:50',
        ];
    }

    /**
     * Custom error messages (opsional).
     *
     * @return array<string, string>
     */
    public function messages()
    {
        return [
            'bank_id.required'        => 'Bank wajib dipilih.',
            'bank_id.exists'          => 'Bank yang dipilih tidak valid.',
            'nama_rekening.required'  => 'Nama rekening wajib diisi.',
            'nama_rekening.unique'    => 'Nama rekening sudah terdaftar.',
            'nomor_rekening.required' => 'Nomor rekening wajib diisi.',
        ];
    }
}
