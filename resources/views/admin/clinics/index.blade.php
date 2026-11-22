@extends('layouts.app')

@section('title', 'Klinikalar Sanawy')

@section('header', 'Klinikalar Dolandyryşy')

@section('content')
<div class="space-y-6">

    @if(session('success'))
    <div class="bg-emerald-50 dark:bg-emerald-500/10 border border-emerald-200 dark:border-emerald-500/30 text-emerald-800 dark:text-emerald-400 px-4 py-3 rounded-2xl text-sm flex items-center justify-between shadow-sm">
        <div class="flex items-center gap-2">
            <i class="ti ti-circle-check text-xl"></i>
            <span>{{ session('success') }}</span>
        </div>
        <button type="button" onclick="this.parentElement.remove()" class="text-emerald-600 hover:text-emerald-800 dark:text-emerald-400">
            <i class="ti ti-x text-lg"></i>
        </button>
    </div>
    @endif

    <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-2xl shadow-sm overflow-hidden transition-colors">

        <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <h5 class="font-bold text-gray-900 dark:text-white text-lg flex items-center gap-2">
                <i class="ti ti-building-hospital text-blue-600 dark:text-blue-400"></i>
                Klinikalaryň Sanawy
            </h5>
            <a href="{{ route('admin.clinics.create') }}" class="inline-flex items-center gap-2 bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-xl text-sm font-semibold shadow-sm transition">
                <i class="ti ti-plus text-lg"></i>
                Täze Klinika Goşmak
            </a>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-gray-50 dark:bg-gray-900/50 text-gray-500 dark:text-gray-400 text-xs uppercase tracking-wider border-b border-gray-200 dark:border-gray-700">
                        <th class="py-3.5 px-6 font-semibold">#ID</th>
                        <th class="py-3.5 px-6 font-semibold">Ady</th>
                        <th class="py-3.5 px-6 font-semibold">Şäher / Salgy</th>
                        <th class="py-3.5 px-6 font-semibold">Telefon</th>
                        <th class="py-3.5 px-6 font-semibold">Ýagdaýy</th>
                        <th class="py-3.5 px-6 font-semibold text-end">Amallar</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 dark:divide-gray-700 text-sm">
                    @forelse($clinics as $clinic)
                    <tr class="hover:bg-gray-50/50 dark:hover:bg-gray-700/50 transition">
                        <td class="py-4 px-6 text-gray-500 dark:text-gray-400 font-medium">#{{ $clinic->id }}</td>
                        <td class="py-4 px-6 font-semibold text-gray-900 dark:text-white flex items-center gap-2">
                            <i class="ti ti-hospital text-blue-500 text-lg"></i>
                            {{ $clinic->name }}
                        </td>
                        <td class="py-4 px-6 text-gray-600 dark:text-gray-300">{{ $clinic->city }}</td>
                        <td class="py-4 px-6 text-gray-600 dark:text-gray-300">{{ $clinic->phone }}</td>
                        <td class="py-4 px-6">
                            @if($clinic->is_active)
                            <span class="inline-flex items-center gap-1 px-3 py-1 rounded-full text-xs font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200 dark:bg-emerald-500/10 dark:text-emerald-400 dark:border-emerald-500/30">
                                <i class="ti ti-check text-xs"></i> Açyk / Active
                            </span>
                            @else
                            <span class="inline-flex items-center gap-1 px-3 py-1 rounded-full text-xs font-semibold bg-gray-100 text-gray-600 border border-gray-200 dark:bg-gray-700 dark:text-gray-400 dark:border-gray-600">
                                <i class="ti ti-minus text-xs"></i> Ýapyk
                            </span>
                            @endif
                        </td>
                        <td class="py-4 px-6 text-end">
                            <div class="inline-flex items-center gap-1.5">
                                <a href="{{ route('admin.clinics.show', $clinic->id) }}" class="p-2 text-sky-600 hover:text-white hover:bg-sky-600 dark:text-sky-400 dark:hover:bg-sky-600 rounded-xl transition shadow-xs" title="Maglumaty Gör">
                                    <i class="ti ti-eye text-lg"></i>
                                </a>
                                <a href="{{ route('admin.clinics.edit', $clinic->id) }}" class="p-2 text-amber-600 hover:text-white hover:bg-amber-600 dark:text-amber-400 dark:hover:bg-amber-600 rounded-xl transition shadow-xs" title="Üýtgetmek">
                                    <i class="ti ti-edit text-lg"></i>
                                </a>
                                <form action="{{ route('admin.clinics.destroy', $clinic->id) }}" method="POST" class="inline" onsubmit="return confirm('Hakykatdan hem bu klinikalary pozmak isleýärsiňizmi?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="p-2 text-red-600 hover:text-white hover:bg-red-600 dark:text-red-400 dark:hover:bg-red-600 rounded-xl transition shadow-xs" title="Pozmak">
                                        <i class="ti ti-trash text-lg"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="text-center text-gray-500 dark:text-gray-400 py-8 italic">
                            <div class="flex flex-col items-center justify-center gap-2">
                                <i class="ti ti-database-off text-3xl"></i>
                                <span>Häzirlikçe hiç hili klinika maglumaty ýok.</span>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if(method_exists($clinics, 'links') && $clinics->hasPages())
        <div class="px-6 py-4 border-t border-gray-200 dark:border-gray-700">
            {{ $clinics->links() }}
        </div>
        @endif

    </div>
</div>
@endsection