<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Support\SinActividad;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;

class RehashPasswords extends Command
{
    protected $signature = 'users:rehash-passwords';
    protected $description = 'Rehash user passwords to bcrypt';

    public function handle()
    {
        // Proceso masivo: no es una acción de nadie, no deja Actividad.
        return SinActividad::correr(fn () => $this->procesar());
    }

    private function procesar()
    {
        $users = User::all();
        
        foreach ($users as $user) {
            // Si la contraseña no está hasheada con bcrypt
            if (!str_starts_with($user->password, '$2y$')) {
                $user->password = Hash::make($user->password);
                $user->save();
                $this->info("Updated password for user: {$user->email}");
            }
        }
        
        $this->info('Passwords rehashed successfully!');
    }
}