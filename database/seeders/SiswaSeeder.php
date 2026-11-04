<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Siswa;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class SiswaSeeder extends Seeder
{
    public function run()
    {
        $adminName = config('app.admin_default_name');
        $adminEmail = config('app.admin_default_email');
        $adminPassword = config('app.admin_default_password');
        $demoPassword = config('app.demo_wali_password');
        if (!is_string($adminName) || $adminName === '' || !is_string($adminEmail) || filter_var($adminEmail, FILTER_VALIDATE_EMAIL) === false) {
            throw new \RuntimeException('Set a valid ADMIN_DEFAULT_NAME and ADMIN_DEFAULT_EMAIL before seeding students.');
        }
        if (!is_string($adminPassword) || $adminPassword === '' || !is_string($demoPassword) || $demoPassword === '') {
            throw new \RuntimeException('Set ADMIN_DEFAULT_PASSWORD and DEMO_WALI_PASSWORD before seeding students.');
        }

        $operator = User::firstOrCreate(
            ['email' => $adminEmail],
            [
                'name'     => $adminName,
                'email'    => $adminEmail,
                'password' => Hash::make($adminPassword),
                'akses'    => 'admin',
            ]
        );

        // 🔹 Pastikan ada beberapa wali murid
        $walis = [];
        for ($i = 1; $i <= 50; $i++) {
            $walis[] = User::firstOrCreate(
                ['email' => "wali{$i}@sekolah.local"],
                [
                    'name'     => "Wali Murid {$i}",
                    'email'    => "wali{$i}@sekolah.local",
                    'password'    => Hash::make($demoPassword),
                    'akses'    => 'wali',
                ]
            )->id;
        }

        // 🔹 Buat data dummy yang realistis
        $siswaData = [];

        $tahunSekarang = date('Y');
        $angkatanList = [$tahunSekarang - 2, $tahunSekarang - 1, $tahunSekarang]; // Misal: 2023, 2024, 2025

        for ($i = 1; $i <= 300; $i++) {
            // Distribusi kelas: 40% VII, 40% VIII, 20% IX
            $rand = \mt_rand(1, 100);
            if ($rand <= 40) {
                $kelas = 'VII';
                $angkatan = $angkatanList[2]; // Angkatan terbaru
            } elseif ($rand <= 80) {
                $kelas = 'VIII';
                $angkatan = $angkatanList[1];
            } else {
                $kelas = 'IX';
                $angkatan = $angkatanList[0];
            }

            // Status: kebanyakan aktif, sebagian lulus/tidak aktif
            $status = 'aktif';
            if ($kelas === 'IX' && \mt_rand(1, 10) <= 3) { // 30% kelas IX sudah lulus
                $status = 'lulus';
            } elseif (\mt_rand(1, 100) <= 5) { // 5% siswa tidak aktif (DO)
                $status = 'tidak_aktif';
            }

            $siswaData[] = [
                'wali_id'       => $walis[\array_rand($walis)] ?? null,
                'wali_status'   => \mt_rand(0, 1) ? 'ok' : null,
                'nama'          => $this->generateNamaSiswa(),
                'nisn'          => $this->generateUniqueNisn($i),
                'kelas'         => $kelas,
                'angkatan'      => $angkatan,
                'status'        => $status,
                'tahun_lulus'   => $status === 'lulus' ? $tahunSekarang - 1 : null,
                'user_id'       => $operator->id,
                'created_at'    => now()->subDays(\mt_rand(0, 365)),
                'updated_at'    => now(),
            ];
        }

        // 🔹 Insert dalam batch untuk performa
        Siswa::insert($siswaData);

        $this->command->info('✅ 300 data siswa dummy realistis berhasil ditambahkan!');
        $this->command->info('   - Kelas: VII, VIII, IX');
        $this->command->info('   - Status: Aktif, Lulus, Tidak Aktif');
        $this->command->info('   - Termasuk data wali murid & angkatan.');
    }

    /**
     * Generate nama siswa acak (Indonesia)
     */
    private function generateNamaSiswa(): string
    {
        $namaDepan = [
            'Ahmad', 'Budi', 'Citra', 'Dewi', 'Eka', 'Fajar', 'Galih', 'Hana',
            'Indra', 'Joko', 'Kiki', 'Lina', 'Mira', 'Nanda', 'Oki', 'Putri',
            'Rudi', 'Sari', 'Tono', 'Umi', 'Vina', 'Wawan', 'Yuni', 'Zahra'
        ];
        $namaBelakang = [
            'Wijaya', 'Santoso', 'Pratama', 'Putra', 'Putri', 'Hidayat', 'Rahman',
            'Sari', 'Kusuma', 'Nugroho', 'Firmansyah', 'Handayani', 'Saputra', 'Lestari'
        ];

        return $namaDepan[\array_rand($namaDepan)] . ' ' . $namaBelakang[\array_rand($namaBelakang)];
    }

    /**
     * Generate NISN unik (10 digit)
     */
    private function generateUniqueNisn(int $index): string
    {
        return str_pad((string) (1000000000 + $index), 10, '0', STR_PAD_LEFT);
    }
}
