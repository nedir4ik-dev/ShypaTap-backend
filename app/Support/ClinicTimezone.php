<?php

namespace App\Support;

use App\Models\Clinic;
use App\Models\Setting;

class ClinicTimezone
{
    public static function forClinic(?int $clinicId): string
    {
        $clinicTimezone = $clinicId ? Clinic::whereKey($clinicId)->value('timezone') : null;
        $globalTimezone = Setting::where('key', 'timezone')->value('value');

        return $clinicTimezone ?: ($globalTimezone ?: config('app.timezone', 'Asia/Ashgabat'));
    }
}
