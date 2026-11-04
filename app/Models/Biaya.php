<?php

namespace App\Models;

use App\Traits\HasFormatRupiah;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;

class Biaya extends Model
{
    use HasFactory, HasFormatRupiah;

    protected $guarded = [];
    protected $append = ['nama_biaya_full'];

    protected $casts = [
        'jumlah' => 'integer',
    ];

    protected $attributes = [
        'jumlah' => 0,
    ];

    protected function namaBiayaFull(): Attribute
    {
        return Attribute::make(
            get: fn() => $this->nama . ' - ' . $this->formatRupiah('jumlah'),
        );
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function tagihans()
    {
        return $this->hasMany(Tagihan::class);
    }

    public function scopeSearch($query, $keyword)
    {
        return $query->where('nama', 'like', "%{$keyword}%");
    }

    protected static function booted(): void
    {
        static::creating(fn ($biaya) => $biaya->user_id = auth()->id());
        static::updating(fn ($biaya) => $biaya->user_id = auth()->id());
    }
}
