<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\Tagihan;

class Pembayaran extends Model
{
    use HasFactory;

    protected $fillable = [
        'tagihan_id',
        'wali_id',
        'wali_bank_id',
        'nama_rekening_pengirim',
        'nomor_rekening_pengirim',
        'nama_bank_pengirim',
        'kode_bank_pengirim',
        'bank_sekolah_id',
        'tanggal_bayar',
        'tanggal_konfirmasi',
        'status_konfirmasi',
        'jumlah_dibayar',
        'bukti_bayar',
        'metode_pembayaran',
        'user_id',
        'group_id',
    ];

    protected $dates = [
        'tanggal_bayar',
        'tanggal_konfirmasi',
    ];

    /**
     * Relasi ke Tagihan.
     */
    public function tagihan(): BelongsTo
    {
        return $this->belongsTo(Tagihan::class);
    }

    /**
     * Relasi ke User yang membuat pembayaran.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the Wali that owns the Pembayaran
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function wali(): BelongsTo
    {
        return $this->belongsTo(User::class, 'wali_id');
    }

    /**
     * Accessor: Format jumlah dibayar ke Rupiah.
     */
    public function getJumlahDibayarFormattedAttribute(): string
    {
        return 'Rp ' . number_format($this->jumlah_dibayar, 0, ',', '.');
    }

    /**
     * Booted events — auto set user_id saat create/update.
     */
    protected static function booted(): void
    {
        static::creating(function (self $pembayaran) {
            if (auth()->check()) {
                $pembayaran->user_id = auth()->id();
            }
        });

        static::updating(function (self $pembayaran) {
            if (auth()->check()) {
                $pembayaran->user_id = auth()->id();
            }
        });
    }

    // Opsional: accessor untuk tampilan
    public function getRekeningPengirimAttribute(): string
    {
        return ($this->nama_bank_pengirim ?? 'Bank')
            . ' a/n ' . ($this->nama_rekening_pengirim ?? 'Nama')
            . ' (' . ($this->nomor_rekening_pengirim ?? 'Nomor') . ')';
    }

    public function bankSekolah(): BelongsTo
    {
        return $this->belongsTo(BankSekolah::class);
    }
    public function waliBank(): BelongsTo
    {
        return $this->belongsTo(WaliBank::class);
    }

}
