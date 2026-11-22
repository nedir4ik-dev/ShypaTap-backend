<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\DoctorDayOff;
use App\Models\DoctorSchedule;
use App\Support\ClinicTimezone;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class DoctorScheduleController extends Controller
{
    private function profile(Request $request)
    {
        $profile = $request->user()->doctorProfile;
        abort_unless($profile, 404, 'Lukman profili tapylmady.');
        return $profile;
    }

    public function index(Request $request)
    {
        return response()->json(
            $this->profile($request)->schedules()->with('clinic')->orderBy('day_of_week')->get()
        );
    }

    public function store(Request $request)
    {
        $profile = $this->profile($request);
        $data = $request->validate([
            'clinic_id' => ['required', 'integer', 'exists:clinics,id'],
            'day_of_week' => ['required', 'integer', 'min:1', 'max:7'],
            'start_time' => ['required', 'date_format:H:i'],
            'end_time' => ['required', 'date_format:H:i', 'after:start_time'],
            'break_start' => ['nullable', 'date_format:H:i'],
            'break_end' => ['nullable', 'date_format:H:i', 'after:break_start'],
            'slot_duration_minutes' => ['required', 'integer', 'min:5', 'max:240'],
            'is_active' => ['sometimes', 'boolean'],
        ]);

        abort_unless($profile->clinics()->where('clinics.id', $data['clinic_id'])->exists(), 403);
        $schedule = $profile->schedules()->create($data);
        return response()->json(['data' => $schedule], 201);
    }

    public function update(Request $request, int $id)
    {
        $schedule = $this->profile($request)->schedules()->findOrFail($id);
        $data = $request->validate([
            'start_time' => ['sometimes', 'date_format:H:i'],
            'end_time' => ['sometimes', 'date_format:H:i'],
            'break_start' => ['sometimes', 'nullable', 'date_format:H:i'],
            'break_end' => ['sometimes', 'nullable', 'date_format:H:i'],
            'slot_duration_minutes' => ['sometimes', 'integer', 'min:5', 'max:240'],
            'is_active' => ['sometimes', 'boolean'],
        ]);

        $start = $data['start_time'] ?? $schedule->start_time;
        $end = $data['end_time'] ?? $schedule->end_time;
        if ($start >= $end) {
            throw ValidationException::withMessages(['end_time' => ['Tamamlaýyş wagty başlangyçdan soň bolmaly.']]);
        }
        $breakStart = $data['break_start'] ?? $schedule->break_start;
        $breakEnd = $data['break_end'] ?? $schedule->break_end;
        if (($breakStart && ! $breakEnd) || (! $breakStart && $breakEnd) || ($breakStart && $breakEnd && ($breakStart >= $breakEnd || $breakStart < $start || $breakEnd > $end))) {
            throw ValidationException::withMessages(['break_end' => ['Arakesme wagty iş tertibiniň içinde bolmaly.']]);
        }
        $schedule->update($data);
        return response()->json(['data' => $schedule->fresh()]);
    }

    public function destroy(Request $request, int $id)
    {
        $this->profile($request)->schedules()->findOrFail($id)->delete();
        return response()->json(['message' => 'Iş tertibi pozuldy.']);
    }

    public function daysOff(Request $request)
    {
        return response()->json($this->profile($request)->daysOff()->with('clinic')->orderBy('off_date')->get());
    }

    public function addDayOff(Request $request)
    {
        $profile = $this->profile($request);
        $data = $request->validate([
            'clinic_id' => ['required', 'integer', 'exists:clinics,id'],
            'off_date' => ['required', 'date_format:Y-m-d'],
            'reason' => ['nullable', 'string', 'max:255'],
        ]);
        abort_unless($profile->clinics()->where('clinics.id', $data['clinic_id'])->exists(), 403);

        $timezone = ClinicTimezone::forClinic((int) $data['clinic_id']);
        if (Carbon::parse($data['off_date'], $timezone)->startOfDay()->lt(Carbon::today($timezone))) {
            throw ValidationException::withMessages(['off_date' => ['Geçen senä dynç güni goşup bolmaýar.']]);
        }

        $dayOff = $profile->daysOff()->create($data);
        return response()->json(['data' => $dayOff], 201);
    }

    public function deleteDayOff(Request $request, int $id)
    {
        $this->profile($request)->daysOff()->findOrFail($id)->delete();
        return response()->json(['message' => 'Dynç güni pozuldy.']);
    }
}
