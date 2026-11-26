@extends('layouts.app')
@section('title','Hasabatlar')
@section('content')
<div class="mx-auto max-w-7xl space-y-6">
    <header>
        <h1 class="text-2xl font-extrabold">Hasabatlar</h1>
        <p class="mt-1 text-sm text-slate-500">Klinika çägindäki kabul we töleg görkezijileri.</p>
    </header>
    <form method="GET" class="grid gap-3 rounded-2xl border bg-white/70 p-4 dark:border-slate-800 dark:bg-slate-900/70 sm:grid-cols-4"><label class="text-sm">Klinika<select name="clinic_id" class="mt-1 w-full rounded-xl border p-2 dark:bg-slate-950">
                <option value="">Ählisi</option>@foreach($clinics as $clinic)<option value="{{$clinic->id}}" @selected(($filters['clinic_id']??'')==$clinic->id)>{{$clinic->name}}</option>@endforeach
            </select></label><label class="text-sm">Başlangyç sene<input type="date" name="from" value="{{$filters['from']??''}}" class="mt-1 w-full rounded-xl border p-2 dark:bg-slate-950"></label><label class="text-sm">Soňky sene<input type="date" name="to" value="{{$filters['to']??''}}" class="mt-1 w-full rounded-xl border p-2 dark:bg-slate-950"></label>
        <div class="flex items-end gap-2"><button class="rounded-xl bg-emerald-600 px-4 py-2 text-sm font-semibold text-white">Süz</button><a href="{{route('admin.reports.export',request()->query())}}" class="rounded-xl border px-4 py-2 text-sm">CSV eksport</a></div>
    </form>
    <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">@foreach(['total'=>'Ähli kabul','completed'=>'Tamamlanan','cancelled'=>'Ýatyrylan','no_show'=>'Gelmediler','revenue'=>'Girdeji','pending'=>'Garaşýan töleg','refunded'=>'Yzyna gaýtarylan'] as $key=>$label)<article class="rounded-2xl border bg-white/70 p-5 dark:border-slate-800 dark:bg-slate-900/70">
            <p class="text-sm text-slate-500">{{$label}}</p>
            <p class="mt-2 text-2xl font-extrabold">{{number_format($stats[$key],2,'.',' ')}} @if(in_array($key,['revenue','pending','refunded'])) TMT @endif</p>
        </article>@endforeach</div>
    <div class="grid gap-5 lg:grid-cols-2">
        <section class="rounded-2xl border bg-white/70 p-5 dark:border-slate-800 dark:bg-slate-900/70">
            <h2 class="mb-3 font-bold">Lukman öndürijiligi</h2>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b text-left text-slate-500">
                            <th class="p-2">Lukman</th>
                            <th class="p-2">Kabul</th>
                            <th class="p-2">Tamamlanan</th>
                        </tr>
                    </thead>
                    <tbody>@forelse($doctorPerformance as $doctor)<tr class="border-b dark:border-slate-800">
                            <td class="p-2">{{$doctor->name}}</td>
                            <td class="p-2">{{$doctor->total}}</td>
                            <td class="p-2">{{$doctor->completed}}</td>
                        </tr>@empty<tr>
                            <td colspan="3" class="p-4 text-center text-slate-500">Maglumat ýok</td>
                        </tr>@endforelse</tbody>
                </table>
            </div>
        </section>
        <section class="rounded-2xl border bg-white/70 p-5 dark:border-slate-800 dark:bg-slate-900/70">
            <h2 class="mb-3 font-bold">Klinika öndürijiligi</h2>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b text-left text-slate-500">
                            <th class="p-2">Klinika</th>
                            <th class="p-2">Kabul</th>
                            <th class="p-2">Tamamlanan</th>
                        </tr>
                    </thead>
                    <tbody>@forelse($clinicPerformance as $clinic)<tr class="border-b dark:border-slate-800">
                            <td class="p-2">{{$clinic->name}}</td>
                            <td class="p-2">{{$clinic->total}}</td>
                            <td class="p-2">{{$clinic->completed}}</td>
                        </tr>@empty<tr>
                            <td colspan="3" class="p-4 text-center text-slate-500">Maglumat ýok</td>
                        </tr>@endforelse</tbody>
                </table>
            </div>
        </section>
    </div>
</div>
@endsection