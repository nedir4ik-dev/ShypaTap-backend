@extends('layouts.app')

@section('title', 'Admin Dashboard')

@section('header', 'Dolandyryş Paneli')

@section('content')
<div class="space-y-6">
    <form method="GET" class="grid grid-cols-1 gap-3 rounded-2xl border border-gray-200 bg-white p-4 dark:border-gray-700 dark:bg-gray-800 sm:grid-cols-2 xl:grid-cols-5">
        <label class="min-w-0 text-xs font-semibold text-gray-700 dark:text-gray-200">Klinika
            <select name="clinic_id" class="mt-1 w-full rounded-xl border border-gray-300 bg-white p-2.5 text-sm dark:border-gray-600 dark:bg-gray-900">
                <option value="">Ählisi</option>@foreach ($clinics as $clinic)<option value="{{ $clinic->id }}" @selected(($filters['clinic_id'] ?? '' )==$clinic->id)>{{ $clinic->name }}</option>@endforeach
            </select>
        </label>
        <label class="min-w-0 text-xs font-semibold text-gray-700 dark:text-gray-200">Lukman
            <select name="doctor_profile_id" class="mt-1 w-full rounded-xl border border-gray-300 bg-white p-2.5 text-sm dark:border-gray-600 dark:bg-gray-900">
                <option value="">Ählisi</option>@foreach ($doctors as $doctor)<option value="{{ $doctor->id }}" @selected(($filters['doctor_profile_id'] ?? '' )==$doctor->id)>{{ $doctor->user?->name }}</option>@endforeach
            </select>
        </label>
        <label class="min-w-0 text-xs font-semibold text-gray-700 dark:text-gray-200">Aý<input type="month" name="month" value="{{ $filters['month'] ?? '' }}" class="mt-1 w-full rounded-xl border border-gray-300 bg-white p-2.5 text-sm dark:border-gray-600 dark:bg-gray-900"></label>
        <label class="min-w-0 text-xs font-semibold text-gray-700 dark:text-gray-200">Kabul ýagdaýy
            <select name="status" class="mt-1 w-full rounded-xl border border-gray-300 bg-white p-2.5 text-sm dark:border-gray-600 dark:bg-gray-900">
                <option value="">Ählisi</option>@foreach (['pending' => 'Garaşylýar', 'confirmed' => 'Tassyklanan', 'checked_in' => 'Geldi', 'completed' => 'Tamamlanan', 'cancelled' => 'Ýatyrylan', 'no_show' => 'Gelmedi'] as $value => $label)<option value="{{ $value }}" @selected(($filters['status'] ?? '' )===$value)>{{ $label }}</option>@endforeach
            </select>
        </label>
        <label class="min-w-0 text-xs font-semibold text-gray-700 dark:text-gray-200">Töleg ýagdaýy
            <select name="payment_status" class="mt-1 w-full rounded-xl border border-gray-300 bg-white p-2.5 text-sm dark:border-gray-600 dark:bg-gray-900">
                <option value="">Ählisi</option>@foreach (['pending' => 'Garaşylýar', 'paid' => 'Tölenen', 'refunded' => 'Yzyna gaýtarylan', 'cancelled' => 'Ýatyrylan'] as $value => $label)<option value="{{ $value }}" @selected(($filters['payment_status'] ?? '' )===$value)>{{ $label }}</option>@endforeach
            </select>
        </label>
        <div class="flex flex-col gap-2 sm:col-span-2 sm:flex-row xl:col-span-5"><button class="rounded-xl bg-emerald-600 px-5 py-2 text-sm font-semibold text-white transition hover:bg-emerald-700">Süzgüçleri ulan</button><a href="{{ route('admin.dashboard') }}" class="rounded-xl border border-gray-300 px-5 py-2 text-center text-sm font-semibold text-gray-700 transition hover:bg-gray-50 dark:border-gray-600 dark:text-gray-200 dark:hover:bg-gray-700">Arassala</a></div>
    </form>

    @php
    $metrics = [
    ['Jemi Näsaglar', $totalPatients, 'ti-users', 'bg-blue-50 text-blue-600 dark:bg-blue-500/10 dark:text-blue-400'],
    ['Jemi Lukmanlar', $totalDoctors, 'ti-stethoscope', 'bg-emerald-50 text-emerald-600 dark:bg-emerald-500/10 dark:text-emerald-400'],
    ['Jemi Klinikalar', $totalClinics, 'ti-building-hospital', 'bg-amber-50 text-amber-600 dark:bg-amber-500/10 dark:text-amber-400'],
    ['Şu günki Bronlar', $todayAppointments, 'ti-calendar-event', 'bg-purple-50 text-purple-600 dark:bg-purple-500/10 dark:text-purple-400'],
    ['Jemi ulanyjy', $totalUsers, 'ti-users', 'bg-sky-50 text-sky-600 dark:bg-sky-500/10 dark:text-sky-400'],
    ['Jemi kabul', $totalAppointments, 'ti-calendar', 'bg-cyan-50 text-cyan-600 dark:bg-cyan-500/10 dark:text-cyan-400'],
    ['Tamamlanan', $completedAppointments, 'ti-check', 'bg-green-50 text-green-600 dark:bg-green-500/10 dark:text-green-400'],
    ['Ýatyrylan', $cancelledAppointments, 'ti-calendar-x', 'bg-red-50 text-red-600 dark:bg-red-500/10 dark:text-red-400'],
    ['Garaşýan tölegler', $pendingPayments, 'ti-wallet', 'bg-orange-50 text-orange-600 dark:bg-orange-500/10 dark:text-orange-400'],
    ['Tölenen tölegler', $paidPayments, 'ti-cash', 'bg-teal-50 text-teal-600 dark:bg-teal-500/10 dark:text-teal-400'],
    ['Girdeji', number_format((float) $revenue, 2).' TMT', 'ti-currency-manat', 'bg-indigo-50 text-indigo-600 dark:bg-indigo-500/10 dark:text-indigo-400'],
    ];
    @endphp

    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
        @foreach ($metrics as [$label, $value, $icon, $iconColors])
        <article class="flex min-w-0 items-center justify-between gap-4 rounded-2xl border border-gray-200 bg-white p-5 shadow-sm transition-colors dark:border-gray-700 dark:bg-gray-800 sm:p-6">
            <div class="min-w-0">
                <p class="truncate text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">{{ $label }}</p>
                <p class="mt-1 truncate text-2xl font-extrabold text-gray-900 dark:text-white sm:text-3xl">{{ $value }}</p>
            </div>
            <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl text-2xl {{ $iconColors }}"><i class="ti {{ $icon }}"></i></div>
        </article>
        @endforeach
    </div>

    <section class="rounded-2xl border border-gray-200 bg-white p-4 dark:border-gray-700 dark:bg-gray-800 sm:p-5">
        <h2 class="mb-3 text-lg font-bold text-gray-900 dark:text-white">Aýlyk girdeji</h2>
        <div class="overflow-x-auto">
            <table class="min-w-120 w-full text-sm">
                <thead>
                    <tr class="border-b text-left text-gray-500 dark:border-gray-700 dark:text-gray-400">
                        <th scope="col" class="p-2">Aý</th>
                        <th scope="col" class="p-2">Girdeji</th>
                    </tr>
                </thead>
                <tbody>@forelse ($revenueByMonth as $month)<tr class="border-b dark:border-gray-700">
                        <td class="p-2 text-gray-700 dark:text-gray-200">{{ $month->month }}</td>
                        <td class="p-2 font-semibold text-gray-900 dark:text-white">{{ number_format((float) $month->total, 2) }} TMT</td>
                    </tr>@empty<tr>
                        <td colspan="2" class="p-4 text-center text-gray-500">Girdeji maglumatlary ýok.</td>
                    </tr>@endforelse</tbody>
            </table>
        </div>
    </section>

</div>
@endsection