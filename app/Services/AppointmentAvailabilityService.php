<?php

namespace App\Services;

use App\Models\Appointment;
use App\Models\DoctorDayOff;
use App\Models\DoctorSchedule;
use Carbon\Carbon;
use Illuminate\Validation\ValidationException;

class AppointmentAvailabilityService
{
    public function slots(int $doctorProfileId, int $clinicId, string $date): array
    {
        $day = Carbon::parse($date, 'Asia/Ashgabat');
        if ($day->isPast() && !$day->isToday()) return [];

        $schedules = DoctorSchedule::query()
            ->where('doctor_profile_id', $doctorProfileId)
            ->where('clinic_id', $clinicId)
            ->where('day_of_week', $day->dayOfWeekIso)
            ->where('is_active', true)->get();

        if (!$schedules->count() || DoctorDayOff::where('doctor_profile_id', $doctorProfileId)
            ->where('clinic_id', $clinicId)->whereDate('off_date', $day->toDateString())->exists()) return [];

        $busy = Appointment::where('doctor_profile_id', $doctorProfileId)
            ->where('clinic_id', $clinicId)->whereDate('appointment_date', $day->toDateString())
            ->whereIn('status', ['pending', 'confirmed'])->get(['start_time', 'end_time']);

        $result = [];
        foreach ($schedules as $schedule) {
            $cursor = Carbon::parse($day->toDateString().' '.$schedule->start_time, 'Asia/Ashgabat');
            $end = Carbon::parse($day->toDateString().' '.$schedule->end_time, 'Asia/Ashgabat');
            $breakStart = $schedule->break_start ? Carbon::parse($day->toDateString().' '.$schedule->break_start, 'Asia/Ashgabat') : null;
            $breakEnd = $schedule->break_end ? Carbon::parse($day->toDateString().' '.$schedule->break_end, 'Asia/Ashgabat') : null;
            $duration = max(5, (int) $schedule->slot_duration_minutes);

            while ($cursor->copy()->addMinutes($duration)->lte($end)) {
                $slotEnd = $cursor->copy()->addMinutes($duration);
                $past = $day->isToday() && $cursor->lte(Carbon::now('Asia/Ashgabat'));
                $duringBreak = $breakStart && $breakEnd && $cursor->lt($breakEnd) && $slotEnd->gt($breakStart);
                $overlap = $busy->contains(fn ($booking) =>
                    $booking->start_time < $slotEnd->format('H:i:s') && $booking->end_time > $cursor->format('H:i:s')
                );
                if (!$past && !$duringBreak && !$overlap) {
                    $result[] = ['start_time' => $cursor->format('H:i'), 'end_time' => $slotEnd->format('H:i'), 'duration_minutes' => $duration];
                }
                $cursor = $slotEnd;
            }
        }
        return $result;
    }

    public function assertAvailable(int $doctorProfileId, int $clinicId, string $date, string $start, string $end): void
    {
        $day = Carbon::parse($date, 'Asia/Ashgabat');
        if ($day->lt(Carbon::today('Asia/Ashgabat'))) {
            throw ValidationException::withMessages(['appointment_date' => ['Geçen senä bron edip bolmaýar.']]);
        }
        $schedule = DoctorSchedule::where('doctor_profile_id', $doctorProfileId)
            ->where('clinic_id', $clinicId)->where('day_of_week', $day->dayOfWeekIso)
            ->where('is_active', true)->where('start_time', '<=', $start)->where('end_time', '>=', $end)->first();
        if (!$schedule) throw ValidationException::withMessages(['start_time' => ['Lukman bu wagtda kabul etmeýär.']]);
        $scheduleStart = Carbon::parse($day->toDateString().' '.$schedule->start_time, 'Asia/Ashgabat');
        $requestedStart = Carbon::parse($day->toDateString().' '.$start, 'Asia/Ashgabat');
        if ($requestedStart->diffInMinutes($scheduleStart) % max(5, (int) $schedule->slot_duration_minutes) !== 0) {
            throw ValidationException::withMessages(['start_time' => ['Wagt lukmanyň slot tertibine gabat gelenok.']]);
        }

        if ($schedule->break_start && $schedule->break_end && $start < $schedule->break_end && $end > $schedule->break_start) {
            throw ValidationException::withMessages(['start_time' => ['Bu wagt arakesmä gabat gelýär.']]);
        }
        if (DoctorDayOff::where('doctor_profile_id', $doctorProfileId)->where('clinic_id', $clinicId)->whereDate('off_date', $day->toDateString())->exists()) {
            throw ValidationException::withMessages(['appointment_date' => ['Lukman bu gün dynç alýar.']]);
        }
        if ($day->isToday() && Carbon::parse($day->toDateString().' '.$start, 'Asia/Ashgabat')->lte(Carbon::now('Asia/Ashgabat'))) {
            throw ValidationException::withMessages(['start_time' => ['Geçen wagta bron edip bolmaýar.']]);
        }
        $overlap = Appointment::where('doctor_profile_id', $doctorProfileId)->where('clinic_id', $clinicId)
            ->whereDate('appointment_date', $day->toDateString())->whereIn('status', ['pending', 'confirmed'])
            ->where('start_time', '<', $end)->where('end_time', '>', $start)->exists();
        if ($overlap) throw ValidationException::withMessages(['start_time' => ['Bu wagt eýýäm bron edilen.']]);
    }
}
