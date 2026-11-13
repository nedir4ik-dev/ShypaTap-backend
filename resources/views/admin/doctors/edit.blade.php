@extends('layouts.app')

@section('title', 'Lukman Maglumatlaryny Üýtgetmek')

@section('header', 'Lukman Üýtgetmek')

@section('content')
<div class="max-w-4xl mx-auto space-y-6">

    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <h1 class="text-2xl font-bold text-gray-900 dark:text-white flex items-center gap-2">
            <i class="ti ti-user-edit text-blue-600 dark:text-blue-400"></i>
            Lukman Maglumatlaryny Üýtgetmek
        </h1>
        <a href="{{ route('admin.doctors.index') }}" class="inline-flex items-center gap-1.5 text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white text-sm font-medium transition">
            <i class="ti ti-arrow-left text-lg"></i>
            Yzyna gaýtmak
        </a>
    </div>

    @if ($errors->any())
    <div class="bg-red-50 dark:bg-red-500/10 border border-red-200 dark:border-red-500/30 text-red-700 dark:text-red-400 p-4 rounded-2xl space-y-2">
        <div class="flex items-center gap-2 font-semibold text-sm">
            <i class="ti ti-alert-circle text-lg"></i>
            <span>Ýalňyşlyk bar! Maglumatlary barlap täzeden ediň.</span>
        </div>
        <ul class="list-disc list-inside text-xs space-y-1">
            @foreach ($errors->all() as $error)
            <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
    @endif

    <form action="{{ route('admin.doctors.update', $doctor->id) }}" method="POST" class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-2xl shadow-sm p-6 sm:p-8 space-y-6 transition-colors">
        @csrf
        @method('PUT')

        <div>
            <label class="block text-xs font-semibold uppercase tracking-wider text-gray-700 dark:text-gray-300 mb-2">FIO (Ady we Familiýasy)</label>
            <div class="relative">
                <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-gray-400">
                    <i class="ti ti-user text-lg"></i>
                </span>
                <input type="text" name="name" value="{{ old('name', $doctor->user->name ?? '') }}" required
                    class="w-full pl-10 pr-4 py-2.5 bg-gray-50 dark:bg-gray-900 border border-gray-300 dark:border-gray-700 rounded-xl text-gray-900 dark:text-gray-100 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-blue-500 transition text-sm">
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <label class="block text-xs font-semibold uppercase tracking-wider text-gray-700 dark:text-gray-300 mb-2">Email Salgysy</label>
                <div class="relative">
                    <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-gray-400">
                        <i class="ti ti-mail text-lg"></i>
                    </span>
                    <input type="email" name="email" value="{{ old('email', $doctor->user->email ?? '') }}" required
                        class="w-full pl-10 pr-4 py-2.5 bg-gray-50 dark:bg-gray-900 border border-gray-300 dark:border-gray-700 rounded-xl text-gray-900 dark:text-gray-100 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-blue-500 transition text-sm">
                </div>
            </div>
            <div>
                <label class="block text-xs font-semibold uppercase tracking-wider text-gray-700 dark:text-gray-300 mb-2">Telefon belgisi</label>
                <div class="relative">
                    <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-gray-400">
                        <i class="ti ti-phone text-lg"></i>
                    </span>
                    <input type="text" name="phone" value="{{ old('phone', $doctor->user->phone ?? '') }}"
                        class="w-full pl-10 pr-4 py-2.5 bg-gray-50 dark:bg-gray-900 border border-gray-300 dark:border-gray-700 rounded-xl text-gray-900 dark:text-gray-100 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-blue-500 transition text-sm" placeholder="+9936xxxxxxxx">
                </div>
            </div>
        </div>

        <div>
            <label class="block text-xs font-semibold uppercase tracking-wider text-gray-700 dark:text-gray-300 mb-2">Täze Parol (Eger üýtgetmezek bolsaňyz boş goýuň)</label>
            <div class="relative">
                <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-gray-400">
                    <i class="ti ti-lock text-lg"></i>
                </span>
                <input type="password" name="password"
                    class="w-full pl-10 pr-4 py-2.5 bg-gray-50 dark:bg-gray-900 border border-gray-300 dark:border-gray-700 rounded-xl text-gray-900 dark:text-gray-100 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-blue-500 transition text-sm" placeholder="Iň az 8 simwol">
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <label class="block text-xs font-semibold uppercase tracking-wider text-gray-700 dark:text-gray-300 mb-2">Hünär Ugry</label>
                <div class="relative">
                    <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-gray-400">
                        <i class="ti ti-stethoscope text-lg"></i>
                    </span>
                    <select name="specialization_id" required
                        class="w-full pl-10 pr-4 py-2.5 bg-gray-50 dark:bg-gray-900 border border-gray-300 dark:border-gray-700 rounded-xl text-gray-900 dark:text-gray-100 focus:outline-none focus:ring-2 focus:ring-blue-500 transition text-sm appearance-none">
                        @foreach($specializations as $spec)
                        <option value="{{ $spec->id }}" {{ old('specialization_id', $doctor->specialization_id) == $spec->id ? 'selected' : '' }}>
                            {{ $spec->name }}
                        </option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div>
                <label class="block text-xs font-semibold uppercase tracking-wider text-gray-700 dark:text-gray-300 mb-2">Tejribe ýyly</label>
                <div class="relative">
                    <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-gray-400">
                        <i class="ti ti-clock text-lg"></i>
                    </span>
                    <input type="number" name="experience_years" value="{{ old('experience_years', $doctor->experience_years) }}" min="0" max="70"
                        class="w-full pl-10 pr-4 py-2.5 bg-gray-50 dark:bg-gray-900 border border-gray-300 dark:border-gray-700 rounded-xl text-gray-900 dark:text-gray-100 focus:outline-none focus:ring-2 focus:ring-blue-500 transition text-sm">
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <label class="block text-xs font-semibold uppercase tracking-wider text-gray-700 dark:text-gray-300 mb-2">Maslahat bahasy (Fee)</label>
                <div class="relative">
                    <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-gray-400">
                        <i class="ti ti-cash text-lg"></i>
                    </span>
                    <input type="number" step="0.01" name="consultation_fee" value="{{ old('consultation_fee', $doctor->consultation_fee) }}" min="0"
                        class="w-full pl-10 pr-4 py-2.5 bg-gray-50 dark:bg-gray-900 border border-gray-300 dark:border-gray-700 rounded-xl text-gray-900 dark:text-gray-100 focus:outline-none focus:ring-2 focus:ring-blue-500 transition text-sm">
                </div>
            </div>
            <div>
                <label class="block text-xs font-semibold uppercase tracking-wider text-gray-700 dark:text-gray-300 mb-2">Valýuta</label>
                <div class="relative">
                    <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-gray-400">
                        <i class="ti ti-currency-dollar text-lg"></i>
                    </span>
                    <input type="text" name="currency" value="{{ old('currency', $doctor->currency) }}" maxlength="3"
                        class="w-full pl-10 pr-4 py-2.5 bg-gray-50 dark:bg-gray-900 border border-gray-300 dark:border-gray-700 rounded-xl text-gray-900 dark:text-gray-100 focus:outline-none focus:ring-2 focus:ring-blue-500 transition text-sm">
                </div>
            </div>
        </div>

        <div>
            <label class="block text-xs font-semibold uppercase tracking-wider text-gray-700 dark:text-gray-300 mb-2">Işleýän Klinikalary</label>
            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-3 border border-gray-200 dark:border-gray-700 p-4 rounded-xl bg-gray-50 dark:bg-gray-900/50">
                @php
                $selectedClinics = old('clinic_ids', $doctor->clinics->pluck('id')->toArray());
                @endphp
                @foreach($clinics as $clinic)
                <label class="flex items-center space-x-3 cursor-pointer p-2 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-800 transition">
                    <input type="checkbox" name="clinic_ids[]" value="{{ $clinic->id }}"
                        {{ in_array($clinic->id, $selectedClinics) ? 'checked' : '' }}
                        class="w-4 h-4 rounded border-gray-300 dark:border-gray-700 text-blue-600 focus:ring-blue-500 dark:bg-gray-900">
                    <span class="text-sm font-medium text-gray-700 dark:text-gray-300 flex items-center gap-1.5">
                        <i class="ti ti-building-hospital text-blue-500"></i>
                        {{ $clinic->name }}
                    </span>
                </label>
                @endforeach
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <label class="block text-xs font-semibold uppercase tracking-wider text-gray-700 dark:text-gray-300 mb-2">Bilimi (Education)</label>
                <textarea name="education" rows="3"
                    class="w-full p-3 bg-gray-50 dark:bg-gray-900 border border-gray-300 dark:border-gray-700 rounded-xl text-gray-900 dark:text-gray-100 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-blue-500 transition text-sm">{{ old('education', $doctor->education) }}</textarea>
            </div>
            <div>
                <label class="block text-xs font-semibold uppercase tracking-wider text-gray-700 dark:text-gray-300 mb-2">Biografiýa</label>
                <textarea name="biography" rows="3"
                    class="w-full p-3 bg-gray-50 dark:bg-gray-900 border border-gray-300 dark:border-gray-700 rounded-xl text-gray-900 dark:text-gray-100 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-blue-500 transition text-sm">{{ old('biography', $doctor->biography) }}</textarea>
            </div>
        </div>

        <div class="flex flex-col sm:flex-row sm:items-center gap-6 pt-2">
            <label class="flex items-center space-x-3 cursor-pointer">
                <input type="checkbox" name="is_verified" value="1" {{ old('is_verified', $doctor->is_verified) ? 'checked' : '' }}
                    class="w-4 h-4 rounded border-gray-300 dark:border-gray-700 text-blue-600 focus:ring-blue-500 dark:bg-gray-900">
                <span class="text-sm font-medium text-gray-700 dark:text-gray-300 flex items-center gap-1.5">
                    <i class="ti ti-shield-check text-emerald-500"></i>
                    Tassyklanan (Verified)
                </span>
            </label>
            <label class="flex items-center space-x-3 cursor-pointer">
                <input type="checkbox" name="is_active" value="1" {{ old('is_active', $doctor->user->is_active ?? true) ? 'checked' : '' }}
                    class="w-4 h-4 rounded border-gray-300 dark:border-gray-700 text-blue-600 focus:ring-blue-500 dark:bg-gray-900">
                <span class="text-sm font-medium text-gray-700 dark:text-gray-300 flex items-center gap-1.5">
                    <i class="ti ti-user-check text-blue-500"></i>
                    Hasap Işjeň (Active)
                </span>
            </label>
        </div>

        <div class="pt-4 border-t border-gray-200 dark:border-gray-700">
            <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-semibold py-3 px-4 rounded-xl shadow-lg shadow-blue-500/25 transition duration-200 flex items-center justify-center gap-2">
                <i class="ti ti-device-floppy text-xl"></i>
                Maglumatlary Täzelemek
            </button>
        </div>
    </form>
</div>
@endsection
