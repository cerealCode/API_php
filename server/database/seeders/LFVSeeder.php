<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\MascotaLFV;

class LFVSeeder extends Seeder
{
    /**
     * Poblar la DB con los seeders.
     */
    public function run(): void
    {
        if (User::where('email', 'LFV1@email.LFV')->count() === 0) {
            $u = User::create([
                'name' => 'LFV1',
                'email' => 'LFV1@email.LFV',
                'password' => bcrypt('LFV1')
            ]);
            $u->email_verified_at = now();
            $u->save();
            MascotaLFV::create([
                'nombre' => 'Mascota 1 LFV 1',
                'descripcion' => 'Ejemplo Descripción',
                'publica' => 'Si',
                'tipo' => 'Perro',
                'user_id' => $u->id
            ]);
            MascotaLFV::create([
                'nombre' => 'Mascota 2 LFV 1',
                'descripcion' => 'Ejemplo Descripción',
                'publica' => 'No',
                'tipo' => 'Gato',
                'user_id' => $u->id
            ]);
        }
        if (User::where('email', 'LFV2@email.LFV')->count() === 0) {
            $u = User::create([
                'name' => 'LFV2',
                'email' => 'LFV2@email.LFV',
                'password' => bcrypt('LFV2')
            ]);
            $u->email_verified_at = now();
            $u->save();
            MascotaLFV::create([
                'nombre' => 'Mascota 1 LFV 2',
                'descripcion' => 'Ejemplo Descripción',
                'publica' => 'Si',
                'tipo' => 'Dragón',
                'user_id' => $u->id
            ]);
            MascotaLFV::create([
                'nombre' => 'Mascota 2 LFV 2',
                'descripcion' => 'Ejemplo Descripción',
                'publica' => 'No',
                'tipo' => 'Pájaro',
                'user_id' => $u->id
            ]);
        }
    }
}
