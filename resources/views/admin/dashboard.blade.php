@extends('layouts.app')

@section('title', 'Admin Dashboard')

@section('header', 'Dolandyryş Paneli')

@section('content')
<div class="space-y-6">

    {{-- Statistika Kartlary --}}
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">

        {{-- Jemi Näsaglar --}}
        <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-2xl p-6 shadow-sm flex items-center justify-between transition-colors">
            <div>
                <p class="text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400 mb-1">Jemi Näsaglar</p>
                <h3 class="text-3xl font-extrabold text-gray-900 dark:text-white">{{ $totalPatients ?? 0 }}</h3>
            </div>
            <div class="w-12 h-12 rounded-xl bg-blue-50 dark:bg-blue-500/10 text-blue-600 dark:text-blue-400 flex items-center justify-center text-2xl">
                <i class="ti ti-users"></i>
            </div>
        </div>

        {{-- Jemi Lukmanlar --}}
        <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-2xl p-6 shadow-sm flex items-center justify-between transition-colors">
            <div>
                <p class="text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400 mb-1">Jemi Lukmanlar</p>
                <h3 class="text-3xl font-extrabold text-gray-900 dark:text-white">{{ $totalDoctors ?? 0 }}</h3>
            </div>
            <div class="w-12 h-12 rounded-xl bg-emerald-50 dark:bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 flex items-center justify-center text-2xl">
                <i class="ti ti-stethoscope"></i>
            </div>
        </div>

        {{-- Jemi Klinikalar --}}
        <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-2xl p-6 shadow-sm flex items-center justify-between transition-colors">
            <div>
                <p class="text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400 mb-1">Jemi Klinikalar</p>
                <h3 class="text-3xl font-extrabold text-gray-900 dark:text-white">{{ $totalClinics ?? 0 }}</h3>
            </div>
            <div class="w-12 h-12 rounded-xl bg-amber-50 dark:bg-amber-500/10 text-amber-600 dark:text-amber-400 flex items-center justify-center text-2xl">
                <i class="ti ti-building-hospital"></i>
            </div>
        </div>

    </div>

    <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-2xl shadow-sm overflow-hidden transition-colors">
        <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
            <h5 class="font-bold text-gray-900 dark:text-white text-lg">Soňky Bron Edilmeler</h5>
        </div>
        <div class="p-6">
            @if(isset($recentAppointments) && count($recentAppointments) > 0)
            <div class="divide-y divide-gray-200 dark:divide-gray-700">
                @foreach($recentAppointments as $app)
                <div class="py-3.5 first:pt-0 last:pb-0 flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-lg bg-gray-100 dark:bg-gray-700 text-gray-500 dark:text-gray-400 flex items-center justify-center">
                            <i class="ti ti-calendar-event text-xl"></i>
                        </div>
                        <div>
                            <strong class="text-gray-900 dark:text-white font-semibold">{{ $app->booking_code }}</strong>
                            <span class="text-xs text-gray-500 dark:text-gray-400 block">Näsag ID: {{ $app->patient_id }}</span>
                        </div>
                    </div>
                    <span class="px-3 py-1 rounded-full text-xs font-semibold bg-sky-50 text-sky-700 border border-sky-200 dark:bg-sky-500/10 dark:text-sky-400 dark:border-sky-500/30">
                        {{ $app->status }}
                    </span>
                </div>
                @endforeach
            </div>
            @else
            <p class="text-gray-500 dark:text-gray-400 text-center py-6 mb-0">Häzirlikçe täze bron maglumaty ýok.</p>
            @endif
        </div>
    </div>

</div>
@endsection