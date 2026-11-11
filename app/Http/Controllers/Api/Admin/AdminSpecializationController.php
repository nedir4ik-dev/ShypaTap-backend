<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Specialization;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class AdminSpecializationController extends Controller
{
    public function index(Request $request)
    {
        $specializations = Specialization::withCount('doctors')->orderBy('name')->paginate(20);

        // Eger sorag API (JSON) arkaly gelse:
        if ($request->wantsJson() || $request->is('api/*')) {
            return response()->json([
                'data' => $specializations,
            ]);
        }

        // Brauzerden girilende Blade sahypasyny görkezýäris:
        return view('admin.specializations.index', compact('specializations'));
    }

    public function create(Request $request)
    {
        if ($request->wantsJson() || $request->is('api/*')) {
            return response()->json(['message' => 'API üçin bu ýol ýok.']);
        }

        return view('admin.specializations.create');
    }

    public function show(Request $request, Specialization $specialization)
    {
        $specialization->loadCount('doctors');

        if ($request->wantsJson() || $request->is('api/*')) {
            return response()->json(['data' => $specialization]);
        }

        return view('admin.specializations.show', compact('specialization'));
    }

    public function edit(Request $request, Specialization $specialization)
    {
        if ($request->wantsJson() || $request->is('api/*')) {
            return response()->json(['data' => $specialization]);
        }

        return view('admin.specializations.edit', compact('specialization'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:specializations,name'],
            'slug' => ['nullable', 'string', 'max:255', 'unique:specializations,slug'],
            'description' => ['nullable', 'string'],
            'is_active' => ['sometimes', 'boolean'],
        ]);

        $data['slug'] = $data['slug'] ?? Str::slug($data['name']);
        if (Specialization::where('slug', $data['slug'])->exists()) {
            if ($request->wantsJson() || $request->is('api/*')) {
                return response()->json(['message' => 'Bu slug eýýäm ulanylýar. Başga slug giriziň.'], 422);
            }
            return back()->withErrors(['slug' => 'Bu slug eýýäm ulanylýar.'])->withInput();
        }

        $data['is_active'] = $request->has('is_active');
        $specialization = Specialization::create($data);

        if ($request->wantsJson() || $request->is('api/*')) {
            return response()->json(['message' => 'Hünär ugry döredildi.', 'data' => $specialization], 201);
        }

        return redirect()->route('admin.specializations.index')->with('success', 'Hünär ugry üstünlikli döredildi.');
    }

    public function update(Request $request, Specialization $specialization)
    {
        $data = $request->validate([
            'name' => ['sometimes', 'required', 'string', 'max:255', Rule::unique('specializations', 'name')->ignore($specialization->id)],
            'slug' => ['sometimes', 'required', 'string', 'max:255', Rule::unique('specializations', 'slug')->ignore($specialization->id)],
            'description' => ['sometimes', 'nullable', 'string'],
            'is_active' => ['sometimes', 'boolean'],
        ]);

        if (isset($data['name']) && !isset($data['slug'])) {
            $candidate = Str::slug($data['name']);
            if (!Specialization::where('slug', $candidate)->whereKeyNot($specialization->id)->exists()) {
                $data['slug'] = $candidate;
            }
        }

        $data['is_active'] = $request->has('is_active');
        $specialization->update($data);

        if ($request->wantsJson() || $request->is('api/*')) {
            return response()->json(['message' => 'Hünär ugry täzelendi.', 'data' => $specialization->fresh()]);
        }

        return redirect()->route('admin.specializations.index')->with('success', 'Hünär ugry üstünlikli täzelendi.');
    }

    public function destroy(Request $request, Specialization $specialization)
    {
        if ($specialization->doctors()->exists()) {
            $specialization->update(['is_active' => false]);

            if ($request->wantsJson() || $request->is('api/*')) {
                return response()->json([
                    'message' => 'Bu hünäre degişli lukmanlar bar. Hünär pozulmady, diňe ýapyldy.',
                ]);
            }
            return redirect()->route('admin.specializations.index')->with('success', 'Hünäre degişli lukmanlar bolandygy üçin hünär ýapyk ýagdaýa geçirildi.');
        }

        $specialization->delete();

        if ($request->wantsJson() || $request->is('api/*')) {
            return response()->json(['message' => 'Hünär ugry pozuldy.']);
        }

        return redirect()->route('admin.specializations.index')->with('success', 'Hünär ugry üstünlikli pozuldy.');
    }
}
