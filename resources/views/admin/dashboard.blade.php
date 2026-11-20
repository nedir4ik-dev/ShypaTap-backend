@extends('layouts.app')

@section('title', 'Admin Dashboard')

@section('header', 'Dolandyryş Paneli')

@section('content')
<div class="space-y-6">

    <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 xl:grid-cols-4">

        <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-2xl p-6 shadow-sm flex items-center justify-between transition-colors">
            <div>
                <p class="text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400 mb-1">Jemi Näsaglar</p>
                <h3 class="text-3xl font-extrabold text-gray-900 dark:text-white">{{ $totalPatients ?? 0 }}</h3>
            </div>
            <div class="w-12 h-12 rounded-xl bg-blue-50 dark:bg-blue-500/10 text-blue-600 dark:text-blue-400 flex items-center justify-center text-2xl">
                <i class="ti ti-users"></i>
            </div>
        </div>

        <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-2xl p-6 shadow-sm flex items-center justify-between transition-colors">
            <div>
                <p class="text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400 mb-1">Jemi Lukmanlar</p>
                <h3 class="text-3xl font-extrabold text-gray-900 dark:text-white">{{ $totalDoctors ?? 0 }}</h3>
            </div>
            <div class="w-12 h-12 rounded-xl bg-emerald-50 dark:bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 flex items-center justify-center text-2xl">
                <i class="ti ti-stethoscope"></i>
            </div>
        </div>

        <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-2xl p-6 shadow-sm flex items-center justify-between transition-colors">
            <div>
                <p class="text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400 mb-1">Jemi Klinikalar</p>
                <h3 class="text-3xl font-extrabold text-gray-900 dark:text-white">{{ $totalClinics ?? 0 }}</h3>
            </div>
            <div class="w-12 h-12 rounded-xl bg-amber-50 dark:bg-amber-500/10 text-amber-600 dark:text-amber-400 flex items-center justify-center text-2xl">
                <i class="ti ti-building-hospital"></i>
            </div>
        </div>
        <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-2xl p-6 shadow-sm flex items-center justify-between transition-colors">
            <div>
                <p class="text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400 mb-1">Şu günki Bronlar</p>
                <h3 class="text-3xl font-extrabold text-gray-900 dark:text-white">{{ $todayAppointments ?? 0 }}</h3>
            </div>
            <div class="w-12 h-12 rounded-xl bg-purple-50 dark:bg-purple-500/10 text-purple-600 dark:text-purple-400 flex items-center justify-center text-2xl">
                <i class="ti ti-calendar-event"></i>
            </div>
        </div>

    </div>

</div>
@endsection