<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $accounts = [
            [
                'name' => 'Atmint Laboratorium',
                'nim' => null,
                'email' => 'admin@dasar.com',
                'role' => 'admin',
            ],
            [
                'name' => 'Mahasiswa Contoh',
                'nim' => '2026990001',
                'email' => 'mahasiswa@dasar.com',
                'role' => 'mahasiswa',
            ],
        ];

        foreach ($accounts as $account) {
            if (User::where('email', $account['email'])->exists()) {
                $this->command->warn(
                    "Dilewati, email sudah ada: {$account['email']}"
                );

                continue;
            }

            if (
                $account['nim'] !== null
                && User::where('nim', $account['nim'])->exists()
            ) {
                $this->command->warn(
                    "Dilewati, NIM sudah ada: {$account['nim']}"
                );

                continue;
            }

            $user = new User();
            $user->name = $account['name'];
            $user->nim = $account['nim'];
            $user->email = $account['email'];
            $user->role = $account['role'];
            $user->password = Hash::make('anakGunung');
            $user->save();

            $this->command->info(
                "Akun {$account['role']} dibuat: {$account['email']}"
            );
        }
    }
    // public function run(): void
    // {
    //     User::factory()->count(100)->create();
    // }
}
