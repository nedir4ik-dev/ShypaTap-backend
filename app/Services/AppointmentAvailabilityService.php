<?php

namespace App\Services;

use App\Models\Appointment;
use App\Models\Clinic;
use App\Models\DoctorDayOff;
use App\Models\DoctorSchedule;
use Carbon\Carbon;
use Illuminate\Validation\ValidationException;

class AppointmentAvailabilityService
{
    public function slots(int $doctorProfileId, int $clinicId, string $date): array
    {
        $clinic = Clinic::whereKey($clinicId)->where('is_active',true)->first();
        if (!$clinic) return [];
        $timezone = $clinic->timezone ?: config('app.timezone', 'Asia/Ashgabat');
        $day = Carbon::parse($date, $timezone);
        if ($day->toDateString() < Carbon::today($timezone)->toDateString()) return [];

        $schedules = DoctorSchedule::query()
            ->where('doctor_profile_id', $doctorProfileId)
            ->where('clinic_id', $clinicId)
            ->where('day_of_week', $day->dayOfWeekIso)
            ->where('is_active', true)->orderBy('start_time')->get();

        if ($schedules->isEmpty() || DoctorDayOff::where('doctor_profile_id', $doctorProfileId)
            ->where('clinic_id', $clinicId)->whereDate('off_date', $day->toDateString())->exists()) return [];

        $busy = Appointment::query()->with('clinic:id,timezone')->where('doctor_profile_id', $doctorProfileId)
            ->whereBetween('appointment_date', [$day->copy()->subDays(2)->toDateString(), $day->copy()->addDays(2)->toDateString()])
            ->whereIn('status', ['pending', 'confirmed', 'checked_in'])->get(['id','clinic_id','appointment_date','start_time','end_time']);

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
                $overlap = $busy->contains(fn ($booking) => $this->overlapsInUtc($booking, $day->toDateString(), $cursor->format('H:i:s'), $slotEnd->format('H:i:s'), $timezone));
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
        $clinic = Clinic::whereKey($clinicId)->where('is_active',true)->first();
        if (!$clinic) throw ValidationException::withMessages(['clinic_id'=>['Klinika işjeň däl ýa-da tapylmady.']]);
        $doctor = \App\Models\DoctorProfile::whereKey($doctorProfileId)->whereHas('clinics',fn($q)=>$q->whereKey($clinicId)->where('is_active',true))->first();
        if (!$doctor) throw ValidationException::withMessages(['doctor_profile_id'=>['Lukman bu klinikada kabul etmeýär.']]);
        $tz = $clinic->timezone ?: config('app.timezone', 'Asia/Ashgabat');
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
        $candidates = Appointment::with('clinic:id,timezone')->where('doctor_profile_id',$doctorProfileId)
            ->whereBetween('appointment_date', [$day->copy()->subDays(2)->toDateString(), $day->copy()->addDays(2)->toDateString()])
            ->whereIn('status',['pending','confirmed','checked_in'])->get(['id','clinic_id','appointment_date','start_time','end_time']);
        $overlap = $candidates->contains(fn ($booking) => $this->overlapsInUtc($booking, $date, $start, $end, $tz));
        if ($overlap) throw ValidationException::withMessages(['start_time'=>['Bu lukmanyň başga klinikadaky bronunyň wagty bilen gabat gelýär. Başga wagt saýlaň.']]);
        return $schedule;
    }

    private function overlapsInUtc(Appointment $booking, string $targetDate, string $targetStart, string $targetEnd, string $targetTimezone): bool
    {
        $bookingTimezone = $booking->clinic?->timezone ?: config('app.timezone', 'Asia/Ashgabat');
        $bookingDate = $booking->appointment_date instanceof \Carbon\CarbonInterface
            ? $booking->appointment_date->toDateString() : (string) $booking->appointment_date;
        $existingStart = Carbon::parse($bookingDate.' '.$booking->start_time, $bookingTimezone)->utc();
        $existingEnd = Carbon::parse($bookingDate.' '.$booking->end_time, $bookingTimezone)->utc();
        $requestedStart = Carbon::parse($targetDate.' '.$targetStart, $targetTimezone)->utc();
        $requestedEnd = Carbon::parse($targetDate.' '.$targetEnd, $targetTimezone)->utc();
        return $existingStart->lt($requestedEnd) && $existingEnd->gt($requestedStart);
    }

}
