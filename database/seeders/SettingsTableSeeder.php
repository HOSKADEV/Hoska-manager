<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Setting;

class SettingsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Default exchange rates
        Setting::set('usd_rate', 140, 'USD to DZD exchange rate');
        Setting::set('eur_rate', 150, 'EUR to DZD exchange rate');
    }
}
