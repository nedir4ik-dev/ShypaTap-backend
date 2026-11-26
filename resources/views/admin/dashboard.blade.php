@extends('layouts.app')

@section('title', 'Admin Dashboard')

@section('header', 'Dolandyryş Paneli')

@section('content')
<div class="space-y-6">
    <form method="GET" class="grid gap-3 rounded-2xl border border-gray-200 bg-white p-4 dark:border-gray-700 dark:bg-gray-800 sm:grid-cols-2 lg:grid-cols-5">
        <label class="text-xs font-semibold">Klinika<select name="clinic_id" class="mt-1 w-full rounded-xl border p-2.5 dark:bg-gray-900">
                <option value="">Ählisi</option>@foreach($clinics as $clinic)<option value="{{$clinic->id}}" @selected(($filters['clinic_id']??'')==$clinic->id)>{{$clinic->name}}</option>@endforeach
            </select></label>
        <label class="text-xs font-semibold">Lukman<select name="doctor_profile_id" class="mt-1 w-full rounded-xl border p-2.5 dark:bg-gray-900">
                <option value="">Ählisi</option>@foreach($doctors as $doctor)<option value="{{$doctor->id}}" @selected(($filters['doctor_profile_id']??'')==$doctor->id)>{{$doctor->user?->name}}</option>@endforeach
            </select></label>
        <label class="text-xs font-semibold">Aý<input type="month" name="month" value="{{$filters['month']??''}}" class="mt-1 w-full rounded-xl border p-2.5 dark:bg-gray-900"></label>
        <label class="text-xs font-semibold">Kabul ýagdaýy<select name="status" class="mt-1 w-full rounded-xl border p-2.5 dark:bg-gray-900">
                <option value="">Ählisi</option>@foreach(['pending'=>'Garaşylýar','confirmed'=>'Tassyklanan','checked_in'=>'Geldi','completed'=>'Tamamlanan','cancelled'=>'Ýatyrylan','no_show'=>'Gelmedi'] as $value=>$label)<option value="{{$value}}" @selected(($filters['status']??'')===$value)>{{$label}}</option>@endforeach
            </select></label>
        <label class="text-xs font-semibold">Töleg ýagdaýy<select name="payment_status" class="mt-1 w-full rounded-xl border p-2.5 dark:bg-gray-900">
                <option value="">Ählisi</option>@foreach(['pending'=>'Garaşylýar','paid'=>'Tölenen','refunded'=>'Yzyna gaýtarylan','cancelled'=>'Ýatyrylan'] as $value=>$label)<option value="{{$value}}" @selected(($filters['payment_status']??'')===$value)>{{$label}}</option>@endforeach
            </select></label>
        <div class="sm:col-span-2 lg:col-span-5 flex gap-2"><button class="rounded-xl bg-emerald-600 px-5 py-2 text-sm font-semibold text-white">Süzgüçleri ulan</button><a href="{{route('admin.dashboard')}}" class="rounded-xl border px-5 py-2 text-sm">Arassala</a></div>
    </form>

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

        <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 xl:grid-cols-4">
            @foreach([['Jemi ulanyjy',$totalUsers??0,'ti-users'],['Jemi kabul',$totalAppointments??0,'ti-calendar'],['Tamamlanan',$completedAppointments??0,'ti-check'],['Ýatyrylan',$cancelledAppointments??0,'ti-calendar-x'],['Garaşýan tölegler',$pendingPayments??0,'ti-wallet'],['Tölenen tölegler',$paidPayments??0,'ti-cash'],['Girdeji',$revenue??0,'ti-currency-manat']] as $metric)
            <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm dark:border-gray-700 dark:bg-gray-800">
                <p class="text-xs font-semibold uppercase tracking-wider text-gray-500">{{$metric[0]}}</p>
                <div class="mt-2 flex items-center justify-between"><strong class="text-2xl">{{$metric[1]}}</strong><i class="ti {{$metric[2]}} text-2xl text-emerald-600"></i></div>
            </div>
            @endforeach
        </div>
    </div>
    <section class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-700 dark:bg-gray-800">
        <h2 class="mb-3 font-bold">Aýlyk girdeji</h2>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b text-left text-gray-500">
                        <th class="p-2">Aý</th>
                        <th class="p-2">Girdeji</th>
                    </tr>
                </thead>
                <tbody>@forelse($revenueByMonth as $month)<tr class="border-b dark:border-gray-700">
                        <td class="p-2">{{$month->month}}</td>
                        <td class="p-2 font-semibold">{{number_format($month->total,2)}} TMT</td>
                    </tr>@empty<tr>
                        <td colspan="2" class="p-4 text-center text-gray-500">Girdeji maglumatlary ýok.</td>
                    </tr>@endforelse</tbody>
            </table>
        </div>
    </section>

</div>
@endsection