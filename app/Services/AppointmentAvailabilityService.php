<?php

namespace App\Services;

use App\Models\Appointment;
use App\Models\Clinic;
use App\Support\ClinicTimezone;
use App\Models\DoctorDayOff;
use App\Models\DoctorSchedule;
use Carbon\Carbon;
use Illuminate\Validation\ValidationException;

class AppointmentAvailabilityService
{
    public function slots(int $doctorProfileId, int $clinicId, string $date): array
    {
        $timezone = ClinicTimezone::forClinic($clinicId);
        $day = Carbon::parse($date, $timezone);
        if ($day->toDateString() < Carbon::today($timezone)->toDateString()) return [];

        $schedules = DoctorSchedule::query()
            ->where('doctor_profile_id', $doctorProfileId)
            ->where('clinic_id', $clinicId)
            ->where('day_of_week', $day->dayOfWeekIso)
            ->where('is_active', true)->orderBy('start_time')->get();

        if ($schedules->isEmpty() || DoctorDayOff::where('doctor_profile_id', $doctorProfileId)
            ->where('clinic_id', $clinicId)->whereDate('off_date', $day->toDateString())->exists()) return [];

        $busy = Appointment::query()->with('clinic:id,timezone')
            ->where('doctor_profile_id', $doctorProfileId)
            ->whereBetween('appointment_date', [$day->copy()->subDay()->toDateString(), $day->copy()->addDay()->toDateString()])
            ->whereIn('status', ['pending', 'confirmed'])->get();

        $result = [];
        foreach ($schedules as $schedule) {
            $cursor = Carbon::parse($day->toDateString().' '.$schedule->start_time, $timezone);
            $end = Carbon::parse($day->toDateString().' '.$schedule->end_time, $timezone);
            $breakStart = $schedule->break_start ? Carbon::parse($day->toDateString().' '.$schedule->break_start, $timezone) : null;
            $breakEnd = $schedule->break_end ? Carbon::parse($day->toDateString().' '.$schedule->break_end, $timezone) : null;
            $duration = max(5, (int) $schedule->slot_duration_minutes);
            while ($cursor->copy()->addMinutes($duration)->lte($end)) {
                $slotEnd = $cursor->copy()->addMinutes($duration);
                $past = $day->isToday() && $cursor->lte(Carbon::now($timezone));
                $duringBreak = $breakStart && $breakEnd && $cursor->lt($breakEnd) && $slotEnd->gt($breakStart);
                $candidateStartUtc = $cursor->copy()->utc();
                $candidateEndUtc = $slotEnd->copy()->utc();
                $overlap = $busy->contains(function ($booking) use ($candidateStartUtc, $candidateEndUtc) {
                    $bookingTimezone = $booking->clinic?->timezone ?: config('app.timezone', 'Asia/Ashgabat');
                    $bookingStart = Carbon::parse($booking->appointment_date->toDateString().' '.$booking->start_time, $bookingTimezone)->utc();
                    $bookingEnd = Carbon::parse($booking->appointment_date->toDateString().' '.$booking->end_time, $bookingTimezone)->utc();
                    return $bookingStart->lt($candidateEndUtc) && $bookingEnd->gt($candidateStartUtc);
                });
                if (!$past && !$duringBreak && !$overlap) {
                    $result[] = ['start_time'=>$cursor->format('H:i'), 'end_time'=>$slotEnd->format('H:i'), 'duration_minutes'=>$duration];
                }
                $cursor = $slotEnd;
            }
        }
        return $result;
    }

    public function assertAvailable(int $doctorProfileId, int $clinicId, string $date, string $start, string $end): DoctorSchedule
    {
        $tz = ClinicTimezone::forClinic($clinicId);
        $day = Carbon::parse($date, $tz);
        if ($day->toDateString() < Carbon::today($tz)->toDateString()) {
            throw ValidationException::withMessages(['appointment_date'=>['Geçen senä bron edip bolmaýar.']]);
        }
        if (DoctorDayOff::where('doctor_profile_id',$doctorProfileId)->where('clinic_id',$clinicId)->whereDate('off_date',$date)->exists()) {
            throw ValidationException::withMessages(['appointment_date'=>['Lukman bu gün dynç alýar.']]);
        }
        $schedule = DoctorSchedule::query()->where('doctor_profile_id',$doctorProfileId)->where('clinic_id',$clinicId)
            ->where('day_of_week',$day->dayOfWeekIso)->where('is_active',true)
            ->where('start_time','<=',$start)->where('end_time','>=',$end)->first();
        if (!$schedule) throw ValidationException::withMessages(['start_time'=>['Lukman bu wagtda kabul etmeýär.']]);

        $slotMinutes = max(5,(int)$schedule->slot_duration_minutes);
        $base = Carbon::parse($date.' '.$schedule->start_time,$tz);
        $requested = Carbon::parse($date.' '.$start,$tz);
        if ($base->diffInMinutes($requested) % $slotMinutes !== 0) {
            throw ValidationException::withMessages(['start_time'=>['Saýlanan wagt lukmanyň kabul tertibine gabat gelenok.']]);
        }
        if ($schedule->break_start && $schedule->break_end && $start < $schedule->break_end && $end > $schedule->break_start) {
            throw ValidationException::withMessages(['start_time'=>['Bu wagt arakesmä gabat gelýär.']]);
        }
        if ($day->isToday() && $requested->lte(Carbon::now($tz))) {
            throw ValidationException::withMessages(['start_time'=>['Geçen wagta bron edip bolmaýar.']]);
        }
        $candidateStartUtc = Carbon::parse($date.' '.$start, $tz)->utc();
        $candidateEndUtc = Carbon::parse($date.' '.$end, $tz)->utc();
        $busy = Appointment::query()->with('clinic:id,timezone')
            ->where('doctor_profile_id', $doctorProfileId)
            ->whereBetween('appointment_date', [$day->copy()->subDay()->toDateString(), $day->copy()->addDay()->toDateString()])
            ->whereIn('status', ['pending', 'confirmed'])->get();
        $overlap = $busy->contains(function ($booking) use ($candidateStartUtc, $candidateEndUtc) {
            $bookingTimezone = $booking->clinic?->timezone ?: config('app.timezone', 'Asia/Ashgabat');
            $bookingStart = Carbon::parse($booking->appointment_date->toDateString().' '.$booking->start_time, $bookingTimezone)->utc();
            $bookingEnd = Carbon::parse($booking->appointment_date->toDateString().' '.$booking->end_time, $bookingTimezone)->utc();
            return $bookingStart->lt($candidateEndUtc) && $bookingEnd->gt($candidateStartUtc);
        });
        if ($overlap) throw ValidationException::withMessages(['start_time'=>['Bu wagt eýýäm bron edilen. Başga wagt saýlaň.']]);
        return $schedule;
    }
}
