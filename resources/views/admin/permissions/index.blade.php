@extends('layouts.app')
@section('title', 'Rugsat dolandyryşy')
@section('header', 'Klinika we amal rugsatlary')
@section('content')
<div class="space-y-7">
    @if(session('success'))<div class="rounded-xl border border-emerald-200 bg-emerald-50 p-3 text-sm text-emerald-800">{{ session('success') }}</div>@endif
    <p class="text-sm text-gray-500">Her klinika üçin aýratyn rugsatlary belläň. Rugsatlaryň hiç biri saýlanmasa, ulanyjy şol klinikada degişli amaly ýerine ýetirip bilmez.</p>

    <section class="space-y-4">
        <h2 class="text-lg font-bold">Registratorlar</h2>
        @forelse($receptionists as $user)
        <article class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-700 dark:bg-gray-800">
            <h3 class="mb-4 font-semibold">{{ $user->name }} <span class="text-xs font-normal text-gray-500">{{ $user->email }}</span></h3>
            <div class="space-y-4">
                @foreach($clinics as $clinic)
                    @php($assigned = $user->staffClinics->firstWhere('id', $clinic->id))
                    @php($selected = $assigned?->pivot?->permissions ?? [])
                    <form method="POST" action="{{ route('admin.users.clinic-permissions', $user) }}" class="rounded-xl border border-gray-100 p-3 dark:border-gray-700">
                        @csrf @method('PATCH')
                        <input type="hidden" name="clinic_id" value="{{ $clinic->id }}"><input type="hidden" name="permissions[]" value="">
                        <div class="mb-2 flex items-center justify-between"><strong class="text-sm">{{ $clinic->name }}</strong><span class="text-xs text-gray-500">{{ $assigned ? 'Birikdirilen' : 'Täze birikdirme' }}</span></div>
                        <div class="grid grid-cols-2 gap-2 md:grid-cols-4">
                            @foreach($staffPermissions as $key=>$label)
                            <label class="flex items-center gap-2 text-xs"><input type="checkbox" name="permissions[]" value="{{ $key }}" @checked(in_array($key, $selected, true))> {{ $label }}</label>
                            @endforeach
                        </div>
                        <button class="mt-3 rounded-lg bg-emerald-600 px-3 py-2 text-xs font-semibold text-white">Sakla</button>
                    </form>
                @endforeach
            </div>
        </article>
        @empty<p class="rounded-xl border p-5 text-sm text-gray-500">Registrator ýok.</p>@endforelse
    </section>

    <section class="space-y-4">
        <h2 class="text-lg font-bold">Lukmanlar</h2>
        @forelse($doctors as $doctor)
        <article class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-700 dark:bg-gray-800">
            <h3 class="mb-4 font-semibold">{{ $doctor->user?->name }}</h3>
            @foreach($doctor->clinics as $clinic)
                @php($selected = $clinic->pivot->permissions ?? [])
                <form method="POST" action="{{ route('admin.doctors.clinic-permissions', $doctor) }}" class="mb-3 rounded-xl border border-gray-100 p-3 dark:border-gray-700">
                    @csrf @method('PATCH')
                    <input type="hidden" name="clinic_id" value="{{ $clinic->id }}"><input type="hidden" name="permissions[]" value="">
                    <strong class="mb-2 block text-sm">{{ $clinic->name }}</strong>
                    <div class="grid grid-cols-2 gap-2 md:grid-cols-4">
                        @foreach($doctorPermissions as $key=>$label)
                        <label class="flex items-center gap-2 text-xs"><input type="checkbox" name="permissions[]" value="{{ $key }}" @checked(in_array($key, $selected, true))> {{ $label }}</label>
                        @endforeach
                    </div>
                    <button class="mt-3 rounded-lg bg-emerald-600 px-3 py-2 text-xs font-semibold text-white">Sakla</button>
                </form>
            @endforeach
        </article>
        @empty<p class="rounded-xl border p-5 text-sm text-gray-500">Lukman ýok.</p>@endforelse
    </section>
</div>
@endsection
