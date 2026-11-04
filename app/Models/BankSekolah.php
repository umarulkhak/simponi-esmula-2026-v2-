<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BankSekolah extends Model
{
    use HasFactory;

    // ✅ Laravel sebenarnya sudah otomatis pakai 'bank_sekolahs'
    // Tapi lebih aman tulis eksplisit biar jelas
    protected $table = 'bank_sekolahs';

    // ✅ Primary key default = 'id', tidak wajib, tapi lebih jelas
    protected $primaryKey = 'id';

    // ✅ Mass assignment boleh semua kolom
    protected $guarded = [];
}
