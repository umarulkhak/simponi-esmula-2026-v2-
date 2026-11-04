<?php

namespace Tests\Feature\Support;

use App\Models\Siswa;
use App\Models\Tagihan;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

trait CreatesLinkedSchoolRecords
{
    protected function createLinkedSchoolRecords(): array
    {
        $token = Str::uuid()->toString();
        $admin = User::factory()->create([
            'akses' => 'admin',
            'password' => Hash::make('qa-password'),
        ]);
        $wali = User::factory()->create([
            'name' => 'QA Wali ' . substr($token, 0, 8),
            'email' => 'qa-wali-' . $token . '@example.test',
            'akses' => 'wali',
            'password' => Hash::make('qa-password'),
        ]);
        $student = Siswa::factory()->create([
            'wali_id' => $wali->id,
            'wali_status' => 'ok',
            'nama' => 'QA Siswa ' . substr($token, 0, 8),
            'nisn' => '9' . random_int(100000000, 999999999),
            'kelas' => 'VII',
            'angkatan' => (int) now()->format('Y'),
            'jurusan' => 'SMP',
            'status' => 'aktif',
            'user_id' => $admin->id,
        ]);
        $invoice = Tagihan::create([
            'siswa_id' => $student->id,
            'user_id' => $admin->id,
            'angkatan' => $student->angkatan,
            'kelas' => $student->kelas,
            'tanggal_tagihan' => now()->startOfMonth()->toDateString(),
            'tanggal_jatuh_tempo' => now()->startOfMonth()->addDays(14)->toDateString(),
            'keterangan' => 'QA SPP ' . $token,
            'status' => 'baru',
        ]);
        $invoice->tagihanDetails()->create([
            'nama_biaya' => 'QA SPP',
            'jumlah_biaya' => 275000,
        ]);

        return compact('admin', 'wali', 'student', 'invoice');
    }
}
