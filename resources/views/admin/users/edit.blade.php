@extends('layouts.app')

@section('title', 'Ulanyjy Maglumatlaryny Üýtgetmek')

@section('header', 'Ulanyjylar Dolandyryşy')

@section('content')
<div class="max-w-3xl mx-auto space-y-6">

    {{-- Başlyk we Yzyna gaýtmak --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <h1 class="text-2xl font-bold text-gray-900 dark:text-white flex items-center gap-2">
            <i class="ti ti-user-edit text-blue-600 dark:text-blue-400"></i>
            Ulanyjy Üýtgetmek: <span class="text-blue-600 dark:text-blue-400 font-semibold">{{ $user->name }}</span>
        </h1>
        <a href="{{ route('admin.users.index') }}" class="inline-flex items-center gap-1.5 text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white text-sm font-medium transition">
            <i class="ti ti-arrow-left text-lg"></i>
            Yzyna gaýtmak
        </a>
    </div>

    {{-- Ýalňyşlyk Habarlary (Errors) --}}
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
    <form action="{{ route('admin.users.update', $user->id) }}" method="POST" class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-2xl shadow-sm p-6 sm:p-8 space-y-6 transition-colors">
        @csrf
        @method('PUT')

        {{-- Ady we Familiýasy --}}
        <div>
            <label class="block text-xs font-semibold uppercase tracking-wider text-gray-700 dark:text-gray-300 mb-2">Ady we Familiýasy</label>
            <div class="relative">
                <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-gray-400">
                    <i class="ti ti-user text-lg"></i>
                </span>
                <input type="text" name="name" value="{{ old('name', $user->name) }}" required
                    class="w-full pl-10 pr-4 py-2.5 bg-gray-50 dark:bg-gray-900 border border-gray-300 dark:border-gray-700 rounded-xl text-gray-900 dark:text-gray-100 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-blue-500 transition text-sm">
            </div>
        </div>

        {{-- Email we Telefon --}}
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <label class="block text-xs font-semibold uppercase tracking-wider text-gray-700 dark:text-gray-300 mb-2">Email Salgysy</label>
                <div class="relative">
                    <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-gray-400">
                        <i class="ti ti-mail text-lg"></i>
                    </span>
                    <input type="email" name="email" value="{{ old('email', $user->email) }}" required
                        class="w-full pl-10 pr-4 py-2.5 bg-gray-50 dark:bg-gray-900 border border-gray-300 dark:border-gray-700 rounded-xl text-gray-900 dark:text-gray-100 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-blue-500 transition text-sm">
                </div>
            </div>
            <div>
                <label class="block text-xs font-semibold uppercase tracking-wider text-gray-700 dark:text-gray-300 mb-2">Telefon belgisi</label>
                <div class="relative">
                    <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-gray-400">
                        <i class="ti ti-phone text-lg"></i>
                    </span>
                    <input type="text" name="phone" value="{{ old('phone', $user->phone) }}"
                        class="w-full pl-10 pr-4 py-2.5 bg-gray-50 dark:bg-gray-900 border border-gray-300 dark:border-gray-700 rounded-xl text-gray-900 dark:text-gray-100 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-blue-500 transition text-sm" placeholder="+9936xxxxxxxx">
                </div>
            </div>
        </div>

        {{-- Täze Parol --}}
        <div>
            <label class="block text-xs font-semibold uppercase tracking-wider text-gray-700 dark:text-gray-300 mb-2">Täze Parol (Eger üýtgetmezek bolsaňyz boş goýuň)</label>
            <div class="relative">
                <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-gray-400">
                    <i class="ti ti-lock text-lg"></i>
                </span>
                <input type="password" name="password"
                    class="w-full pl-10 pr-4 py-2.5 bg-gray-50 dark:bg-gray-900 border border-gray-300 dark:border-gray-700 rounded-xl text-gray-900 dark:text-gray-100 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-blue-500 transition text-sm" placeholder="Iň az 8 simwol">
            </div>
        </div>

        {{-- Roly / Derejesi (Role) --}}
        <div>
            <label class="block text-xs font-semibold uppercase tracking-wider text-gray-700 dark:text-gray-300 mb-2">Roly / Derejesi</label>
            <div class="relative">
                <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-gray-400">
                    <i class="ti ti-shield text-lg"></i>
                </span>
                <select name="role" required
                    class="w-full pl-10 pr-4 py-2.5 bg-gray-50 dark:bg-gray-900 border border-gray-300 dark:border-gray-700 rounded-xl text-gray-900 dark:text-gray-100 focus:outline-none focus:ring-2 focus:ring-blue-500 transition text-sm appearance-none">
                    <option value="admin" {{ old('role', $user->role) == 'admin' ? 'selected' : '' }}>Admin</option>
                    <option value="doctor" {{ old('role', $user->role) == 'doctor' ? 'selected' : '' }}>Lukman</option>
                    <option value="patient" {{ old('role', $user->role) == 'patient' ? 'selected' : '' }}>Näsag</option>
                </select>
            </div>
        </div>

        {{-- Submit Düwmesi --}}
        <div class="pt-4 border-t border-gray-200 dark:border-gray-700 flex items-center justify-between gap-4">
            <a href="{{ route('admin.users.index') }}" class="px-5 py-2.5 border border-gray-300 dark:border-gray-700 text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-xl text-sm font-semibold transition">
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