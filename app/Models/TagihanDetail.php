<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class TagihanDetail extends Model
{
    use HasFactory;

    protected $fillable = [
        'tagihan_id',
        'nama_biaya',
        'jumlah_biaya',
    ];
    protected $table = 'tagihan_details';

    // Relasi ke Tagihan
    public function tagihan()
    {
        return $this->belongsTo(Tagihan::class);
    }
}
