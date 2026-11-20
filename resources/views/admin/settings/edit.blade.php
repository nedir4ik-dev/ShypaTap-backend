@extends('layouts.app')
@section('title', 'Ulgam sazlamalary')
@section('header', 'Ulgam sazlamalary')
@section('content')
<div class="relative mx-auto max-w-3xl py-4">

    <div class="relative overflow-hidden rounded-4xl border border-white/60 bg-white/45 p-8 shadow-[0_20px_50px_rgba(15,23,42,0.12),inset_0_1px_1px_rgba(255,255,255,0.9)] backdrop-blur-md backdrop-saturate-150 dark:border-white/15 dark:bg-slate-900/50 dark:shadow-[0_20px_50px_rgba(0,0,0,0.45),inset_0_1px_1px_rgba(255,255,255,0.2)]">
        <div class="pointer-events-none absolute inset-x-0 top-0 h-px bg-linear-to-r from-transparent via-white to-transparent opacity-80 dark:via-white/40"></div>

        @if(session('success'))
        <div class="mb-6 flex items-center gap-3 rounded-2xl border border-emerald-300/60 bg-emerald-500/15 p-4 text-sm font-medium text-emerald-900 shadow-[inset_0_1px_0_rgba(255,255,255,0.6)] backdrop-blur-md dark:border-emerald-400/25 dark:bg-emerald-400/15 dark:text-emerald-100">
            <span class="h-2 w-2 rounded-full bg-emerald-500 shadow-[0_0_8px_rgba(14,165,233,0.8)]"></span>
            {{ session('success') }}
        </div>
        @endif

        <form method="POST" action="{{ route('admin.settings.update') }}" class="space-y-5">
            @csrf
            @method('PUT')

            <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
                @foreach([
                ['system_name','Ulgamyň ady','text', 'sm:col-span-2'],
                ['timezone','Wagt zolagy','text', ''],
                ['currency','Walýuta (3 harp)','text', ''],
                ['cancellation_hours','Ýatyrmak üçin sagat','number', ''],
                ['default_slot_duration','Başlangyç kabul dowamlylygy (minut)','number', ''],
                ['contact_email','Aragatnaşyk e-poçtasy','email', ''],
                ['contact_phone','Aragatnaşyk telefony','text', '']
                ] as [$key, $label, $type, $span])
                <label class="block {{ $span }}">
                    <span class="mb-1.5 block text-xs font-semibold tracking-wide text-slate-700/90 dark:text-slate-200/90">
                        {{ $label }}
                    </span>
                    <input
                        type="{{ $type }}"
                        name="{{ $key }}"
                        value="{{ old($key, $settings[$key] ?? '') }}"
                        class="w-full rounded-2xl border border-black/10 bg-white/50 px-4 py-3 text-slate-900 placeholder-slate-400 shadow-[inset_0_2px_4px_rgba(15,23,42,0.04),0_1px_0_rgba(255,255,255,0.7)] backdrop-blur-md transition-all duration-200 focus:border-blue-400/80 focus:bg-white/80 focus:ring-4 focus:ring-blue-500/15 focus:outline-none dark:border-white/10 dark:bg-white/5 dark:text-white dark:shadow-[inset_0_2px_4px_rgba(0,0,0,0.25),0_1px_0_rgba(255,255,255,0.08)] dark:focus:border-sky-400/50 dark:focus:bg-white/10 dark:focus:ring-sky-400/20">
                </label>
                @endforeach
            </div>

            <div class="pt-1">
                <input type="hidden" name="maintenance_mode" value="0">
                <label class="flex cursor-pointer items-center justify-between rounded-2xl border border-black/10 bg-white/40 px-4 py-3.5 shadow-[inset_0_1px_0_rgba(255,255,255,0.7)] backdrop-blur-md transition hover:bg-white/60 dark:border-white/10 dark:bg-white/5 dark:shadow-[inset_0_1px_0_rgba(255,255,255,0.1)] dark:hover:bg-white/10">
                    <span class="text-sm font-medium text-slate-800 dark:text-slate-200">
                        Ulgam hyzmat işlerinde (maintenance mode)
                    </span>
                    <input
                        type="checkbox"
                        name="maintenance_mode"
                        value="1"
                        @checked(($settings['maintenance_mode'] ?? '0' )==='1' )
                        class="h-5 w-5 rounded-full border-white/60 bg-white/70 text-blue-600 shadow-sm focus:ring-blue-500/30 dark:border-white/20 dark:bg-slate-800">
                </label>
            </div>

            <div class="flex justify-end pt-2">
                <button
                    type="submit"
                    class="inline-flex w-full sm:w-auto items-center justify-center rounded-2xl border border-white/70 bg-blue-500/15 px-6 py-3 text-sm font-semibold text-blue-800 shadow-[0_8px_24px_rgba(15,23,42,0.08),inset_0_1px_0_rgba(255,255,255,0.85)] backdrop-blur-xl transition-all duration-200 hover:border-blue-400/60 hover:bg-blue-500/25 hover:text-blue-900 focus:ring-4 focus:ring-blue-500/15 focus:outline-none active:scale-[0.98] dark:border-white/15 dark:bg-sky-400/15 dark:text-sky-200 dark:shadow-[0_8px_24px_rgba(0,0,0,0.35),inset_0_1px_0_rgba(255,255,255,0.16)] dark:hover:border-sky-400/40 dark:hover:bg-sky-400/25 dark:hover:text-white dark:focus:ring-sky-400/20">
                    Sazlamalary sakla
                </button>
            </div>
        </form>
    </div>
</div>
@endsection