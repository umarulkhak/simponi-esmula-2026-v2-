<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Siswa;

class SiswaFactory extends Factory
{
    protected $model = Siswa::class;

    public function definition()
    {
        $kelas = $this->faker->randomElement(['VII', 'VIII', 'IX']);
        // Hitung angkatan: misal tahun sekarang 2025 → VII = 2025, VIII = 2024, IX = 2023
        $angkatan = date('Y') - ((int)$kelas[0] - 7);

        return [
            'nama'         => $this->faker->name(),
            'nisn'         => $this->faker->unique()->numerify('##########'), // 10 digit unik
            'kelas'        => $kelas,
            'angkatan'     => $angkatan,
            'foto'         => null,
            'wali_id'      => null, // 👈 wali belum ditentukan
            'wali_status'  => 'belum_ditentukan',
            'user_id'      => 1, // diasumsikan user ID 1 adalah admin/petugas
        ];
    }
}
