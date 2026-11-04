<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class CreateDefaultAdminUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $name = config('app.admin_default_name');
        $email = config('app.admin_default_email');
        $password = config('app.admin_default_password');

        if (!is_string($name) || $name === '' || !is_string($email) || filter_var($email, FILTER_VALIDATE_EMAIL) === false) {
            throw new \RuntimeException('Set a valid ADMIN_DEFAULT_NAME and ADMIN_DEFAULT_EMAIL before seeding the default admin account.');
        }
        if (!is_string($password) || $password === '') {
            throw new \RuntimeException('Set ADMIN_DEFAULT_PASSWORD before seeding the default admin account.');
        }

        if (!User::where('email', $email)->exists()) {
            User::create([
                'name'     => $name,
                'email'    => $email,
                'password' => Hash::make($password),
                'akses'    => 'admin',
            ]);

            $this->command->info('Default admin account created.');
        } else {
            $this->command->warn('The configured default admin account already exists. It was not changed.');
        }
    }
}
