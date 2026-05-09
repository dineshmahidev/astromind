<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SettingController extends Controller
{
    /**
     * Get a setting value by key.
     */
    public function getSetting(Request $request)
    {
        $key = $request->key;
        $setting = DB::table('settings')->where('key', $key)->first();
        
        return response()->json([
            'success' => true,
            'value' => $setting ? $setting->value : null
        ]);
    }

    /**
     * Update or create a setting.
     */
    public function saveSetting(Request $request)
    {
        $request->validate([
            'key' => 'required',
            'value' => 'required'
        ]);

        DB::table('settings')->updateOrInsert(
            ['key' => $request->key],
            ['value' => $request->value, 'updated_at' => now(), 'created_at' => now()]
        );

        return response()->json(['success' => true, 'message' => 'Setting saved successfully']);
    }

    /**
     * Get all public settings (e.g. support email, contact).
     */
    public function getPublicSettings()
    {
        $settings = DB::table('settings')->where('group', 'public')->get();
        return response()->json(['success' => true, 'settings' => $settings]);
    /**
     * Get ZegoCloud configuration from environment.
     */
    public function getZegoConfig()
    {
        return response()->json([
            'success' => true,
            'app_id' => env('ZEGO_APP_ID'),
            'app_sign' => env('ZEGO_APP_SIGN')
        ]);
    }
}
