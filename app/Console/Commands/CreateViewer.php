<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;

class CreateViewer extends Command
{
    protected $signature = 'infak:create-viewer
                            {email? : Email akun viewer}
                            {password? : Password akun viewer (min. 8 karakter)}
                            {--name= : Nama yang ditampilkan}';

    protected $description = 'Membuat akun admin read-only (role viewer): bisa melihat semua data, tidak bisa mengubah apa pun.';

    public function handle(): int
    {
        $email = strtolower(trim((string) ($this->argument('email') ?: $this->ask('Email viewer'))));
        $password = (string) ($this->argument('password') ?: $this->secret('Password viewer (min. 8 karakter)'));
        $name = (string) ($this->option('name') ?: 'Viewer Madrasah');

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->error('Email tidak valid.');

            return self::FAILURE;
        }

        if (strlen($password) < 8) {
            $this->error('Password minimal 8 karakter.');

            return self::FAILURE;
        }

        $existing = User::where('email', $email)->first();

        if ($existing && $existing->role === 'admin') {
            $this->error('Email tersebut milik akun admin. Gunakan email lain agar admin tidak tertimpa.');

            return self::FAILURE;
        }

        $user = User::updateOrCreate(
            ['email' => $email],
            [
                'name' => $name !== '' ? $name : 'Viewer Madrasah',
                'password' => Hash::make($password),
                'role' => 'viewer',
            ]
        );

        $this->info("Akun viewer siap: {$user->email} (role: viewer, hanya bisa melihat).");

        return self::SUCCESS;
    }
}
