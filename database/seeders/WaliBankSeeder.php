<?php

namespace Database\Seeders;

use App\Models\Bank;
use App\Models\User;
use App\Models\WaliBank;
use Illuminate\Database\Seeder;

class WaliBankSeeder extends Seeder
{
    public function run(): void
    {
        if (! app()->environment(['local', 'testing'])) {
            throw new \RuntimeException('Demo sender bank accounts are restricted to local/testing environments.');
        }

        $bank = Bank::where('sandi_bank', '002')
            ->where('nama_bank', 'like', '%BRI%')
            ->firstOrFail();

        foreach ([
            'wali1@demo.test' => '000000000000001',
            'wali2@demo.test' => '000000000000002',
            'wali3@demo.test' => '000000000000003',
        ] as $email => $accountNumber) {
            $wali = User::where('email', $email)
                ->where('akses', 'wali')
                ->firstOrFail();

            WaliBank::updateOrCreate(
                ['wali_id' => $wali->id, 'nomor_rekening' => $accountNumber],
                [
                    'kode' => $bank->sandi_bank,
                    'nama_bank' => $bank->nama_bank,
                    'nama_rekening' => $wali->name . ' (DEMO)',
                ]
            );
        }

        $this->command?->info('Fictional sender bank accounts seeded for demo wali accounts.');
    }
}
