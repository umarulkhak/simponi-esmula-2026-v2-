<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StorePembayaranRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'tanggal_bayar'   => 'required|date',
            'jumlah_dibayar'  => 'required|numeric|min:1',
            'tagihan_id'      => 'required|exists:tagihans,id',
            // 'metode_pembayaran' => 'required|string|in:transfer,cash,qris,lainnya,manual', // ← HAPUS BARIS INI
        ];
    }

    protected function prepareForValidation()
    {
        if ($this->jumlah_dibayar) {
            $this->merge([
                'jumlah_dibayar' => str_replace('.', '', $this->jumlah_dibayar),
            ]);
        }
    }

    public function messages()
    {
        return [
            'tanggal_bayar.required' => 'Tanggal pembayaran wajib diisi.',
            'jumlah_dibayar.required' => 'Jumlah pembayaran wajib diisi.',
            'jumlah_dibayar.min' => 'Jumlah pembayaran minimal Rp1.',
            'tagihan_id.exists' => 'Tagihan tidak ditemukan.',
            // 'metode_pembayaran.in' => 'Metode pembayaran tidak valid.', // ← HAPUS JUGA
        ];
    }
}
