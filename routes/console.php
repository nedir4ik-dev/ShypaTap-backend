<?php

use Illuminate\Support\Facades\Schedule;
use App\Models\Appointment;
use App\Models\Notification;
use Carbon\Carbon;

Schedule::call(function () {
    $now = Carbon::now(config('app.timezone', 'Asia/Ashgabat'));
    $upcoming = Appointment::with('doctor')->whereIn('status', ['pending', 'confirmed'])
        ->whereNull('reminder_sent_at')->whereDate('appointment_date', $now->toDateString())
        ->whereBetween('start_time', [$now->copy()->addMinutes(30)->format('H:i:s'), $now->copy()->addMinutes(60)->format('H:i:s')])
        ->get();
    foreach ($upcoming as $appointment) {
        Notification::firstOrCreate([
            'user_id' => $appointment->patient_id,
            'type' => 'appointment_reminder',
            'data->appointment_id' => $appointment->id
        ], ['title' => 'Kabul wagty ýakynlaşýar', 'message' => 'Kabul edişiňize 30–60 minut galdy.', 'data' => ['appointment_id' => $appointment->id]]);
        if ($appointment->doctor) Notification::firstOrCreate([
            'user_id' => $appointment->doctor->user_id,
            'type' => 'appointment_reminder',
            'data->appointment_id' => $appointment->id
        ], ['title' => 'Ýakynlaşýan kabul', 'message' => 'Ýakyn wagtda kabul ýazgyňyz bar.', 'data' => ['appointment_id' => $appointment->id]]);
        $appointment->update(['reminder_sent_at' => now()]);
    }
})->name('shypatap-scheduled-task')
    ->everyMinute()
    ->withoutOverlapping();
