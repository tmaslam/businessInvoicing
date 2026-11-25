<?php

use App\Models\User;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Hash;

/*
 * Legacy passwords were stored in plain text. Users are upgraded automatically
 * on their next login; this command hashes everyone at once (recommended right
 * after deploying, so plain-text passwords no longer sit in the database).
 */
Artisan::command('users:hash-passwords {--dry-run}', function () {
    $count = 0;
    User::query()->whereNotNull('Password')->where('Password', '!=', '')->chunkById(200, function ($users) use (&$count) {
        foreach ($users as $user) {
            if (password_get_info($user->Password)['algo'] !== null) {
                continue;
            }
            $count++;
            if (! $this->option('dry-run')) {
                $user->forceFill(['Password' => Hash::make($user->Password)])->saveQuietly();
            }
        }
    }, 'User_Id');

    $this->info(($this->option('dry-run') ? 'Would hash' : 'Hashed')." $count plain-text password(s).");
})->purpose('Hash all legacy plain-text user passwords');
