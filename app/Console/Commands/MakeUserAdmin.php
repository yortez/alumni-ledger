<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('alumni:make-admin {email}')]
#[Description('Grant alumni administration access to an existing account')]
class MakeUserAdmin extends Command
{
    public function handle(): int
    {
        $user = User::query()->where('email', $this->argument('email'))->first();

        if ($user !== null) {
            $user->forceFill(['is_admin' => true, 'admin_role' => 'super_admin'])->save();
            $this->info("{$user->email} can now manage the graduate master list.");

            return self::SUCCESS;
        }

        if (! $this->input->isInteractive()) {
            $this->error('Run this command interactively to create the first administrator account.');

            return self::FAILURE;
        }

        $name = trim((string) $this->ask('Administrator name'));
        $username = trim((string) $this->ask('Administrator username'));
        $password = (string) $this->secret('Password (at least 8 characters)');
        $confirmation = (string) $this->secret('Confirm password');

        if ($name === '' || $username === '' || strlen($password) < 8 || $password !== $confirmation) {
            $this->error('Name and username are required, and the passwords must match and contain at least 8 characters.');

            return self::FAILURE;
        }

        if (User::query()->where('username', $username)->exists()) {
            $this->error('That username is already in use.');

            return self::FAILURE;
        }

        $user = User::query()->create([
            'name' => $name,
            'username' => $username,
            'email' => $this->argument('email'),
            'password' => $password,
        ]);

        $user->forceFill(['is_admin' => true, 'admin_role' => 'super_admin'])->save();
        $this->info("Administrator account created for {$user->email}.");

        return self::SUCCESS;
    }
}
