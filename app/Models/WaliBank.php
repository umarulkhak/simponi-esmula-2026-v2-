<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class WaliBank extends Model
{
    protected $appends = ['nama_bank_full'];
    protected $guarded = [];

    protected function namaBankFull(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->nama_bank .
                ' - An. ' . $this->nama_rekening .
                ' (' . $this->nomor_rekening . ')',
        );
    }
}
