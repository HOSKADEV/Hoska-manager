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
            ['lable' => 'Administrator']
        );
        Role::firstOrCreate(
            ['name' => 'employee'],
            ['lable' => 'Employee']
        );
        Role::firstOrCreate(
            ['name' => 'client'],
            ['lable' => 'Client']
        );
        Role::firstOrCreate(
            ['name' => 'accountant'],
            ['lable' => 'Accountant']
        );// إضافة دور المحاسب
    }
}
