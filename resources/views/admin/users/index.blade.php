@extends('layouts.app')

@section('title', 'Ulanyjylar Sanawy')

@section('header', 'Ulanyjylar Dolandyryşy')

@section('content')
<div class="space-y-6">

    {{-- Üstünlikli Habar (Success Alert) --}}
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

    {{-- Esasy Karta --}}
    <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-2xl shadow-sm overflow-hidden transition-colors">

        {{-- Karta Başlygy --}}
        <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <h5 class="font-bold text-gray-900 dark:text-white text-lg flex items-center gap-2">
                <i class="ti ti-users text-blue-600 dark:text-blue-400"></i>
                Ulanyjylaryň Sanawy
            </h5>
            {{-- Eger täze ulanyjy goşmak düwmesi bar bolsa --}}
            @if(Route::has('admin.users.create'))
            <a href="{{ route('admin.users.create') }}" class="inline-flex items-center gap-2 bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-xl text-sm font-semibold shadow-sm transition">
                <i class="ti ti-user-plus text-lg"></i>
                Täze Ulanyjy Goşmak
            </a>
            @endif
        </div>

        {{-- Tablisa --}}
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-gray-50 dark:bg-gray-900/50 text-gray-500 dark:text-gray-400 text-xs uppercase tracking-wider border-b border-gray-200 dark:border-gray-700">
                        <th class="py-3.5 px-6 font-semibold">#ID</th>
                        <th class="py-3.5 px-6 font-semibold">Ady we Familiýasy</th>
                        <th class="py-3.5 px-6 font-semibold">Email Salgysy</th>
                        <th class="py-3.5 px-6 font-semibold">Telefon</th>
                        <th class="py-3.5 px-6 font-semibold">Roly / Derejesi</th>
                        <th class="py-3.5 px-6 font-semibold text-end">Amallar</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 dark:divide-gray-700 text-sm">
                    @forelse($users as $user)
                    <tr class="hover:bg-gray-50/50 dark:hover:bg-gray-700/50 transition">
                        <td class="py-4 px-6 text-gray-500 dark:text-gray-400 font-medium">#{{ $user->id }}</td>
                        <td class="py-4 px-6 font-semibold text-gray-900 dark:text-white flex items-center gap-2">
                            <i class="ti ti-user-circle text-blue-500 text-xl"></i>
                            {{ $user->name }}
                        </td>
                        <td class="py-4 px-6 text-gray-600 dark:text-gray-300">{{ $user->email }}</td>
                        <td class="py-4 px-6 text-gray-600 dark:text-gray-300">{{ $user->phone ?? 'Görkezilmedik' }}</td>
                        <td class="py-4 px-6">
                            <span class="inline-flex items-center gap-1 px-3 py-1 rounded-full text-xs font-semibold bg-sky-50 text-sky-700 border border-sky-200 dark:bg-sky-500/10 dark:text-sky-400 dark:border-sky-500/30">
                                {{ $user->role ?? 'User' }}
                            </span>
                        </td>
                        <td class="py-4 px-6 text-end">
                            <div class="inline-flex items-center gap-1.5">
                                {{-- Üýtgetmek --}}
                                @if(Route::has('admin.users.edit'))
                                <a href="{{ route('admin.users.edit', $user->id) }}" class="p-2 text-amber-600 hover:text-white hover:bg-amber-600 dark:text-amber-400 dark:hover:bg-amber-600 rounded-xl transition shadow-xs" title="Üýtgetmek">
                                    <i class="ti ti-edit text-lg"></i>
                                </a>
                                @endif

                                {{-- Pozmak (Öz akkaundyňy pozmagyň öňüni almak üçin barlag goşmak bolar) --}}
                                @if(auth()->id() !== $user->id && Route::has('admin.users.destroy'))
                                <form action="{{ route('admin.users.destroy', $user->id) }}" method="POST" class="inline" onsubmit="return confirm('Hakykatdan hem bu ulanyjyny pozmak isleýärsiňizmi?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="p-2 text-red-600 hover:text-white hover:bg-red-600 dark:text-red-400 dark:hover:bg-red-600 rounded-xl transition shadow-xs" title="Pozmak">
                                        <i class="ti ti-trash text-lg"></i>
                                    </button>
                                </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="text-center text-gray-500 dark:text-gray-400 py-8 italic">
                            <div class="flex flex-col items-center justify-center gap-2">
                                <i class="ti ti-database-off text-3xl"></i>
                                <span>Häzirlikçe hiç hili ulanyjy maglumaty ýok.</span>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Paginasiýa --}}
        @if(method_exists($users, 'links') && $users->hasPages())
        <div class="px-6 py-4 border-t border-gray-200 dark:border-gray-700">
            {{ $users->links() }}
        </div>
        @endif

    </div>
</div>
@endsection