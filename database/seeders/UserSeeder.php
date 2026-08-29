<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Admin User
        User::firstOrCreate(
            ['email' => 'fawwazfara11@assajjad.com'],
            [
                'name' => 'Fawwaz Admin',
                'password' => Hash::make('fwaz000165'),
                'role' => 'admin',
            ]
        );

        // Guru User
        User::firstOrCreate(
            ['email' => 'erawati@assajjad.com'],
            [
                'name' => 'Ibu Erawati',
                'password' => Hash::make('era123'),
                'role' => 'guru',
            ]
        );
    }
}
