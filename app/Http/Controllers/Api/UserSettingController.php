<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\UserSetting;
use Illuminate\Http\Request;

class UserSettingController extends Controller
{
    private const DEFAULTS = [
        'theme' => 'system',
        'timezone' => 'Asia/Ashgabat',
        'notifications_enabled' => '1',
        'appointment_reminders' => '1',
        'email_notifications' => '0',
    ];

    public function show(Request $request)
    {
        $saved = $request->user()->settings()->pluck('value', 'key')->all();
        return response()->json(['data' => array_merge(self::DEFAULTS, $saved)]);
    }

    public function update(Request $request)
    {
        $data = $request->validate([
            'theme' => ['sometimes', 'in:light,dark,system'],
            'timezone' => ['sometimes', 'timezone'],
            'notifications_enabled' => ['sometimes', 'boolean'],
            'appointment_reminders' => ['sometimes', 'boolean'],
            'email_notifications' => ['sometimes', 'boolean'],
        ]);

        foreach ($data as $key => $value) {
            UserSetting::updateOrCreate(
                ['user_id' => $request->user()->id, 'key' => $key],
                ['value' => is_bool($value) ? ($value ? '1' : '0') : (string) $value]
            );
        }

        return $this->show($request);
    }
}
