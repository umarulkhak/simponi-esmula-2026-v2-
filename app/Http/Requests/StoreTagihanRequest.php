<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreTagihanRequest extends FormRequest
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
            'biaya_id'            => 'required|array',
            'biaya_id.*'          => 'exists:biayas,id',
            'angkatan'            => 'nullable|numeric',
            'kelas'               => 'nullable|string|in:VII,VIII,IX',
            'tanggal_tagihan'     => 'required|date',
            'tanggal_jatuh_tempo' => 'required|date|after_or_equal:tanggal_tagihan',
            'keterangan'          => 'nullable|string|max:255',
        ];
    }
    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $data = $validator->getData();

            $query = \App\Models\Siswa::where('status', 'aktif');
            if (!empty($data['kelas'])) {
                $query->where('kelas', $data['kelas']);
            }
            if (!empty($data['angkatan'])) {
                $query->where('angkatan', $data['angkatan']);
            }

            if ($query->count() === 0) {
                $validator->errors()->add('kelas', 'Tidak ada siswa aktif yang sesuai dengan filter yang dipilih.');
            }
        });
    }

}
