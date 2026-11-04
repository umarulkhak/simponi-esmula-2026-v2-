<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        // Jalankan seeder untuk membuat user admin default
        $this->call(CreateDefaultAdminUserSeeder::class);
        $this->call(IndoBankSeeder::class);

        if (app()->environment(['local', 'testing'])) {
            $this->call([
                BankSekolahSeeder::class,
                DemoWaliSiswaTagihanSeeder::class,
                WaliBankSeeder::class,
            ]);
        }
    }
}
