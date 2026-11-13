@extends('layouts.app')

@section('title', 'Täze Ulanyjy Goşmak')

@section('header', 'Ulanyjy Dolandyryşy')

@section('content')
<div class="max-w-3xl mx-auto space-y-6">

    <div class="flex items-center justify-between">
        <h5 class="font-bold text-gray-900 dark:text-white text-xl flex items-center gap-2">
            <i class="ti ti-user-plus text-emerald-600 dark:text-emerald-400"></i>
            Täze Ulanyjy Döretmek
        </h5>
        <a href="{{ route('admin.users.index') }}" class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 text-sm hover:bg-gray-100 dark:hover:bg-gray-700 transition">
            <i class="ti ti-arrow-left"></i> Yza gaýdyt
        </a>
    </div>

    @if ($errors->any())
    <div class="bg-red-50 dark:bg-red-500/10 border border-red-200 dark:border-red-500/30 text-red-800 dark:text-red-400 px-4 py-3 rounded-2xl text-sm space-y-1">
        <div class="font-semibold flex items-center gap-2">
            <i class="ti ti-alert-circle text-lg"></i> Maglumatlarda ýalňyşlyklar bar:
        </div>
        <ul class="list-disc list-inside text-xs">
            @foreach ($errors->all() as $error)
            <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
    @endif

    <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-2xl shadow-sm p-6 sm:p-8">
        <form action="{{ route('admin.users.store') }}" method="POST" class="space-y-6">
            @csrf

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                <div class="space-y-1.5">
                    <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300">Doly adyňyz</label>
                    <input type="text" name="name" value="{{ old('name') }}" required placeholder="Ady Familiýasy"
                        class="w-full px-4 py-3 rounded-xl border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900 text-gray-900 dark:text-white text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500" />
                </div>

                <div class="space-y-1.5">
                    <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300">Email Salgysy</label>
                    <input type="email" name="email" value="{{ old('email') }}" required placeholder="example@mail.com"
                        class="w-full px-4 py-3 rounded-xl border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900 text-gray-900 dark:text-white text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500" />
                </div>

                <div class="space-y-1.5">
                    <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300">Telefon Belgi</label>
                    <input type="text" name="phone" value="{{ old('phone') }}" placeholder="+99365XXXXXX"
                        class="w-full px-4 py-3 rounded-xl border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900 text-gray-900 dark:text-white text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500" />
                </div>

                <div class="space-y-1.5">
                    <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300">Ulgamdaky Roly</label>
                    <select name="role" required class="w-full px-4 py-3 rounded-xl border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900 text-gray-900 dark:text-white text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500">
                        <option value="patient" {{ old('role') == 'patient' ? 'selected' : '' }}>Näsag (Patient)</option>
                        <option value="doctor" {{ old('role') == 'doctor' ? 'selected' : '' }}>Lukman (Doctor)</option>
                        <option value="receptionist" {{ old('role') == 'receptionist' ? 'selected' : '' }}>Registratura</option>
                        <option value="admin" {{ old('role') == 'admin' ? 'selected' : '' }}>Admin</option>
                    </select>
                </div>

                <div class="space-y-1.5 sm:col-span-2">
                    <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300">Açar sözi (Password)</label>
                    <input type="password" name="password" required placeholder="Iň az 8 simwol bolmaly"
                        class="w-full px-4 py-3 rounded-xl border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900 text-gray-900 dark:text-white text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500" />
                </div>

                <div class="sm:col-span-2 flex items-center gap-3 pt-2">
                    <input type="checkbox" name="is_active" id="is_active" value="1" {{ old('is_active', true) ? 'checked' : '' }}
                        class="w-5 h-5 rounded border-gray-300 text-emerald-600 focus:ring-emerald-500" />
                    <label for="is_active" class="text-sm font-medium text-gray-700 dark:text-gray-300 cursor-pointer">Hasaby aktiw ýagdaýda döretmek</label>
                </div>
            </div>

            <div class="flex items-center justify-end gap-3 pt-4 border-t border-gray-200 dark:border-gray-700">
                <a href="{{ route('admin.users.index') }}" class="px-5 py-2.5 rounded-xl border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 text-sm hover:bg-gray-100 dark:hover:bg-gray-700 transition">
                    Ýatjyr
                </a>
                <button type="submit" class="px-6 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-semibold shadow-sm transition flex items-center gap-2">
                    <i class="ti ti-device-floppy"></i> Ulanyjyny Ýatda Sakla
                </button>
            </div>
        </form>
    </div>
</div>
@endsection