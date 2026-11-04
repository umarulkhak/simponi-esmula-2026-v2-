<?php

namespace Database\Seeders;

use App\Models\Siswa;
use App\Models\Tagihan;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DemoWaliSiswaTagihanSeeder extends Seeder
{
    public function run(): void
    {
        if (! app()->environment(['local', 'testing'])) {
            throw new \RuntimeException('Demo wali, siswa, and invoices are restricted to local/testing environments.');
        }

        $adminEmail = config('app.admin_default_email');
        $demoPassword = config('app.demo_wali_password');
        if (!is_string($adminEmail) || filter_var($adminEmail, FILTER_VALIDATE_EMAIL) === false) {
            throw new \RuntimeException('Set a valid ADMIN_DEFAULT_EMAIL before seeding demo school data.');
        }
        if (!is_string($demoPassword) || $demoPassword === '') {
            throw new \RuntimeException('Set DEMO_WALI_PASSWORD before seeding demo wali accounts.');
        }

        $admin = User::where('email', $adminEmail)
            ->where('akses', 'admin')
            ->firstOrFail();

        $period = now()->startOfMonth();
        $invoiceDate = $period->toDateString();
        $dueDate = $period->copy()->day(min(15, $period->daysInMonth))->toDateString();
        $invoiceDescription = 'SPP ' . $period->format('m/Y');

        $demoRecords = [
            [
                'wali' => ['name' => 'Budi Santoso', 'email' => 'wali1@demo.test', 'nohp' => '081200000001'],
                'siswa' => ['nama' => 'Ahmad Santoso', 'nisn' => '9900000001', 'kelas' => 'VII'],
                'amount' => 350000,
            ],
            [
                'wali' => ['name' => 'Siti Rahmawati', 'email' => 'wali2@demo.test', 'nohp' => '081200000002'],
                'siswa' => ['nama' => 'Zahra Rahmawati', 'nisn' => '9900000002', 'kelas' => 'VIII'],
                'amount' => 375000,
            ],
            [
                'wali' => ['name' => 'Dedi Pratama', 'email' => 'wali3@demo.test', 'nohp' => '081200000003'],
                'siswa' => ['nama' => 'Fajar Pratama', 'nisn' => '9900000003', 'kelas' => 'IX'],
                'amount' => 400000,
            ],
        ];

        foreach ($demoRecords as $index => $record) {
            $wali = User::updateOrCreate(
                ['email' => $record['wali']['email']],
                [
                    'name' => $record['wali']['name'],
                    'password' => Hash::make($demoPassword),
                    'akses' => 'wali',
                    'nohp' => $record['wali']['nohp'],
                ]
            );

            $siswa = Siswa::updateOrCreate(
                ['nisn' => $record['siswa']['nisn']],
                [
                    'wali_id' => $wali->id,
                    'wali_status' => 'ok',
                    'nama' => $record['siswa']['nama'],
                    'kelas' => $record['siswa']['kelas'],
                    'angkatan' => (int) now()->format('Y'),
                    'status' => 'aktif',
                    'user_id' => $admin->id,
                ]
            );

            $tagihan = Tagihan::firstOrNew([
                'siswa_id' => $siswa->id,
                'tanggal_tagihan' => $invoiceDate,
                'keterangan' => $invoiceDescription,
            ]);
            $tagihan->user_id = $admin->id;
            $tagihan->angkatan = $siswa->angkatan;
            $tagihan->kelas = $siswa->kelas;
            $tagihan->tanggal_jatuh_tempo = $dueDate;
            $tagihan->status ??= 'baru';
            $tagihan->save();

            $tagihan->tagihanDetails()->firstOrCreate(
                ['nama_biaya' => $invoiceDescription],
                ['jumlah_biaya' => $record['amount']]
            );

            $this->command->info("Wali: {$wali->email} | Siswa: {$siswa->nama} | Tagihan: Rp " . number_format($record['amount'], 0, ',', '.'));
        }

        $this->command->info('Demo wali accounts are ready; use the configured demo password.');
    }
}
