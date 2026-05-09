<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SettingsSeeder extends Seeder
{
    public function run(): void
    {
        $settings = [
            [
                'key' => 'grok_api_key',
                'value' => '', // USER: Put your X.AI API Key here
                'group' => 'ai',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'razorpay_key',
                'value' => '',
                'group' => 'payment',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        foreach ($settings as $setting) {
            DB::table('settings')->updateOrInsert(['key' => $setting['key']], $setting);
        }
    }
}
