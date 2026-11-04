<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Nicolaslopezj\Searchable\SearchableTrait;
use App\Models\User;

class Siswa extends Model
{
    use HasFactory, SearchableTrait;

    protected $searchable = [
        'columns' => [
            'nama' => 10,
            'nisn' => 10,
        ],
    ];

    protected $fillable = [
        'wali_id',
        'wali_status',
        'nama',
        'nisn',
        'kelas',
        'angkatan',
        'foto',
        'user_id',
        'status',
        'tahun_lulus',
    ];

    /**
     * Relasi ke user yang membuat data siswa.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Relasi ke user yang menjadi wali murid.
     */
    public function wali(): BelongsTo
    {
        return $this->belongsTo(User::class, 'wali_id')
            ->withDefault(['name' => 'Belum ada wali murid']);
    }

    /**
     * Relasi ke tagihan: 1 siswa punya banyak tagihan.
     */
    public function tagihan(): HasMany
    {
        return $this->hasMany(Tagihan::class, 'siswa_id');
    }

    // 🔹 RELASI BARU: satu tagihan per siswa pada bulan & tahun tertentu
    public function tagihanPadaBulan()
    {
        return $this->hasOne(Tagihan::class, 'siswa_id');
    }

    // 🔹 SCOPE BARU: eager load tagihan berdasarkan bulan & tahun
    public function scopeDenganTagihanBulan($query, string $bulan, string $tahun)
    {
        return $query->with([
            'tagihanPadaBulan' => function ($q) use ($bulan, $tahun) {
                $q->whereMonth('tanggal_tagihan', $bulan)
                  ->whereYear('tanggal_tagihan', $tahun);
            }
        ]);
    }
    /**
     * Scope untuk mengambil hanya siswa aktif.
     */
    public function scopeAktif($query)
    {
        return $query->where('status', 'aktif');
    }

    /**
     * Scope untuk mengambil hanya siswa yang sudah lulus.
     */
    public function scopeLulus($query)
    {
        return $query->where('status', 'lulus');
    }

    /**
     * Menampilkan label status yang lebih manusiawi.
     */
    public function getStatusLabelAttribute(): string
    {
        return $this->status === 'lulus' ? 'LULUS' : 'Aktif';
    }
}
