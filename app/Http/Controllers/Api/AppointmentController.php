<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\Review;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class AppointmentController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'doctor_id' => 'required|exists:doctor_profiles,id',
            'clinic_id' => 'nullable|exists:clinics,id',
            'appointment_date' => 'required|date|after_or_equal:today',
            'start_time' => 'required|date_format:H:i',
            'reason' => 'nullable|string|max:500',
        ]);

        $patientId = $request->user()->id;

        return DB::transaction(function () use ($validated, $patientId) {
            $exists = Appointment::where('doctor_id', $validated['doctor_id'])
                ->where('appointment_date', $validated['appointment_date'])
                ->where('start_time', $validated['start_time'])
                ->whereIn('status', ['pending', 'confirmed'])
                ->lockForUpdate()
                ->exists();

            if ($exists) {
                return response()->json([
                    'message' => 'Bu saýlanan wagt eýýäm bron edilen.'
                ], 422);
            }

            $startTime = Carbon::createFromFormat('H:i', $validated['start_time']);
            $endTime = $startTime->copy()->addMinutes(30)->format('H:i:s');

            $appointment = Appointment::create([
                'patient_id' => $patientId,
                'doctor_id' => $validated['doctor_id'],
                'clinic_id' => $validated['clinic_id'] ?? null,
                'appointment_date' => $validated['appointment_date'],
                'start_time' => $validated['start_time'] . ':00',
                'end_time' => $endTime,
                'status' => 'pending',
                'reason' => $validated['reason'] ?? null,
            ]);

            return response()->json([
                'message' => 'Bron üstünlikli döredildi.',
                'appointment_code' => 'ST-' . date('Y') . '-' . str_pad($appointment->id, 5, '0', STR_PAD_LEFT),
                'data' => $appointment
            ], 201);
        });
    }

    public function myAppointments(Request $request): JsonResponse
    {
        $appointments = Appointment::with(['doctor.user', 'doctor.specialization', 'clinic'])
            ->where('patient_id', $request->user()->id)
            ->orderBy('appointment_date', 'desc')
            ->orderBy('start_time', 'desc')
            ->paginate(10);

        return response()->json($appointments);
    }

    public function cancel(Request $request, int $id): JsonResponse
    {
        $appointment = Appointment::where('id', $id)
            ->where('patient_id', $request->user()->id)
            ->firstOrFail();

        if (in_array($appointment->status, ['completed', 'cancelled'])) {
            return response()->json(['message' => 'Bu bron eýýäm tamamlanan ýa-da ýatyrylan.'], 400);
        }

        $appointment->update([
            'status' => 'cancelled',
            'cancellation_reason' => $request->input('reason', 'Näsag tarapyndan ýatyryldy')
        ]);

        return response()->json(['message' => 'Bron ýatyryldy.', 'data' => $appointment]);
    }

    public function storeReview(Request $request, int $id): JsonResponse
    {
        $appointment = Appointment::where('id', $id)
            ->where('patient_id', $request->user()->id)
            ->where('status', 'completed')
            ->firstOrFail();

        $validated = $request->validate([
            'rating' => 'required|integer|min:1|max:5',
            'comment' => 'nullable|string|max:1000',
        ]);

        $review = Review::updateOrCreate(
            ['appointment_id' => $appointment->id],
            [
                'patient_id' => $request->user()->id,
                'doctor_id' => $appointment->doctor_id,
                'rating' => $validated['rating'],
                'comment' => $validated['comment'] ?? null,
            ]
        );

        return response()->json(['message' => 'Baha berildi.', 'data' => $review]);
    }

    public function doctorAppointments(Request $request): JsonResponse
    {
        $doctorProfile = $request->user()->doctorProfile;

        if (!$doctorProfile) {
            return response()->json(['message' => 'Lukman profili tapylmady.'], 404);
        }

        $appointments = Appointment::with(['patient', 'clinic'])
            ->where('doctor_id', $doctorProfile->id)
            ->orderBy('appointment_date', 'desc')
            ->orderBy('start_time', 'asc')
            ->paginate(15);

        return response()->json($appointments);
    }

    public function updateStatus(Request $request, int $id): JsonResponse
    {
        $doctorProfile = $request->user()->doctorProfile;

        $appointment = Appointment::where('id', $id)
            ->where('doctor_id', $doctorProfile->id)
            ->firstOrFail();

        $validated = $request->validate([
            'status' => 'required|in:confirmed,completed,rejected,cancelled',
        ]);

        $appointment->update(['status' => $validated['status']]);

        return response()->json(['message' => 'Bron statusy täzelendi.', 'data' => $appointment]);
    }

    public function storeForPatient(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'patient_id' => 'required|exists:users,id',
            'doctor_id' => 'required|exists:doctor_profiles,id',
            'clinic_id' => 'nullable|exists:clinics,id',
            'appointment_date' => 'required|date',
            'start_time' => 'required|date_format:H:i',
            'reason' => 'nullable|string',
        ]);

        $startTime = Carbon::createFromFormat('H:i', $validated['start_time']);
        $endTime = $startTime->copy()->addMinutes(30)->format('H:i:s');

        $appointment = Appointment::create([
            'patient_id' => $validated['patient_id'],
            'doctor_id' => $validated['doctor_id'],
            'clinic_id' => $validated['clinic_id'] ?? null,
            'appointment_date' => $validated['appointment_date'],
            'start_time' => $validated['start_time'] . ':00',
            'end_time' => $endTime,
            'status' => 'confirmed',
            'reason' => $validated['reason'] ?? 'Registratura arkaly ýazyldy',
        ]);

        return response()->json(['message' => 'Näsag üstünlikli ýazyldy.', 'data' => $appointment], 201);
    }
}
