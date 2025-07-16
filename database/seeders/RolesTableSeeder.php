<?php

namespace Database\Seeders;

use App\Models\Role;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class RolesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Role::firstOrCreate(
            ['name' => 'admin'],
        );
        Role::firstOrCreate(
            ['name' => 'employee'],
        );
        Role::firstOrCreate(
            ['name' => 'client'],
        );
        Role::firstOrCreate(
            ['name' => 'accountant'],
        );// إضافة دور المحاسب
    }
}
