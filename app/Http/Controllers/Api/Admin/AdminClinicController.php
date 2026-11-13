<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Clinic;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class AdminClinicController extends Controller
{
    public function index(Request $request)
    {
        $query = Clinic::query()->withCount(['doctors', 'appointments']);

        if ($request->filled('city')) {
            $query->where('city', 'ilike', '%' . $request->string('city')->toString() . '%');
        }
        if ($request->has('is_active')) {
            $query->where('is_active', $request->boolean('is_active'));
        }

        $clinics = $query->latest()->paginate(15);

        // Eger sorag API (JSON) arkaly gelse:
        if ($request->wantsJson() || $request->is('api/*')) {
            return response()->json($clinics);
        }

        // Eger brauzerden admin panel arkaly açylsa Blade view gaýtarýarys:
        return view('admin.clinics.index', compact('clinics'));
    }

    public function create(Request $request)
    {
        // Blade sahypasy üçin create (täze goşmak formasy)
        if ($request->wantsJson() || $request->is('api/*')) {
            return response()->json(['message' => 'API üçin bu ýol ýok.']);
        }
        return view('admin.clinics.create');
    }

    public function show(Request $request, Clinic $clinic)
    {
        $clinic->loadCount(['doctors', 'appointments']);

        if ($request->wantsJson() || $request->is('api/*')) {
            return response()->json(['data' => $clinic]);
        }

        return view('admin.clinics.show', compact('clinic'));
    }

    public function edit(Request $request, Clinic $clinic)
    {
        // Blade sahypasy üçin edit (üýtgetmek formasy)
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

        if ($request->wantsJson() || $request->is('api/*')) {
            return response()->json(['message' => 'Klinika döredildi.', 'data' => $clinic], 201);
        }

        return redirect()->route('admin.clinics.index')->with('success', 'Klinika üstünlikli döredildi.');
    }

    public function update(Request $request, Clinic $clinic)
    {
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
        if ($clinic->appointments()->exists()) {
            $clinic->update(['is_active' => false]);

            if ($request->wantsJson() || $request->is('api/*')) {
                return response()->json([
                    'message' => 'Klinikada bron taryhy bar. Şonuň üçin ol pozulmady, diňe ýapyk ýagdaýa geçirildi.',
                ]);
            }
            return redirect()->route('admin.clinics.index')->with('success', 'Klinikada bron taryhy bolandygy üçin ýapyk ýagdaýa geçirildi.');
        }

        $clinic->delete();

        if ($request->wantsJson() || $request->is('api/*')) {
            return response()->json(['message' => 'Klinika arhiwlendi.']);
        }

        return redirect()->route('admin.clinics.index')->with('success', 'Klinika üstünlikli pozuldy.');
    }
}
