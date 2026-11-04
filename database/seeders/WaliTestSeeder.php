<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class WaliTestSeeder extends Seeder
{
    public function run()
    {
        if (!app()->environment(['local', 'testing'])) {
            throw new \RuntimeException('Test wali accounts are restricted to local/testing environments.');
        }

        $password = config('app.demo_wali_password');
        if (!is_string($password) || $password === '') {
            throw new \RuntimeException('Set DEMO_WALI_PASSWORD before seeding test wali accounts.');
        }
        $passwordHash = Hash::make($password);

        foreach (range(1, 200) as $i) {
            User::create([
                'name' => 'Wali ' . str_pad($i, 3, '0', STR_PAD_LEFT),
                'email' => "wali{$i}@sekolah.sch.id",
                'nohp' => '081234567' . str_pad($i, 3, '0', STR_PAD_LEFT),
                'password' => $passwordHash,
                'akses' => 'wali',
                'email_verified_at' => now(),
            ]);
        }
    }
}
