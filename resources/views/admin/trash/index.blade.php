@extends('layouts.app')
@section('content')
<div class="mx-auto max-w-7xl space-y-6 px-4 py-8">
    <div class="rounded-3xl border border-white/60 bg-white/55 p-6 shadow-xl backdrop-blur-xl dark:border-white/10 dark:bg-slate-900/55">
        <h1 class="text-2xl font-extrabold text-slate-900 dark:text-white">Trash</h1>
        <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Pozulan ýazgylar 90 gün saklanýar. Töleg taryhy aýratynlykda goragly saklanýar.</p>
    </div>
    @foreach($items as $type => $paginator)
    <section class="overflow-hidden rounded-3xl border border-white/60 bg-white/55 p-5 shadow-lg backdrop-blur-xl dark:border-white/10 dark:bg-slate-900/55">
        <div class="mb-4 flex items-center justify-between"><h2 class="text-lg font-bold text-slate-900 dark:text-white">{{ ucfirst($type) }}</h2><span class="text-sm text-slate-500">{{ $paginator->total() }} ýazgy</span></div>
        <div class="overflow-x-auto"><table class="w-full text-left text-sm"><thead><tr class="border-b text-slate-500"><th class="p-3">ID</th><th class="p-3">Maglumat</th><th class="p-3">Pozulan senesi</th><th class="p-3">Galan wagt</th><th class="p-3">Hereket</th></tr></thead><tbody>
        @forelse($paginator as $record)
        @php($label = $record->name ?? $record->booking_code ?? ($record->user?->name ?? ('#'.$record->id)))
        @php($days = max(0, 90 - $record->deleted_at->diffInDays(now())))
        <tr class="border-b border-slate-200/60 dark:border-slate-700/60"><td class="p-3">#{{ $record->id }}</td><td class="p-3 font-semibold">{{ $label }}</td><td class="p-3">{{ $record->deleted_at->format('d.m.Y H:i') }}</td><td class="p-3">{{ $days }} gün</td><td class="p-3"><div class="flex gap-2">
            <form method="POST" action="{{ route('admin.trash.restore',[$type,$record->id]) }}">@csrf<button class="rounded-lg bg-emerald-600 px-3 py-2 text-xs font-semibold text-white">Dikelt</button></form>
            <form method="POST" action="{{ route('admin.trash.force-delete',[$type,$record->id]) }}" onsubmit="return confirm('Bu ýazgyny hemişelik pozmak isleýärsiňizmi?')">@csrf @method('DELETE')<button class="rounded-lg bg-red-600 px-3 py-2 text-xs font-semibold text-white">Hemişelik poz</button></form>
        </div></td></tr>
        @empty <tr><td colspan="5" class="p-6 text-center text-slate-500">Bu bölümde ýazgy ýok.</td></tr> @endforelse
        </tbody></table></div>
        <div class="mt-4">{{ $paginator->links() }}</div>
    </section>
    @endforeach
</div>
@endsection
