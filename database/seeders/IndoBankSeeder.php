<?php

/*
 * This file is part of the IndoBank package.
 *
 * (c) Andri Desmana <andridesmana.pw | andridesmana29@gmail.com>
 *
 */

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Andes2912\IndoBank\RawDataGetter;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class IndoBankSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @deprecated
     *
     * @return void
     */
    public function run()
    {
        $banks = RawDataGetter::getBanks();

        if (empty($banks)) {
            throw new \RuntimeException('IndoBank library returned an empty bank catalog.');
        }

        foreach ($banks as $bank) {
            if (empty($bank['nama_bank']) || empty($bank['sandi_bank'])) {
                Log::warning('Skipping an invalid bank record returned by IndoBank.', [
                    'record' => $bank,
                ]);
                continue;
            }

            DB::table('banks')->updateOrInsert(
                [
                    'nama_bank' => $bank['nama_bank'],
                    'sandi_bank' => (string) $bank['sandi_bank'],
                ],
                [
                    'nama_bank' => $bank['nama_bank'],
                    'sandi_bank' => (string) $bank['sandi_bank'],
                ]
            );
        }

        $this->command?->info(count($banks) . ' bank records from IndoBank are available.');
    }
}