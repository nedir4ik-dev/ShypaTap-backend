<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\DoctorProfile;
use App\Services\AppointmentAvailabilityService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class DoctorController extends Controller
{
    public function __construct(protected AppointmentAvailabilityService $availabilityService) {}

    public function index(Request $request): JsonResponse
    {
        $query = DoctorProfile::with(['user', 'specialization', 'clinics'])
            ->withAvg('reviews', 'rating')->where('is_verified', true)
            ->whereHas('user', fn($q) => $q->where('is_active', true));
        if ($request->filled('specialization_id')) $query->where('specialization_id', $request->integer('specialization_id'));
        if ($request->filled('specialization')) $query->whereHas('specialization', fn($q) => $q->where('name', 'ilike', '%' . $request->string('specialization') . '%'));
        if ($request->filled('clinic_id')) $query->whereHas('clinics', fn($q) => $q->where('clinics.id', $request->integer('clinic_id')));
        if ($request->filled('city')) $query->whereHas('clinics', fn($q) => $q->where('city', 'ilike', '%' . $request->string('city') . '%'));
        if ($request->filled('search')) $query->whereHas('user', fn($q) => $q->where('name', 'ilike', '%' . $request->string('search') . '%'));
        return response()->json($query->orderBy('id')->paginate(min(50, max(1, $request->integer('per_page', 12)))));
    }

    public function show(int $id): JsonResponse
    {
        $doctor = DoctorProfile::with(['user', 'specialization', 'clinics', 'schedules', 'reviews:id,doctor_profile_id,appointment_id,rating,comment,created_at'])
            ->withAvg('reviews', 'rating')
            ->where('is_verified', true)
            ->whereHas('user', fn ($query) => $query->where('is_active', true))
            ->findOrFail($id);
        return response()->json($doctor);
    }

    public function slots(Request $request, int $id): JsonResponse
    {
        $data = $request->validate(['date' => 'required|date_format:Y-m-d', 'clinic_id' => 'required|integer|exists:clinics,id']);
        return response()->json([
            'doctor_profile_id' => $id,
            'date' => $data['date'],
            'slots' => $this->availabilityService->slots($id, (int)$data['clinic_id'], $data['date'])
        ]);
    }
    public function getProfile(Request $request): JsonResponse
    {
        $user = $request->user();
        $doctor = DoctorProfile::with(['user', 'specialization', 'clinics'])
            ->where('user_id', $user->id)
            ->first();

        return response()->json([
            'data' => [
                'id' => $doctor?->id,
                'user' => $user,
                'name' => $user->name,
                'phone' => $user->phone,
                'avatar_url' => $user->avatar_url,
                'experience_years' => $doctor?->experience_years ?? 0,
                'consultation_fee' => $doctor?->consultation_fee ?? 0,
                'bio' => $doctor?->biography ?? '',
                'specialization' => $doctor?->specialization?->name,
                'clinics' => $doctor?->clinics ?? [],
            ],
        ]);
    }

    public function updateProfile(Request $request): JsonResponse
    {
        $user = $request->user();

        $validated = $request->validate([
            'name' => ['sometimes', 'required', 'string', 'max:255'],
            'phone' => ['sometimes', 'nullable', 'string', 'max:30'],
            'experience_years' => ['sometimes', 'nullable', 'integer', 'min:0', 'max:70'],
            'consultation_fee' => ['sometimes', 'nullable', 'numeric', 'min:0'],
            'bio' => ['sometimes', 'nullable', 'string', 'max:1000'],
        ]);

        $user->update(array_intersect_key($validated, array_flip(['name', 'phone'])));

        $profileData = [];
        if (array_key_exists('experience_years', $validated)) {
            $profileData['experience_years'] = $validated['experience_years'] ?? 0;
        }
        if (array_key_exists('consultation_fee', $validated)) {
            $profileData['consultation_fee'] = $validated['consultation_fee'] ?? 0;
        }
        if (array_key_exists('bio', $validated)) {
            $profileData['biography'] = $validated['bio'];
        }

        if ($profileData) {
            DoctorProfile::where('user_id', $user->id)->update($profileData);
        }

        $doctor = DoctorProfile::with(['specialization', 'clinics'])
            ->where('user_id', $user->id)->first();

        return response()->json([
            'message' => 'Profil maglumatlaryňyz üstünlikli täzelendi!',
            'data' => [
                'name' => $user->fresh()->name,
                'phone' => $user->fresh()->phone,
                'avatar_url' => $user->fresh()->avatar_url,
                'experience_years' => $doctor?->experience_years ?? 0,
                'consultation_fee' => $doctor?->consultation_fee ?? 0,
                'bio' => $doctor?->biography ?? '',
            ],
        ]);
    }

}
