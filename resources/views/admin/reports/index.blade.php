@extends('layouts.app')
@section('title', 'Hasabatlar')
@section('header', 'Giňişleýin hasabatlar')
@section('content')
<div class="space-y-6">
    <form method="GET" action="{{ route('admin.reports.index') }}" class="grid grid-cols-1 gap-4 rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-700 dark:bg-gray-800 md:grid-cols-5">
        <div><label class="mb-1 block text-sm">Klinika</label><select name="clinic_id" class="w-full rounded-lg border p-2 dark:bg-gray-900"><option value="">Ähli klinikalar</option>@foreach($clinics as $clinic)<option value="{{ $clinic->id }}" @selected(($filters['clinic_id'] ?? '') == $clinic->id)>{{ $clinic->name }}</option>@endforeach</select></div>
        <div><label class="mb-1 block text-sm">Lukman</label><select name="doctor_profile_id" class="w-full rounded-lg border p-2 dark:bg-gray-900"><option value="">Ähli lukmanlar</option>@foreach($doctors as $doctor)<option value="{{ $doctor->id }}" @selected(($filters['doctor_profile_id'] ?? '') == $doctor->id)>{{ $doctor->user?->name }}</option>@endforeach</select></div>
        <div><label class="mb-1 block text-sm">Aý</label><input type="month" name="month" value="{{ $filters['month'] ?? '' }}" class="w-full rounded-lg border p-2 dark:bg-gray-900"></div>
        <div><label class="mb-1 block text-sm">Töleg ýagdaýy</label><select name="payment_status" class="w-full rounded-lg border p-2 dark:bg-gray-900"><option value="">Ählisi</option>@foreach(['pending'=>'Garaşylýar','paid'=>'Tölenen','refunded'=>'Yzyna gaýtarylan','cancelled'=>'Ýatyrylan'] as $key=>$label)<option value="{{ $key }}" @selected(($filters['payment_status'] ?? '') === $key)>{{ $label }}</option>@endforeach</select></div>
        <div class="flex items-end gap-2"><button class="rounded-lg bg-emerald-600 px-4 py-2 font-semibold text-white">Filtrle</button><a href="{{ route('admin.reports.index') }}" class="rounded-lg border px-4 py-2">Arassala</a></div>
    </form>
    <div class="grid grid-cols-2 gap-3 md:grid-cols-5">
        @foreach(['Jemi töleg'=>$total,'Garaşylýar'=>$pending,'Tölenen'=>$paid,'Refund'=>$refunded] as $label=>$value)
        <div class="rounded-2xl border bg-white p-4 dark:border-gray-700 dark:bg-gray-800"><p class="text-xs text-gray-500">{{ $label }}</p><p class="mt-1 text-2xl font-bold">{{ $value }}</p></div>
        @endforeach
    </div>
    <div class="rounded-2xl border bg-white p-4 dark:border-gray-700 dark:bg-gray-800"><p class="text-xs text-gray-500">Tölenen girdeji (walýuta boýunça)</p><div class="mt-1 flex flex-wrap gap-3">@forelse($revenueByCurrency as $currency)<span class="text-lg font-bold">{{ number_format((float)$currency->total, 2) }} {{ $currency->currency }}</span>@empty<span class="text-sm text-gray-500">Töleg ýok</span>@endforelse</div></div>
    <div class="flex justify-end"><a href="{{ route('admin.reports.export', request()->query()) }}" class="rounded-lg bg-slate-800 px-4 py-2 text-sm font-semibold text-white">CSV eksport</a></div>
    <div class="overflow-x-auto rounded-2xl border bg-white dark:border-gray-700 dark:bg-gray-800">
        <table class="w-full text-left text-sm"><thead><tr class="border-b dark:border-gray-700"><th class="p-3">Bron</th><th class="p-3">Sene / wagt</th><th class="p-3">Näsag</th><th class="p-3">Lukman</th><th class="p-3">Klinika</th><th class="p-3">Bron</th><th class="p-3">Töleg</th><th class="p-3">Mukdar</th></tr></thead><tbody>
        @forelse($appointments as $a)<tr class="border-t dark:border-gray-700"><td class="p-3">{{ $a->booking_code }}</td><td class="p-3">{{ $a->appointment_date?->format('d.m.Y') }} {{ substr($a->start_time,0,5) }}</td><td class="p-3">{{ $a->patient?->name }}</td><td class="p-3">{{ $a->doctor?->user?->name }}</td><td class="p-3">{{ $a->clinic?->name }}</td><td class="p-3">{{ $a->status }}</td><td class="p-3">{{ $a->payment?->status ?? '—' }}</td><td class="p-3">{{ $a->payment?->amount ?? '—' }} {{ $a->payment?->currency }}</td></tr>
        @empty<tr><td colspan="8" class="p-8 text-center text-gray-500">Maglumat tapylmady.</td></tr>@endforelse
        </tbody></table>
    </div>
    {{ $appointments->links() }}
</div>
@endsection
