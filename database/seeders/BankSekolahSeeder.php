<?php

namespace Database\Seeders;

use App\Models\Bank;
use App\Models\BankSekolah;
use Illuminate\Database\Seeder;

class BankSekolahSeeder extends Seeder
{
    public function run(): void
    {
        if (! app()->environment(['local', 'testing'])) {
            throw new \RuntimeException('The fictional school bank account is restricted to local/testing environments.');
        }

        $bank = Bank::where('sandi_bank', '002')
            ->where('nama_bank', 'like', '%BRI%')
            ->firstOrFail();

        BankSekolah::updateOrCreate(
            ['nama_rekening' => 'SMP Muhammadiyah Larangan (DEMO)'],
            [
                'kode' => $bank->sandi_bank,
                'nama_bank' => $bank->nama_bank,
                'nomor_rekening' => '000000000000000',
            ]
        );

        $this->command?->info('Demo school account created (fictional account number; no real transfer).');
    }
}
