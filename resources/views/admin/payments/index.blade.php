@extends('layouts.app')
@section('title', 'Tölegler')
@section('header', 'Töleg dolandyryşy')
@section('content')
<div class="space-y-5">
    @if(session('success'))
    <div class="rounded-xl bg-emerald-50 p-3 text-emerald-700">
        {{ session('success') }}
    </div>
    @endif
    <div class="overflow-x-auto rounded-2xl border border-gray-200 bg-white dark:border-gray-700 dark:bg-gray-800">
        <table class="w-full text-left text-sm">
            <thead>
                <tr class="border-b dark:border-gray-700">
                    <th class="p-3">Bron belgisi</th>
                    <th class="p-3">Näsag</th>
                    <th class="p-3">Lukman</th>
                    <th class="p-3">Klinika</th>
                    <th class="p-3">Mukdar</th>
                    <th class="p-3">Usul</th>
                    <th class="p-3">Ýagdaý</th>
                    <th class="p-3">Tölenen wagty</th>
                </tr>
            </thead>
            <tbody>
                @forelse($payments as $payment)
                <tr class="border-t dark:border-gray-700">
                    <td class="p-3"><a class="text-blue-300 underline" href="{{ route('admin.payments.show',$payment) }}">{{ $payment->appointment?->booking_code }}</a></td>
                    <td class="p-3">{{ $payment->appointment?->patient?->name }}</td>
                    <td class="p-3">{{ $payment->appointment?->doctor?->user?->name }}</td>
                    <td class="p-3">{{ $payment->appointment?->clinic?->name }}</td>
                    <td class="p-3">{{ number_format((float)$payment->amount,2) }} {{ $payment->currency }}</td>
                    <td class="p-3">{{ $payment->payment_method }}</td>
                    <td class="p-3">{{ $payment->status }}</td>
                    <td class="p-3">{{ $payment->paid_at?->format('Y-m-d H:i') ?? '—' }}</td>
                </tr>
                @empty<tr>
                    <td colspan="8" class="p-8 text-center text-gray-500">Töleg ýazgysy ýok.</td>
                </tr>@endforelse
            </tbody>
        </table>
    </div>{{ $payments->links() }}
</div>
@endsection