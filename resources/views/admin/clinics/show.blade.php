@extends('layouts.app')

@section('title', 'Klinika Maglumaty')

@section('header', 'Klinika Dolandyryşy')

@section('content')
<div class="max-w-3xl mx-auto space-y-6">

    {{-- Başlyk we Yzyna gaýtmak / Üýtgetmek Düwmeleri --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <h1 class="text-2xl font-bold text-gray-900 dark:text-white flex items-center gap-2">
            <i class="ti ti-building-hospital text-blue-600 dark:text-blue-400"></i>
            {{ $clinic->name }}
        </h1>
        <div class="flex items-center gap-3">
            <a href="{{ route('admin.clinics.edit', $clinic->id) }}" class="inline-flex items-center gap-2 bg-amber-600 hover:bg-amber-700 text-white px-4 py-2 rounded-xl text-sm font-semibold shadow-sm transition">
                <i class="ti ti-edit text-lg"></i>
                Üýtgetmek
            </a>
            <a href="{{ route('admin.clinics.index') }}" class="inline-flex items-center gap-1.5 text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white text-sm font-medium transition">
                <i class="ti ti-arrow-left text-lg"></i>
                Sanawa gaýtmak
            </a>
        </div>
    </div>

    {{-- Maglumat Kartasy --}}
    <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-2xl shadow-sm overflow-hidden transition-colors p-6 sm:p-8">

        <div class="divide-y divide-gray-200 dark:divide-gray-700">

            {{-- ID --}}
            <div class="py-4 first:pt-0 last:pb-0 flex items-center justify-between">
                <span class="text-sm font-semibold text-gray-500 dark:text-gray-400 flex items-center gap-2">
                    <i class="ti ti-hash text-lg"></i> ID:
                </span>
                <span class="text-base font-bold text-gray-900 dark:text-white">#{{ $clinic->id }}</span>
            </div>

            {{-- Email --}}
            <div class="py-4 first:pt-0 last:pb-0 flex items-center justify-between">
                <span class="text-sm font-semibold text-gray-500 dark:text-gray-400 flex items-center gap-2">
                    <i class="ti ti-mail text-lg"></i> Email:
                </span>
                <span class="text-base font-medium text-gray-900 dark:text-white">{{ $clinic->email }}</span>
            </div>

            {{-- Telefon --}}
            <div class="py-4 first:pt-0 last:pb-0 flex items-center justify-between">
                <span class="text-sm font-semibold text-gray-500 dark:text-gray-400 flex items-center gap-2">
                    <i class="ti ti-phone text-lg"></i> Telefon:
                </span>
                <span class="text-base font-medium text-gray-900 dark:text-white">{{ $clinic->phone }}</span>
            </div>

            {{-- Şäher --}}
            <div class="py-4 first:pt-0 last:pb-0 flex items-center justify-between">
                <span class="text-sm font-semibold text-gray-500 dark:text-gray-400 flex items-center gap-2">
                    <i class="ti ti-map-pin text-lg"></i> Şäher:
                </span>
                <span class="text-base font-medium text-gray-900 dark:text-white">{{ $clinic->city }}</span>
            </div>

            {{-- Salgy --}}
            <div class="py-4 first:pt-0 last:pb-0 flex items-center justify-between">
                <span class="text-sm font-semibold text-gray-500 dark:text-gray-400 flex items-center gap-2">
                    <i class="ti ti-map text-lg"></i> Salgy:
                </span>
                <span class="text-base font-medium text-gray-900 dark:text-white text-right max-w-xs">{{ $clinic->address }}</span>
            </div>

            {{-- Ýagdaýy --}}
            <div class="py-4 first:pt-0 last:pb-0 flex items-center justify-between">
                <span class="text-sm font-semibold text-gray-500 dark:text-gray-400 flex items-center gap-2">
                    <i class="ti ti-activity text-lg"></i> Ýagdaýy:
                </span>
                <span>
                    @if($clinic->is_active)
                    <span class="inline-flex items-center gap-1 px-3 py-1 rounded-full text-xs font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200 dark:bg-emerald-500/10 dark:text-emerald-400 dark:border-emerald-500/30">
                        <i class="ti ti-check text-xs"></i> Active
                    </span>
                    @else
                    <span class="inline-flex items-center gap-1 px-3 py-1 rounded-full text-xs font-semibold bg-gray-100 text-gray-600 border border-gray-200 dark:bg-gray-700 dark:text-gray-400 dark:border-gray-600">
                        <i class="ti ti-minus text-xs"></i> Ýapyk
                    </span>
                    @endif
                </span>
            </div>

        </div>
    </div>
</div>
@endsection