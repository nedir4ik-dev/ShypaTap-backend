@extends('layouts.app')

@section('title', 'Hünär Maglumatlaryny Üýtgetmek')

@section('header', 'Hünärler Dolandyryşy')

@section('content')
<div class="max-w-3xl mx-auto space-y-6">

    {{-- Başlyk we Yzyna gaýtmak --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <h1 class="text-2xl font-bold text-gray-900 dark:text-white flex items-center gap-2">
            <i class="ti ti-award text-blue-600 dark:text-blue-400"></i>
            Hünary Üýtgetmek: <span class="text-blue-600 dark:text-blue-400 font-semibold">{{ $specialization->name }}</span>
        </h1>
        <a href="{{ route('admin.specializations.index') }}" class="inline-flex items-center gap-1.5 text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white text-sm font-medium transition">
            <i class="ti ti-arrow-left text-lg"></i>
            Yzyna gaýtmak
        </a>
    </div>

    {{-- Ýalňyşlyk Habarlary --}}
    @if ($errors->any())
    <div class="bg-red-50 dark:bg-red-500/10 border border-red-200 dark:border-red-500/30 text-red-700 dark:text-red-400 p-4 rounded-2xl space-y-2">
        <div class="flex items-center gap-2 font-semibold text-sm">
            <i class="ti ti-alert-circle text-lg"></i>
            <span>Ýalňyşlyk bar! Maglumatlary barlap täzeden ediň.</span>
        </div>
        <ul class="list-disc list-inside text-xs space-y-1">
            @foreach ($errors->all() as $error)
            <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
    @endif

    {{-- Form Karta --}}
    <form action="{{ route('admin.specializations.update', $specialization->id) }}" method="POST" class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-2xl shadow-sm p-6 sm:p-8 space-y-6 transition-colors">
        @csrf
        @method('PUT')

        {{-- Hünär Ady --}}
        <div>
            <label class="block text-xs font-semibold uppercase tracking-wider text-gray-700 dark:text-gray-300 mb-2">Hünär Ady</label>
            <div class="relative">
                <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-gray-400">
                    <i class="ti ti-stethoscope text-lg"></i>
                </span>
                <input type="text" name="name" value="{{ old('name', $specialization->name) }}" required
                    class="w-full pl-10 pr-4 py-2.5 bg-gray-50 dark:bg-gray-900 border border-gray-300 dark:border-gray-700 rounded-xl text-gray-900 dark:text-gray-100 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-blue-500 transition text-sm">
            </div>
        </div>

        {{-- Düşündiriş --}}
        <div>
            <label class="block text-xs font-semibold uppercase tracking-wider text-gray-700 dark:text-gray-300 mb-2">Düşündiriş (Description)</label>
            <textarea name="description" rows="4"
                class="w-full p-3 bg-gray-50 dark:bg-gray-900 border border-gray-300 dark:border-gray-700 rounded-xl text-gray-900 dark:text-gray-100 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-blue-500 transition text-sm">{{ old('description', $specialization->description) }}</textarea>
        </div>

        {{-- Submit Düwmesi --}}
        <div class="pt-4 border-t border-gray-200 dark:border-gray-700 flex items-center justify-between gap-4">
            <a href="{{ route('admin.specializations.index') }}" class="px-5 py-2.5 border border-gray-300 dark:border-gray-700 text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-xl text-sm font-semibold transition">
                Yza gaýtmak
            </a>
            <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2.5 px-6 rounded-xl shadow-lg shadow-blue-500/25 transition duration-200 flex items-center gap-2 text-sm">
                <i class="ti ti-device-floppy text-lg"></i>
                Üýtgeşmeleri Ýatda Sakla
            </button>
        </div>
    </form>
</div>
@endsection