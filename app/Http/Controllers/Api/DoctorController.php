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
        $doctor = DoctorProfile::with(['user', 'specialization', 'clinics', 'schedules', 'reviews.patient'])
            ->withAvg('reviews', 'rating')->where('is_verified', true)->findOrFail($id);
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

        // Giriş eden lukmanyň profil maglumatlaryny we gatnaşyklaryny alýarys
        $doctor = DoctorProfile::with(['user', 'specialization', 'clinics'])
            ->where('user_id', $user->id)
            ->first();

        if (!$doctor) {
            return response()->json([
                'data' => [
                    'name' => $user->name,
                    'phone' => $user->phone,
                    'experience_years' => 0,
                    'consultation_fee' => 0,
                    'bio' => ''
                ]
            ]);
        }

        return response()->json([
            'data' => [
                'name' => $user->name,
                'phone' => $user->phone,
                'experience_years' => $doctor->experience_years,
                'consultation_fee' => $doctor->consultation_fee,
                'bio' => $doctor->bio,
                'specialization' => $doctor->specialization?->name
            ]
        ]);
    }

    public function updateProfile(Request $request, AppointmentAvailabilityService $availabilityService): JsonResponse
    {
        $user = $request->user();

        $validated = $request->validate([
            'name' => ['sometimes', 'required', 'string', 'max:255'],
            'phone' => ['sometimes', 'nullable', 'string', 'max:30'],
            'experience_years' => ['sometimes', 'nullable', 'integer', 'min:0'],
            'consultation_fee' => ['sometimes', 'nullable', 'numeric', 'min:0'],
            'bio' => ['sometimes', 'nullable', 'string', 'max:1000'],
        ]);

        $user->update($request->only(['name', 'phone']));

        $doctor = DoctorProfile::updateOrCreate(
            ['user_id' => $user->id],
            $request->only(['experience_years', 'consultation_fee', 'bio'])
        );

        return response()->json([
            'message' => 'Profil maglumatlaryňyz üstünlikli täzelendi!',
            'data' => [
                'name' => $user->name,
                'phone' => $user->phone,
                'experience_years' => $doctor->experience_years,
                'consultation_fee' => $doctor->consultation_fee,
                'bio' => $doctor->bio,
            ]
        ]);
    }
}
