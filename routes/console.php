<?php

use Illuminate\Support\Facades\Schedule;
use App\Models\Appointment;
use App\Models\Notification;
use Carbon\Carbon;

Schedule::call(function () {
    $fallbackTimezone = config('app.timezone', 'Asia/Ashgabat');
    Appointment::with(['doctor','clinic'])
        ->whereIn('status', ['pending', 'confirmed'])
        ->whereNull('reminder_sent_at')
        ->whereDate('appointment_date','>=',Carbon::today($fallbackTimezone)->subDays(2)->toDateString())
        ->whereDate('appointment_date','<=',Carbon::today($fallbackTimezone)->addDays(2)->toDateString())
        ->chunkById(200, function ($appointments) use ($fallbackTimezone) {
            foreach ($appointments as $appointment) {
                $timezone = $appointment->clinic?->timezone ?: $fallbackTimezone;
                $now = Carbon::now($timezone);
                $start = Carbon::parse($appointment->appointment_date->toDateString().' '.$appointment->start_time, $timezone);
                $minutesUntil = $now->diffInMinutes($start, false);
                if ($minutesUntil < 30 || $minutesUntil > 60) continue;

                Notification::firstOrCreate([
                    'user_id' => $appointment->patient_id,
                    'type' => 'appointment_reminder',
                    'data->appointment_id' => $appointment->id,
                ], ['title' => 'Kabul wagty ýakynlaşýar', 'message' => 'Kabul edişiňize 30–60 minut galdy.', 'data' => ['appointment_id' => $appointment->id]]);
                if ($appointment->doctor) Notification::firstOrCreate([
                    'user_id' => $appointment->doctor->user_id,
                    'type' => 'appointment_reminder',
                    'data->appointment_id' => $appointment->id,
                ], ['title' => 'Ýakynlaşýan kabul', 'message' => 'Ýakyn wagtda kabul ýazgyňyz bar.', 'data' => ['appointment_id' => $appointment->id]]);
                $appointment->update(['reminder_sent_at' => now()]);
            }
        });
})->name('shypatap-scheduled-task')->everyMinute()->withoutOverlapping();

Schedule::command('shypatap:purge-trash')->dailyAt('02:30')->withoutOverlapping();
