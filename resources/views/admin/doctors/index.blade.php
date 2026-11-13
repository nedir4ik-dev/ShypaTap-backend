@extends('layouts.app')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 transition-colors duration-200">

    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Lukmanlar Sanawy</h1>
            <p class="text-sm text-gray-500 dark:text-gray-400">Ulgamda bellige alnan ähli lukmanlary dolandyryň.</p>
        </div>
        <a href="{{ route('admin.doctors.create') }}" class="inline-flex items-center justify-center bg-blue-600 hover:bg-blue-700 text-white font-medium px-4 py-2 rounded-lg shadow-md transition">
            + Täze Lukman Goşmak
        </a>
    </div>

    <form method="GET" action="{{ route('admin.doctors.index') }}" class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 p-4 rounded-xl shadow-sm mb-6 flex flex-wrap gap-4 items-center transition-colors">
        <div class="flex-1 min-w-[240px]">
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Lukmanyň ady boýunça gözleg..." class="w-full bg-gray-50 dark:bg-gray-900 border border-gray-300 dark:border-gray-700 rounded-lg px-4 py-2 text-gray-900 dark:text-gray-100 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-blue-500">
        </div>
        <div class="flex items-center gap-3">
            <button type="submit" class="bg-gray-800 dark:bg-gray-700 hover:bg-gray-700 dark:hover:bg-gray-600 text-white font-medium px-4 py-2 rounded-lg transition">Gözleg</button>
            <a href="{{ route('admin.doctors.index') }}" class="text-sm text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200">Arassala</a>
        </div>
    </form>

    <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 shadow-xl rounded-xl overflow-hidden transition-colors">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-gray-50 dark:bg-gray-900/50 border-b border-gray-200 dark:border-gray-700 text-gray-500 dark:text-gray-400 text-xs uppercase tracking-wider">
                        <th class="p-4">Ady</th>
                        <th class="p-4">Email / Telefon</th>
                        <th class="p-4">Hünär Ugry</th>
                        <th class="p-4">Tassyklama</th>
                        <th class="p-4 text-right">Hereketler</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 dark:divide-gray-700 text-sm">
                    @forelse($doctors as $doctor)
                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/30 transition">
                        <td class="p-4 font-semibold text-gray-900 dark:text-white">{{ $doctor->user->name ?? 'N/A' }}</td>
                        <td class="p-4 text-gray-600 dark:text-gray-300">
                            <div>{{ $doctor->user->email ?? '' }}</div>
                            <div class="text-xs text-gray-400">{{ $doctor->user->phone ?? '' }}</div>
                        </td>
                        <td class="p-4 text-blue-600 dark:text-blue-400 font-medium">{{ $doctor->specialization->name ?? 'Görkezilmedik' }}</td>
                        <td class="p-4">
                            <span class="px-3 py-1 rounded-full text-xs font-semibold tracking-wide {{ $doctor->is_verified ? 'bg-emerald-50 text-emerald-700 border border-emerald-200 dark:bg-emerald-500/10 dark:text-emerald-400 dark:border-emerald-500/30' : 'bg-amber-50 text-amber-700 border border-amber-200 dark:bg-amber-500/10 dark:text-amber-400 dark:border-amber-500/30' }}">
                                {{ $doctor->is_verified ? 'Tassyklanan' : 'Garaşylýär' }}
                            </span>
                        </td>
                        <td class="p-4 text-right space-x-3">
                            <a href="{{ route('admin.doctors.show', $doctor->id) }}" class="text-blue-600 dark:text-blue-400 hover:underline font-medium">Gör</a>
                            <a href="{{ route('admin.doctors.edit', $doctor->id) }}" class="text-indigo-600 dark:text-indigo-400 hover:underline font-medium">Üýtget</a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="p-8 text-center text-gray-500">Hiç hili lukman tapylmady.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-6">
        {{ $doctors->withQueryString()->links() }}
    </div>
</div>
@endsection
