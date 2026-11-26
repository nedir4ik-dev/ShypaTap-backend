<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Clinic;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use App\Models\Appointment;
use App\Models\DoctorProfile;
use App\Models\Review;

class AdminClinicController extends Controller
{
    public function index(Request $request)
    {
        $query = Clinic::query()->withCount(['doctors', 'appointments']);
        if (!in_array(strtolower((string)$request->user()->role), ['superadmin', 'super_admin'], true)) $query->whereIn('id', $request->user()->staffClinics()->pluck('clinics.id'));
        if ($request->filled('clinic_id')) abort_unless($request->user()->belongsToClinic((int)$request->query('clinic_id')), 403);

        if ($request->filled('city')) {
            $query->where('city', 'ilike', '%' . $request->string('city')->toString() . '%');
        }
        if ($request->has('is_active')) {
            $query->where('is_active', $request->boolean('is_active'));
        }

        $clinics = $query->latest()->paginate(15);

        if ($request->wantsJson() || $request->is('api/*')) {
            return response()->json($clinics);
        }

        return view('admin.clinics.index', compact('clinics'));
    }

    public function create(Request $request)
    {
        if ($request->wantsJson() || $request->is('api/*')) {
            return response()->json(['message' => 'API üçin bu ýol ýok.']);
        }
        return view('admin.clinics.create');
    }

    public function show(Request $request, Clinic $clinic)
    {
        abort_unless($request->user()->belongsToClinic((int)$clinic->id), 403);
        $clinic->loadCount(['doctors', 'appointments']);

        if ($request->wantsJson() || $request->is('api/*')) {
            return response()->json(['data' => $clinic]);
        }

        return view('admin.clinics.show', compact('clinic'));
    }

    public function edit(Request $request, Clinic $clinic)
    {
        abort_unless($request->user()->belongsToClinic((int)$clinic->id), 403);
        if ($request->wantsJson() || $request->is('api/*')) {
            return response()->json(['data' => $clinic]);
        }
        return view('admin.clinics.edit', compact('clinic'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:30'],
            'email' => ['nullable', 'email', 'max:255', 'unique:clinics,email'],
            'city' => ['required', 'string', 'max:255'],
            'address' => ['required', 'string'],
            'description' => ['nullable', 'string'],
            'timezone' => ['sometimes', 'required', 'timezone'],
            'is_active' => ['sometimes', 'boolean'],
        ]);

        if ($request->has('is_active')) $data['is_active'] = $request->boolean('is_active');

        $clinic = Clinic::create($data);
        if (!in_array(strtolower((string)$request->user()->role), ['superadmin', 'super_admin'], true)) $clinic->staff()->syncWithoutDetaching([$request->user()->id]);

        if ($request->wantsJson() || $request->is('api/*')) {
            return response()->json(['message' => 'Klinika döredildi.', 'data' => $clinic], 201);
        }

        return redirect()->route('admin.clinics.index')->with('success', 'Klinika üstünlikli döredildi.');
    }

    public function update(Request $request, Clinic $clinic)
    {
        abort_unless($request->user()->belongsToClinic((int)$clinic->id), 403);
        $data = $request->validate([
            'name' => ['sometimes', 'required', 'string', 'max:255'],
            'phone' => ['sometimes', 'nullable', 'string', 'max:30'],
            'email' => ['sometimes', 'nullable', 'email', 'max:255', Rule::unique('clinics', 'email')->ignore($clinic->id)],
            'city' => ['sometimes', 'required', 'string', 'max:255'],
            'address' => ['sometimes', 'required', 'string'],
            'description' => ['sometimes', 'nullable', 'string'],
            'timezone' => ['sometimes', 'required', 'timezone'],
            'is_active' => ['sometimes', 'boolean'],
        ]);

        if ($request->has('is_active')) $data['is_active'] = $request->boolean('is_active');

        $clinic->update($data);

        if ($request->wantsJson() || $request->is('api/*')) {
            return response()->json(['message' => 'Klinika täzelendi.', 'data' => $clinic->fresh()]);
        }

        return redirect()->route('admin.clinics.index')->with('success', 'Klinika maglumatlary üýtgedildi.');
    }

    public function destroy(Request $request, Clinic $clinic)
    {
        abort_unless($request->user()->belongsToClinic((int)$clinic->id), 403);
        DB::transaction(function () use ($clinic) {
            $batch = (string) Str::uuid();
            $now = now();
            Appointment::query()->where('clinic_id', $clinic->id)->whereNull('deleted_at')
                ->update(['deleted_at' => $now, 'trash_batch_id' => $batch]);
            $appointmentIds = Appointment::onlyTrashed()->where('trash_batch_id', $batch)->pluck('id');
            if ($appointmentIds->isNotEmpty()) {
                Review::query()->whereIn('appointment_id', $appointmentIds)->whereNull('deleted_at')
                    ->update(['deleted_at' => $now, 'trash_batch_id' => $batch]);
            }
            $doctorIds = $clinic->doctors()->pluck('doctor_profiles.id');
            foreach ($doctorIds as $doctorId) {
                $otherActiveClinics = DB::table('clinic_doctors')->join('clinics', 'clinics.id', '=', 'clinic_doctors.clinic_id')
                    ->where('clinic_doctors.doctor_profile_id', $doctorId)->where('clinics.id', '<>', $clinic->id)
                    ->whereNull('clinics.deleted_at')->exists();
                if (!$otherActiveClinics) {
                    DoctorProfile::query()->whereKey($doctorId)->whereNull('deleted_at')
                        ->update(['deleted_at' => $now, 'trash_batch_id' => $batch]);
                }
            }
            $clinic->forceFill(['trash_batch_id' => $batch])->save();
            $clinic->delete();
        });
        if ($request->wantsJson() || $request->is('api/*')) return response()->json(['message' => 'Klinika we onuň degişli işjeň ýazgylary Trash bölümine geçirildi.']);
        return redirect()->route('admin.clinics.index')->with('success', 'Klinika we degişli ýazgylar Trash bölümine geçirildi.');
    }
}
