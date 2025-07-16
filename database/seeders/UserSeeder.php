<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Role;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // إنشاء أدمن إذا لم يكن موجود
        User::firstOrCreate([
            'email' => 'admin@gmail.com',
        ], [
            'name' => 'Admin',
            'password' => bcrypt('123456789'),
            'type' => 'admin',
        ]);

        // التأكد من وجود دور Accountant
        $roleAccountant = Role::firstOrCreate([
            'name' => 'accountant',
        ]);

        // إنشاء محاسب وربطه بالدور
        User::firstOrCreate([
            'email' => 'accountant@gmail.com',
        ], [
            'name' => 'Accountant',
            'password' => Hash::make('password123'),
            'type' => 'employee',
            'role_id' => $roleAccountant->id,
        ]);
    }
}
