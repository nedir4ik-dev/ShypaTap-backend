<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\DoctorProfile;
use App\Services\AppointmentAvailabilityService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class DoctorController extends Controller
{
    public function __construct(
        protected AppointmentAvailabilityService $availabilityService
    ) {}

    public function index(Request $request): JsonResponse
    {
        $query = DoctorProfile::with(['user', 'specialization', 'clinics'])
            ->where('is_verified', true);

        if ($request->has('specialization_id')) {
            $query->where('specialization_id', $request->input('specialization_id'));
        }

        if ($request->has('search')) {
            $search = $request->input('search');
            $query->whereHas('user', function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%");
            });
        }

        $doctors = $query->paginate(12);

        return response()->json($doctors);
    }

    /**
     * Lukmanyň giňişleýin profili
     */
    public function show(int $id): JsonResponse
    {
        $doctor = DoctorProfile::with(['user', 'specialization', 'clinics', 'schedules', 'reviews.patient'])
            ->where('is_verified', true)
            ->findOrFail($id);

        return response()->json($doctor);
    }

    /**
     * Lukmanyň boş wagtlary (Slots)
     */
    public function slots(Request $request, int $id): JsonResponse
    {
        $request->validate([
            'date' => 'nullable|date|after_or_equal:today',
        ]);

        $date = $request->input('date', date('Y-m-d'));
        $slots = $this->availabilityService->getAvailableSlots($id, $date);

        return response()->json([
            'doctor_id' => $id,
            'date' => $date,
            'slots' => $slots
        ]);
    }
}
