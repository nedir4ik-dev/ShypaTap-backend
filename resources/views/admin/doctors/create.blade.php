@extends('layouts.app')

@section('content')
<div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8 transition-colors duration-200">

    {{-- Başlyk we Yzyna gaýtmak --}}
    <div class="flex justify-between items-center mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Täze Lukman Goşmak</h1>
            <p class="text-sm text-gray-500 dark:text-gray-400">Ulgama täze hünärmen lukmanyň maglumatlaryny giriziň.</p>
        </div>
        <a href="{{ route('admin.doctors.index') }}" class="text-sm text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white font-medium">
            ← Yzyna gaýtmak
        </a>
    </div>

    {{-- Ýalňyşlyklar --}}
    @if ($errors->any())
    <div class="mb-6 bg-red-50 dark:bg-red-500/10 border border-red-200 dark:border-red-500/30 text-red-700 dark:text-red-400 p-4 rounded-xl text-sm">
        <strong class="font-bold">Ýalňyşlyk bar!</strong>
        <ul class="mt-2 list-disc list-inside space-y-1">
            @foreach ($errors->all() as $error)
            <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
    @endif

    {{-- Forma --}}
    <form action="{{ route('admin.doctors.store') }}" method="POST" class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 shadow-xl rounded-2xl p-6 sm:p-8 space-y-6 transition-colors">
        @csrf

        {{-- Ady we Familiýasy --}}
        <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">FIO (Ady we Familiýasy)</label>
            <input type="text" name="name" value="{{ old('name') }}" required placeholder="Meselem: Dr. Aman Amanow" class="w-full bg-gray-50 dark:bg-gray-900 border border-gray-300 dark:border-gray-700 rounded-lg px-4 py-2.5 text-gray-900 dark:text-gray-100 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-blue-500">
        </div>

        {{-- Email we Telefon --}}
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Email Salgysy</label>
                <input type="email" name="email" value="{{ old('email') }}" required placeholder="doctor@sypatap.test" class="w-full bg-gray-50 dark:bg-gray-900 border border-gray-300 dark:border-gray-700 rounded-lg px-4 py-2.5 text-gray-900 dark:text-gray-100 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Telefon belgisi</label>
                <input type="text" name="phone" value="{{ old('phone') }}" placeholder="+99361000000" class="w-full bg-gray-50 dark:bg-gray-900 border border-gray-300 dark:border-gray-700 rounded-lg px-4 py-2.5 text-gray-900 dark:text-gray-100 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>
        </div>

        {{-- Parol --}}
        <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Parol</label>
            <input type="password" name="password" required placeholder="Iň az 8 simwol" class="w-full bg-gray-50 dark:bg-gray-900 border border-gray-300 dark:border-gray-700 rounded-lg px-4 py-2.5 text-gray-900 dark:text-gray-100 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-blue-500">
        </div>

        {{-- Hünär ugry we Tejribe ýyly --}}
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Hünär Ugry</label>
                <select name="specialization_id" required class="w-full bg-gray-50 dark:bg-gray-900 border border-gray-300 dark:border-gray-700 rounded-lg px-4 py-2.5 text-gray-900 dark:text-gray-100 focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="">Hünär saýlaň...</option>
                    @foreach($specializations as $spec)
                    <option value="{{ $spec->id }}" {{ old('specialization_id') == $spec->id ? 'selected' : '' }}>
                        {{ $spec->name }}
                    </option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Tejribe ýyly</label>
                <input type="number" name="experience_years" value="{{ old('experience_years') }}" min="0" max="70" placeholder="5" class="w-full bg-gray-50 dark:bg-gray-900 border border-gray-300 dark:border-gray-700 rounded-lg px-4 py-2.5 text-gray-900 dark:text-gray-100 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>
        </div>

        {{-- Baha we Valýuta --}}
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Maslahat bahasy (Fee)</label>
                <input type="number" step="0.01" name="consultation_fee" value="{{ old('consultation_fee') }}" min="0" placeholder="150.00" class="w-full bg-gray-50 dark:bg-gray-900 border border-gray-300 dark:border-gray-700 rounded-lg px-4 py-2.5 text-gray-900 dark:text-gray-100 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Valýuta</label>
                <input type="text" name="currency" value="{{ old('currency', 'TMT') }}" maxlength="3" class="w-full bg-gray-50 dark:bg-gray-900 border border-gray-300 dark:border-gray-700 rounded-lg px-4 py-2.5 text-gray-900 dark:text-gray-100 focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>
        </div>

        {{-- Klinika saýlamak (Checkboxes) --}}
        <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Işleýän Klinikalary</label>
            <div class="grid grid-cols-2 sm:grid-cols-3 gap-3 border border-gray-200 dark:border-gray-700 p-4 rounded-xl bg-gray-50 dark:bg-gray-900/50">
                @foreach($clinics as $clinic)
                <label class="flex items-center space-x-3 cursor-pointer">
                    <input type="checkbox" name="clinic_ids[]" value="{{ $clinic->id }}"
                        {{ is_array(old('clinic_ids')) && in_array($clinic->id, old('clinic_ids')) ? 'checked' : '' }}
                        class="rounded border-gray-300 dark:border-gray-700 text-blue-600 focus:ring-blue-500 dark:bg-gray-900 w-4 h-4">
                    <span class="text-sm text-gray-700 dark:text-gray-300">{{ $clinic->name }}</span>
                </label>
                @endforeach
            </div>
        </div>

        {{-- Bilimi we Biografiýa --}}
        <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Bilimi (Education)</label>
            <textarea name="education" rows="2" placeholder="Hajy Ýolölenow adyndaky lukmançylyk uniwersiteti..." class="w-full bg-gray-50 dark:bg-gray-900 border border-gray-300 dark:border-gray-700 rounded-lg px-4 py-2.5 text-gray-900 dark:text-gray-100 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-blue-500">{{ old('education') }}</textarea>
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Biografiýa</label>
            <textarea name="biography" rows="3" placeholder="Lukman hakynda gysgaça maglumat..." class="w-full bg-gray-50 dark:bg-gray-900 border border-gray-300 dark:border-gray-700 rounded-lg px-4 py-2.5 text-gray-900 dark:text-gray-100 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-blue-500">{{ old('biography') }}</textarea>
        </div>

        {{-- Tassyklanan / Active Switchler --}}
        <div class="flex items-center space-x-6 pt-2">
            <label class="flex items-center space-x-3 cursor-pointer">
                <input type="checkbox" name="is_verified" value="1" {{ old('is_verified') ? 'checked' : '' }} class="rounded border-gray-300 dark:border-gray-700 text-blue-600 focus:ring-blue-500 dark:bg-gray-900 w-4 h-4">
                <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Tassyklanan (Verified)</span>
            </label>
            <label class="flex items-center space-x-3 cursor-pointer">
                <input type="checkbox" name="is_active" value="1" {{ old('is_active', true) ? 'checked' : '' }} class="rounded border-gray-300 dark:border-gray-700 text-blue-600 focus:ring-blue-500 dark:bg-gray-900 w-4 h-4">
                <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Hasap Işjeň (Active)</span>
            </label>
        </div>

        {{-- Submit Düwmesi --}}
        <div class="pt-4">
            <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-medium py-2.5 px-4 rounded-xl shadow-lg shadow-blue-600/30 transition">
                Lukmany Ýatda Saklaň
            </button>
        </div>
    </form>
</div>
@endsection