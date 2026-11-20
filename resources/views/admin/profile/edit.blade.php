@extends('layouts.app')

@section('title', 'Şahsy profil')
@section('header', 'Şahsy profil')

@section('content')
<div class="mx-auto max-w-3xl space-y-6">
    @if (session('success'))
    <div class="rounded-xl border border-emerald-200 bg-emerald-50 p-4 text-sm text-emerald-800">{{ session('success') }}</div>
    @endif
    @if ($errors->any())
    <div class="rounded-xl border border-red-200 bg-red-50 p-4 text-sm text-red-700">
        @foreach ($errors->all() as $error)<p>{{ $error }}</p>@endforeach
    </div>
    @endif

    <section class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-700 dark:bg-gray-800">
        <h2 class="mb-5 text-lg font-bold text-gray-900 dark:text-white">Profil suraty</h2>
        <div class="mb-5 flex items-center gap-4">
            @if ($user->avatar_url)
            <img src="{{ $user->avatar_url }}" alt="Profil suraty" class="h-20 w-20 rounded-full object-cover">
            @else
            <div class="flex h-20 w-20 items-center justify-center rounded-full bg-blue-100 text-3xl font-bold text-blue-800">{{ mb_strtoupper(mb_substr($user->name, 0, 1)) }}</div>
            @endif
            <div>
                <p class="font-semibold text-gray-900 dark:text-white">{{ $user->name }}</p>
                <p class="text-sm text-gray-500">JPG ýa-da PNG, iň köp 3 MB. WebP görnüşinde saklanýar.</p>
            </div>
        </div>
        <form action="{{ route('admin.profile.avatar.upload') }}" method="POST" enctype="multipart/form-data" class="flex flex-wrap items-center gap-3">
            @csrf
            <input type="file" name="avatar" accept=".jpg,.jpeg,.png,image/jpeg,image/png" required class="max-w-full text-sm text-gray-700 dark:text-gray-300">
            <button class="rounded-xl bg-blue-700 px-4 py-2 text-sm font-semibold text-white hover:bg-blue-800">Suraty ýükle</button>
        </form>
        @if ($user->avatar_url)
        <form action="{{ route('admin.profile.avatar.delete') }}" method="POST" class="mt-3" onsubmit="return confirm('Profil suratyny aýyrmak isleýärsiňizmi?')">
            @csrf
            @method('DELETE')
            <button class="text-sm font-semibold text-red-600 hover:underline">Suraty aýyr</button>
        </form>
        @endif
    </section>

    <section class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-700 dark:bg-gray-800">
        <h2 class="mb-5 text-lg font-bold text-gray-900 dark:text-white">Şahsy maglumatlar</h2>
        <form action="{{ route('admin.profile.update') }}" method="POST" class="space-y-4">
            @csrf
            @method('PUT')
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Ady
                <input name="name" value="{{ old('name', $user->name) }}" required class="mt-1 w-full rounded-xl border border-gray-300 bg-gray-50 p-3 dark:border-gray-600 dark:bg-gray-900 dark:text-white">
            </label>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">E-poçta
                <input type="email" name="email" value="{{ old('email', $user->email) }}" required class="mt-1 w-full rounded-xl border border-gray-300 bg-gray-50 p-3 dark:border-gray-600 dark:bg-gray-900 dark:text-white">
            </label>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Telefon
                <input name="phone" value="{{ old('phone', $user->phone) }}" class="mt-1 w-full rounded-xl border border-gray-300 bg-gray-50 p-3 dark:border-gray-600 dark:bg-gray-900 dark:text-white">
            </label>
            <button class="rounded-xl bg-blue-700 px-5 py-3 font-semibold text-white hover:bg-blue-800">Maglumatlary sakla</button>
        </form>
    </section>
</div>
@endsection