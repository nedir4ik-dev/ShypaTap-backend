@extends('layouts.app')
@section('title', 'Bahalandyrmalar')
@section('header', 'Lukmanlara berlen bahalar')
@section('content')
<div class="space-y-5">
    <div class="flex flex-wrap gap-2">
        <a href="{{ route('admin.reviews.index') }}" class="rounded-lg px-3 py-2 text-sm {{ !request('archived') ? 'bg-emerald-600 text-white' : 'border' }}">Işjeň bahalar</a>
        <a href="{{ route('admin.reviews.index', ['archived' => 1]) }}" class="rounded-lg px-3 py-2 text-sm {{ request('archived') ? 'bg-emerald-600 text-white' : 'border' }}">Arhiw</a>
    </div>
    @if(session('success'))<div class="rounded-xl bg-emerald-50 p-3 text-emerald-700">{{ session('success') }}</div>@endif
    <div class="overflow-x-auto rounded-2xl border border-gray-200 bg-white dark:border-gray-700 dark:bg-gray-800">
        <table class="w-full text-left text-sm">
            <thead>
                <tr class="border-b dark:border-gray-700">
                    <th class="p-3">Näsag</th>
                    <th class="p-3">Lukman</th>
                    <th class="p-3">Bron</th>
                    <th class="p-3">Baha</th>
                    <th class="p-3">Teswir</th>
                    <th class="p-3">Hereket</th>
                </tr>
            </thead>
            <tbody>
                @forelse($reviews as $review)
                <tr class="border-t dark:border-gray-700">
                    <td class="p-3">{{ $review->patient?->name }}</td>
                    <td class="p-3">{{ $review->doctor?->user?->name }}</td>
                    <td class="p-3">{{ $review->appointment?->booking_code }}</td>
                    <td class="p-3 font-bold text-amber-600">★ {{ number_format((float)$review->rating, 1) }}/5</td>
                    <td class="p-3 max-w-sm">{{ $review->comment ?: '—' }}</td>
                    <td class="p-3">
                        @if($review->trashed())
                        <form method="POST" action="{{ route('admin.reviews.restore', $review->id) }}">@csrf @method('PATCH')<button class="rounded-lg bg-emerald-600 px-3 py-2 text-xs font-semibold text-white">Dikelt</button></form>
                        @else
                        <form method="POST" action="{{ route('admin.reviews.destroy', $review) }}" onsubmit="return confirm('Bu bahalandyrmany arhiwe geçiräýelimi?')">@csrf @method('DELETE')<button class="rounded-lg bg-red-600 px-3 py-2 text-xs font-semibold text-white">Arhiw</button></form>
                        @endif
                    </td>
                </tr>
                @empty<tr>
                    <td colspan="6" class="p-8 text-center text-gray-500">Bahalandyrma ýok.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    {{ $reviews->links() }}
</div>
@endsection