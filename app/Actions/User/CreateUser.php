<?php

namespace App\Actions\User;

use App\Models\User;
use App\Notifications\User\Welcome;
use Filament\Events\Auth\Registered;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class CreateUser
{
    public function create(array $data): User
    {
        if (empty($data['password'])) {
            $data['password'] = Str::random(10);
        }

        $u = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'role_id' => $data['role_id'],
        ]);

        event(new Registered($u));
        $u->notify(new Welcome($u, $data['password']));

        return $u;
    }
}
