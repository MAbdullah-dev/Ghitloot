<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Role;

class RoleSeeder extends Seeder
{
    public function run()
    {
        Role::create([
            'id' => 1,
            'name' => 'user',
        ]);

        Role::create([
            'id' => 2,
            'name' => 'admin',
        ]);
    }
}
