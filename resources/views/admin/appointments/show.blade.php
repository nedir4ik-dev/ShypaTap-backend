@extends('layouts.app')

@section('title', 'Bron Maglumaty')

@section('header', 'Bron Dolandyryşy')

@section('content')
<div class="max-w-4xl mx-auto space-y-6">

    @if (session('success'))
    <div class="bg-emerald-50 dark:bg-emerald-500/10 border border-emerald-200 dark:border-emerald-500/30 text-emerald-800 dark:text-emerald-400 px-4 py-3 rounded-2xl text-sm flex items-center gap-2">
        <i class="ti ti-circle-check text-lg"></i> {{ session('success') }}
    </div>
    @endif

    @if (session('error'))
    <div class="bg-red-50 dark:bg-red-500/10 border border-red-200 dark:border-red-500/30 text-red-800 dark:text-red-400 px-4 py-3 rounded-2xl text-sm flex items-center gap-2">
        <i class="ti ti-alert-circle text-lg"></i> {{ session('error') }}
    </div>
    @endif

    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <h1 class="text-2xl font-bold text-gray-900 dark:text-white flex items-center gap-2">
            <i class="ti ti-calendar-event text-blue-600 dark:text-blue-400"></i>
            Bron: {{ $appointment->booking_code ?? 'ST-'.$appointment->id }}
        </h1>
        <div class="flex items-center gap-3">
            <a href="{{ route('admin.appointments.index') }}" class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 text-sm hover:bg-gray-100 dark:hover:bg-gray-700 transition">
                <i class="ti ti-list text-lg"></i>
                Ähli Bronlar
            </a>
        </div>
    </div>

    <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-2xl shadow-sm p-6 sm:p-8 space-y-6 transition-colors">

        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-4 bg-gray-50 dark:bg-gray-900/50 border border-gray-200 dark:border-gray-700 p-4 rounded-xl">
            <div>
                <span class="text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400 block">Bron Kody</span>
                <span class="text-base font-bold text-blue-600 dark:text-blue-400">{{ $appointment->booking_code ?? 'ST-'.$appointment->id }}</span>
            </div>
            <div>
                <span class="text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400 block">Ýagdaýy (Status)</span>
                @php
                    $statusColors = match($appointment->status) {
                        'completed' => 'bg-emerald-50 text-emerald-700 border-emerald-200 dark:bg-emerald-500/10 dark:text-emerald-400 dark:border-emerald-500/30',
                        'confirmed' => 'bg-blue-50 text-blue-700 border-blue-200 dark:bg-blue-500/10 dark:text-blue-400 dark:border-blue-500/30',
                        'pending' => 'bg-amber-50 text-amber-700 border-amber-200 dark:bg-amber-500/10 dark:text-amber-400 dark:border-amber-500/30',
                        'cancelled', 'rejected', 'no_show' => 'bg-red-50 text-red-700 border-red-200 dark:bg-red-500/10 dark:text-red-400 dark:border-red-500/30',
                        default => 'bg-gray-100 text-gray-700 border-gray-200 dark:bg-gray-700 dark:text-gray-300 dark:border-gray-600',
                    };
                @endphp
                <span class="inline-flex items-center mt-1 px-3 py-0.5 rounded-full text-xs font-semibold border uppercase {{ $statusColors }}">
                    {{ $appointment->status }}
                </span>
            </div>
            <div>
                <span class="text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400 block">Kabul Senesi</span>
                <span class="text-base font-semibold text-gray-900 dark:text-white">
                    {{ $appointment->appointment_date ? $appointment->appointment_date->format('d.m.Y') : 'Görkezilmedik' }}
                </span>
            </div>
            <div>
                <span class="text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400 block">Kabul Wagty</span>
                <span class="text-base font-semibold text-gray-900 dark:text-white">
                    {{ \Illuminate\Support\Str::substr($appointment->start_time, 0, 5) }} - {{ \Illuminate\Support\Str::substr($appointment->end_time, 0, 5) }}
                    @if($appointment->duration_minutes)
                        <span class="text-xs font-normal text-gray-500">({{ $appointment->duration_minutes }} min)</span>
                    @endif
                </span>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 border-b border-gray-200 dark:border-gray-700 pb-6">
            <div class="p-4 rounded-xl border border-gray-200 dark:border-gray-700 bg-gray-50/50 dark:bg-gray-900/30 space-y-3">
                <div class="flex items-center gap-2 text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">
                    <i class="ti ti-user text-base text-blue-600 dark:text-blue-400"></i>
                    Kim Bron Etdi (Näsag)
                </div>
                <div>
                    <p class="text-base font-bold text-gray-900 dark:text-white">
                        {{ $appointment->patient->name ?? 'Näbelli Näsag (ID: '.$appointment->patient_id.')' }}
                    </p>
                    <div class="mt-2 space-y-1 text-xs text-gray-600 dark:text-gray-400">
                        <p class="flex items-center gap-1.5">
                            <i class="ti ti-phone text-sm"></i> Telefon: <span class="font-medium text-gray-900 dark:text-gray-200">{{ $appointment->patient->phone ?? 'Görkezilmedik' }}</span>
                        </p>
                        <p class="flex items-center gap-1.5">
                            <i class="ti ti-mail text-sm"></i> Email: <span class="font-medium text-gray-900 dark:text-gray-200">{{ $appointment->patient->email ?? 'Görkezilmedik' }}</span>
                        </p>
                        @if($appointment->createdBy && $appointment->created_by_user_id !== $appointment->patient_id)
                        <p class="flex items-center gap-1.5 text-amber-600 dark:text-amber-400 pt-1">
                            <i class="ti ti-user-plus text-sm"></i> Ýazan: {{ $appointment->createdBy->name }} ({{ $appointment->createdBy->role }})
                        </p>
                        @endif
                    </div>
                </div>
            </div>

            <div class="p-4 rounded-xl border border-gray-200 dark:border-gray-700 bg-gray-50/50 dark:bg-gray-900/30 space-y-3">
                <div class="flex items-center gap-2 text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">
                    <i class="ti ti-stethoscope text-base text-emerald-600 dark:text-emerald-400"></i>
                    Haýsy Lukmana we Klinika
                </div>
                <div>
                    <p class="text-base font-bold text-gray-900 dark:text-white">
                        {{ $appointment->doctor->user->name ?? 'Näbelli Lukman' }}
                    </p>
                    <p class="text-xs font-medium text-blue-600 dark:text-blue-400 mt-0.5">
                        {{ $appointment->doctor->specialization->name ?? 'Hünär görkezilmedik' }}
                    </p>
                    <div class="mt-2 space-y-1 text-xs text-gray-600 dark:text-gray-400">
                        <p class="flex items-center gap-1.5">
                            <i class="ti ti-building-hospital text-sm"></i> Klinika:
                            <span class="font-medium text-gray-900 dark:text-gray-200">
                                {{ $appointment->clinic->name ?? 'Görkezilmedik' }}
                                @if($appointment->clinic?->city) ({{ $appointment->clinic->city }}) @endif
                            </span>
                        </p>
                        @if($appointment->clinic?->address)
                        <p class="flex items-center gap-1.5">
                            <i class="ti ti-map-pin text-sm"></i> Salgy: <span class="font-medium text-gray-900 dark:text-gray-200">{{ $appointment->clinic->address }}</span>
                        </p>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <div class="space-y-2">
            <span class="text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400 flex items-center gap-1.5">
                <i class="ti ti-notes text-base text-emerald-600 dark:text-emerald-400"></i>
                Näsagyň Şikaýaty / Ýüzlenme Sebäbi
            </span>
            <div class="p-4 bg-emerald-50/70 dark:bg-emerald-900/20 border border-emerald-200 dark:border-emerald-800/40 rounded-xl text-sm text-gray-800 dark:text-gray-200 leading-relaxed">
                {{ $appointment->reason ?: 'Näsag tarapyndan şikaýat ýa-da goşmaça bellik ýazylmady.' }}
            </div>
        </div>

        @if($appointment->cancellation_reason)
        <div class="space-y-2">
            <span class="text-xs font-semibold uppercase tracking-wider text-red-500 dark:text-red-400 flex items-center gap-1.5">
                <i class="ti ti-ban text-base"></i>
                Ýatyrylmagynyň Sebäbi
            </span>
            <div class="p-4 bg-red-50 dark:bg-red-500/10 border border-red-200 dark:border-red-500/30 rounded-xl text-sm text-red-800 dark:text-red-300">
                {{ $appointment->cancellation_reason }}
            </div>
        </div>
        @endif

        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 pt-2 border-t border-gray-200 dark:border-gray-700 text-xs text-gray-500 dark:text-gray-400">
            <div>
                <span class="block font-semibold">Döredilen wagty:</span>
                <span>{{ $appointment->created_at ? $appointment->created_at->format('d.m.Y H:i') : '-' }}</span>
            </div>
            @if($appointment->confirmed_at)
            <div>
                <span class="block font-semibold text-blue-600 dark:text-blue-400">Tassyklanan wagty:</span>
                <span>{{ $appointment->confirmed_at->format('d.m.Y H:i') }}</span>
            </div>
            @endif
            @if($appointment->completed_at)
            <div>
                <span class="block font-semibold text-emerald-600 dark:text-emerald-400">Tamamlanan wagty:</span>
                <span>{{ $appointment->completed_at->format('d.m.Y H:i') }}</span>
            </div>
            @endif
            @if($appointment->cancelled_at)
            <div>
                <span class="block font-semibold text-red-600 dark:text-red-400">Ýatyrylan wagty:</span>
                <span>{{ $appointment->cancelled_at->format('d.m.Y H:i') }}</span>
            </div>
            @endif
        </div>

    </div>
</div>
@endsection
