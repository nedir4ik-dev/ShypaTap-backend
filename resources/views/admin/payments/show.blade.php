@extends('layouts.app')
@section('title', 'Töleg maglumatlary')
@section('header', 'Töleg maglumatlary')
@section('content')
<div class="relative mx-auto max-w-3xl space-y-6 py-4">
    @if(session('success'))
    <div class="relative flex items-center gap-3 rounded-2xl border border-emerald-300/60 bg-emerald-500/15 p-4 text-sm font-medium text-emerald-900 shadow-lg backdrop-blur-xl dark:border-emerald-400/25 dark:bg-emerald-400/15 dark:text-emerald-100">
        <span class="h-2 w-2 rounded-full bg-emerald-500 shadow-[0_0_8px_rgba(16,185,129,0.85)]"></span>
        {{ session('success') }}
    </div>
    @endif

    <div class="relative overflow-hidden rounded-4xl border border-white/60 bg-white/45 p-8 shadow-[0_20px_50px_rgba(15,23,42,0.12),inset_0_1px_1px_rgba(255,255,255,0.9)] backdrop-blur-2xl backdrop-saturate-150 dark:border-white/15 dark:bg-slate-900/50 dark:shadow-[0_20px_50px_rgba(0,0,0,0.45),inset_0_1px_1px_rgba(255,255,255,0.2)]">
        <div class="pointer-events-none absolute inset-x-0 top-0 h-px bg-linear-to-r from-transparent via-white to-transparent opacity-80 dark:via-white/40"></div>

        <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
            <div class="rounded-2xl border border-white/60 bg-white/40 px-4 py-3 shadow-[inset_0_1px_0_rgba(255,255,255,0.75)] backdrop-blur-md dark:border-white/10 dark:bg-white/5">
                <span class="block text-xs font-medium text-slate-500 dark:text-slate-400">Bron</span>
                <span class="mt-0.5 block text-sm font-bold text-slate-900 dark:text-white">{{ $payment->appointment?->booking_code ?? '—' }}</span>
            </div>

            <div class="rounded-2xl border border-white/60 bg-white/40 px-4 py-3 shadow-[inset_0_1px_0_rgba(255,255,255,0.75)] backdrop-blur-md dark:border-white/10 dark:bg-white/5">
                <span class="block text-xs font-medium text-slate-500 dark:text-slate-400">Mukdar</span>
                <span class="mt-0.5 block text-sm font-bold text-blue-700 dark:text-sky-300">{{ $payment->amount }} {{ $payment->currency }}</span>
            </div>

            <div class="rounded-2xl border border-white/60 bg-white/40 px-4 py-3 shadow-[inset_0_1px_0_rgba(255,255,255,0.75)] backdrop-blur-md dark:border-white/10 dark:bg-white/5">
                <span class="block text-xs font-medium text-slate-500 dark:text-slate-400">Näsag</span>
                <span class="mt-0.5 block text-sm font-semibold text-slate-800 dark:text-slate-100">{{ $payment->appointment?->patient?->name ?? '—' }}</span>
            </div>

            <div class="rounded-2xl border border-white/60 bg-white/40 px-4 py-3 shadow-[inset_0_1px_0_rgba(255,255,255,0.75)] backdrop-blur-md dark:border-white/10 dark:bg-white/5">
                <span class="block text-xs font-medium text-slate-500 dark:text-slate-400">Lukman</span>
                <span class="mt-0.5 block text-sm font-semibold text-slate-800 dark:text-slate-100">{{ $payment->appointment?->doctor?->user?->name ?? '—' }}</span>
            </div>

            <div class="sm:col-span-2 rounded-2xl border border-white/60 bg-white/40 px-4 py-3 shadow-[inset_0_1px_0_rgba(255,255,255,0.75)] backdrop-blur-md dark:border-white/10 dark:bg-white/5">
                <span class="block text-xs font-medium text-slate-500 dark:text-slate-400">Klinika</span>
                <span class="mt-0.5 block text-sm font-semibold text-slate-800 dark:text-slate-100">{{ $payment->appointment?->clinic?->name ?? '—' }}</span>
            </div>
        </div>

        <div class="my-6 h-px bg-linear-to-r from-transparent via-slate-400/25 to-transparent dark:via-white/15"></div>

        <form method="POST" action="{{ route('admin.payments.update', $payment) }}" class="space-y-5">
            @csrf
            @method('PUT')

            <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
                <label class="block">
                    <span class="mb-1.5 block text-xs font-semibold text-slate-700 dark:text-slate-200">Töleg ýagdaýy</span>
                    <select name="status" class="w-full rounded-2xl border border-white/60 bg-white/55 px-4 py-3 text-slate-900 shadow-inner backdrop-blur-sm focus:border-blue-400/80 focus:bg-white/85 focus:ring-4 focus:ring-blue-500/15 focus:outline-none dark:border-white/10 dark:bg-slate-900/60 dark:text-white">
                        <option value="pending" @selected($payment->status==='pending')>Garaşylýar</option>
                        <option value="paid" @selected($payment->status==='paid')>Tölenen</option>
                        <option value="refunded" @selected($payment->status==='refunded')>Yzyna gaýtarylan</option>
                        <option value="cancelled" @selected($payment->status==='cancelled')>Ýatyrylan</option>
                    </select>
                </label>

                <label class="block">
                    <span class="mb-1.5 block text-xs font-semibold text-slate-700 dark:text-slate-200">Töleg usuly</span>
                    <select name="payment_method" class="w-full rounded-2xl border border-white/60 bg-white/55 px-4 py-3 text-slate-900 shadow-inner backdrop-blur-md focus:border-blue-400/80 focus:ring-4 focus:ring-blue-500/15 focus:outline-none dark:border-white/10 dark:bg-slate-900/60 dark:text-white">
                        <option value="cash" selected>Nagt</option>
                    </select>
                </label>
            </div>

            <p class="-mt-2 text-xs text-slate-600 dark:text-slate-400">
                Häzirki wersiýada klinikada nagt töleg işleýär. Online töleg soňra integrasiýa ediler.
            </p>

            <label class="block">
                <span class="mb-1.5 block text-xs font-semibold text-slate-700 dark:text-slate-200">Bellik</span>
                <textarea name="note" rows="3" maxlength="500" class="w-full rounded-2xl border border-white/60 bg-white/50 px-4 py-3 text-slate-900 shadow-inner backdrop-blur-md focus:border-blue-400/80 focus:ring-4 focus:ring-blue-500/15 focus:outline-none dark:border-white/10 dark:bg-white/5 dark:text-white"></textarea>
            </label>

            <div class="pt-1">
                <button
                    type="submit"
                    class="inline-flex w-full sm:w-auto items-center justify-center rounded-2xl border border-white/70 bg-blue-500/15 px-6 py-3 text-sm font-semibold text-blue-800 shadow-[0_8px_24px_rgba(15,23,42,0.08),inset_0_1px_0_rgba(255,255,255,0.85)] backdrop-blur-xl transition-all duration-200 hover:border-blue-400/60 hover:bg-blue-500/25 hover:text-blue-900 focus:ring-4 focus:ring-blue-500/15 focus:outline-none active:scale-[0.98] dark:border-white/15 dark:bg-sky-400/15 dark:text-sky-200 dark:shadow-[0_8px_24px_rgba(0,0,0,0.35),inset_0_1px_0_rgba(255,255,255,0.16)] dark:hover:border-sky-400/40 dark:hover:bg-sky-400/25 dark:hover:text-white dark:focus:ring-sky-400/20">
                    Ýatda sakla
                </button>
            </div>
        </form>
    </div>

    <div class="relative overflow-hidden rounded-4xl border border-white/60 bg-white/45 p-8 shadow-[0_20px_50px_rgba(15,23,42,0.12),inset_0_1px_1px_rgba(255,255,255,0.9)] backdrop-blur-2xl backdrop-saturate-150 dark:border-white/15 dark:bg-slate-900/50 dark:shadow-[0_20px_50px_rgba(0,0,0,0.45),inset_0_1px_1px_rgba(255,255,255,0.2)]">
        <div class="pointer-events-none absolute inset-x-0 top-0 h-px bg-linear-to-r from-transparent via-white to-transparent opacity-80 dark:via-white/40"></div>

        <h2 class="text-base font-bold text-slate-900 dark:text-white">Töleg taryhy</h2>

        <div class="mt-4 space-y-3">
            @forelse($payment->histories as $history)
            <div class="rounded-2xl border border-white/60 bg-white/40 p-4 text-sm shadow-[inset_0_1px_0_rgba(255,255,255,0.75)] backdrop-blur-md dark:border-white/10 dark:bg-white/5">
                <div class="flex flex-wrap items-center justify-between gap-2">
                    <strong class="font-semibold text-slate-900 dark:text-white">
                        {{ $history->old_status ?? '—' }} → {{ $history->new_status }}
                    </strong>
                    <span class="text-xs text-slate-500 dark:text-slate-400">
                        {{ $history->actor?->name ?? 'Ulgam' }} · {{ $history->created_at->format('Y-m-d H:i') }}
                    </span>
                </div>
                @if($history->note)
                <p class="mt-1.5 text-slate-700 dark:text-slate-300">{{ $history->note }}</p>
                @endif
            </div>
            @empty
            <div class="rounded-2xl border border-white/50 bg-white/30 px-4 py-6 text-center text-sm text-slate-500 backdrop-blur-md dark:border-white/10 dark:bg-white/5 dark:text-slate-400">
                Taryh ýok.
            </div>
            @endforelse
        </div>
    </div>
</div>
@endsection