@extends('layouts.app')

@section('title', 'Lukman Profili')

@section('header', 'Lukman Maglumaty')

@section('content')
<div class="max-w-4xl mx-auto space-y-6">

    {{-- Ýokarky Başlyk we Düwmeler --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <h1 class="text-2xl font-bold text-gray-900 dark:text-white flex items-center gap-2">
            <i class="ti ti-user-shield text-blue-600 dark:text-blue-400"></i>
            {{ $doctor->user->name ?? 'N/A' }}
        </h1>
        <div class="flex items-center gap-3">
            <a href="{{ route('admin.doctors.edit', $doctor->id) }}" class="inline-flex items-center gap-2 bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-xl text-sm font-semibold shadow-sm transition">
                <i class="ti ti-edit text-lg"></i>
                Üýtgetmek
            </a>
            <a href="{{ route('admin.doctors.index') }}" class="inline-flex items-center gap-1.5 text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white text-sm font-medium transition">
                <i class="ti ti-arrow-left text-lg"></i>
                Yzyna gaýtmak
            </a>
        </div>
    </div>

    {{-- Esasy Karta --}}
    <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-2xl shadow-sm p-6 sm:p-8 space-y-6 transition-colors">

        {{-- Esasy Maglumatlar Grid --}}
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 border-b border-gray-200 dark:border-gray-700 pb-6">
            <div class="flex items-start gap-3">
                <div class="p-2.5 rounded-xl bg-gray-50 dark:bg-gray-700 text-gray-500 dark:text-gray-400">
                    <i class="ti ti-user text-xl"></i>
                </div>
                <div>
                    <span class="text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400 block">FIO (Ady we Familiýasy)</span>
                    <span class="text-base font-semibold text-gray-900 dark:text-white">{{ $doctor->user->name ?? 'N/A' }}</span>
                </div>
            </div>

            <div class="flex items-start gap-3">
                <div class="p-2.5 rounded-xl bg-gray-50 dark:bg-gray-700 text-gray-500 dark:text-gray-400">
                    <i class="ti ti-mail text-xl"></i>
                </div>
                <div>
                    <span class="text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400 block">Email Salgysy</span>
                    <span class="text-base font-medium text-gray-900 dark:text-white">{{ $doctor->user->email ?? 'N/A' }}</span>
                </div>
            </div>

            <div class="flex items-start gap-3">
                <div class="p-2.5 rounded-xl bg-gray-50 dark:bg-gray-700 text-gray-500 dark:text-gray-400">
                    <i class="ti ti-phone text-xl"></i>
                </div>
                <div>
                    <span class="text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400 block">Telefon belgisi</span>
                    <span class="text-base font-medium text-gray-900 dark:text-white">{{ $doctor->user->phone ?? 'Görkezilmedik' }}</span>
                </div>
            </div>

            <div class="flex items-start gap-3">
                <div class="p-2.5 rounded-xl bg-gray-50 dark:bg-gray-700 text-gray-500 dark:text-gray-400">
                    <i class="ti ti-stethoscope text-xl"></i>
                </div>
                <div>
                    <span class="text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400 block">Hünär Ugry</span>
                    <span class="text-base font-medium text-blue-600 dark:text-blue-400">{{ $doctor->specialization->name ?? 'Görkezilmedik' }}</span>
                </div>
            </div>
        </div>

        {{-- Professional Maglumatlar --}}
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 border-b border-gray-200 dark:border-gray-700 pb-6">
            <div class="flex items-start gap-3">
                <div class="p-2.5 rounded-xl bg-gray-50 dark:bg-gray-700 text-gray-500 dark:text-gray-400">
                    <i class="ti ti-clock text-xl"></i>
                </div>
                <div>
                    <span class="text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400 block">Tejribe ýyly</span>
                    <span class="text-base font-semibold text-gray-900 dark:text-white">{{ $doctor->experience_years }} ýyl</span>
                </div>
            </div>

            <div class="flex items-start gap-3">
                <div class="p-2.5 rounded-xl bg-gray-50 dark:bg-gray-700 text-gray-500 dark:text-gray-400">
                    <i class="ti ti-cash text-xl"></i>
                </div>
                <div>
                    <span class="text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400 block">Maslahat bahasy</span>
                    <span class="text-base font-semibold text-gray-900 dark:text-white">{{ $doctor->consultation_fee }} {{ $doctor->currency }}</span>
                </div>
            </div>

            <div class="flex items-start gap-3">
                <div class="p-2.5 rounded-xl bg-gray-50 dark:bg-gray-700 text-gray-500 dark:text-gray-400">
                    <i class="ti ti-shield-check text-xl"></i>
                </div>
                <div>
                    <span class="text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400 block">Tassyklama Ýagdaýy</span>
                    <span class="inline-flex items-center gap-1.5 mt-1 px-3 py-1 rounded-full text-xs font-semibold {{ $doctor->is_verified ? 'bg-emerald-50 text-emerald-700 border border-emerald-200 dark:bg-emerald-500/10 dark:text-emerald-400 dark:border-emerald-500/30' : 'bg-amber-50 text-amber-700 border border-amber-200 dark:bg-amber-500/10 dark:text-amber-400 dark:border-amber-500/30' }}">
                        <i class="ti {{ $doctor->is_verified ? 'ti-check' : 'ti-clock' }}"></i>
                        {{ $doctor->is_verified ? 'Tassyklanan' : 'Garaşylýär' }}
                    </span>
                </div>
            </div>
        </div>

        {{-- Klinikalary --}}
        <div class="border-b border-gray-200 dark:border-gray-700 pb-6">
            <span class="text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400 block mb-3">Işleýän Klinikalary</span>
            <div class="flex flex-wrap gap-2">
                @forelse($doctor->clinics as $clinic)
                <span class="inline-flex items-center gap-1.5 bg-gray-50 dark:bg-gray-700/50 text-gray-800 dark:text-gray-200 text-sm px-3.5 py-1.5 rounded-xl border border-gray-200 dark:border-gray-700 font-medium">
                    <i class="ti ti-building-hospital text-blue-500"></i>
                    {{ $clinic->name }} ({{ $clinic->city }})
                </span>
                @empty
                <span class="text-gray-500 dark:text-gray-400 text-sm italic">Hiç bir klinika birikdirilmedik.</span>
                @endforelse
            </div>
        </div>

        {{-- Bilimi we Biografiýa --}}
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <span class="text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400 block mb-2">Bilimi (Education)</span>
                <div class="text-gray-700 dark:text-gray-300 bg-gray-50 dark:bg-gray-900/50 border border-gray-200 dark:border-gray-700 p-4 rounded-xl text-sm leading-relaxed">
                    {{ $doctor->education ?? 'Maglumat ýok.' }}
                </div>
            </div>
            <div>
                <span class="text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400 block mb-2">Biografiýa</span>
                <div class="text-gray-700 dark:text-gray-300 bg-gray-50 dark:bg-gray-900/50 border border-gray-200 dark:border-gray-700 p-4 rounded-xl text-sm leading-relaxed">
                    {{ $doctor->biography ?? 'Maglumat ýok.' }}
                </div>
            </div>
        </div>

    </div>
</div>
@endsection