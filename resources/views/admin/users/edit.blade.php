@extends('layouts.app')

@section('title', 'Ulanyjy Maglumatlaryny Üýtgetmek')

@section('header', 'Ulanyjylar Dolandyryşy')

@section('content')
<div class="max-w-3xl mx-auto space-y-6">

    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <h1 class="text-2xl font-bold text-gray-900 dark:text-white flex items-center gap-2">
            <i class="ti ti-user-edit text-blue-600 dark:text-blue-400"></i>
            Ulanyjy Üýtgetmek: <span class="text-blue-600 dark:text-blue-400 font-semibold">#{{ $user->id }} {{ $user->name }}</span>
        </h1>
        <a href="{{ route('admin.users.index') }}" class="inline-flex items-center gap-1.5 text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white text-sm font-medium transition">
            <i class="ti ti-arrow-left text-lg"></i>
            Yzyna gaýtmak
        </a>
    </div>

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

    <form action="{{ route('admin.users.update', $user->id) }}" method="POST" enctype="multipart/form-data" class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-2xl shadow-sm p-6 sm:p-8 space-y-6 transition-colors">
        @csrf

        <div class="flex flex-col sm:flex-row sm:items-center gap-4 rounded-2xl border border-blue-200 dark:border-blue-900 bg-blue-50/50 dark:bg-blue-950/20 p-4">
            <div class="h-20 w-20 shrink-0 overflow-hidden rounded-full bg-blue-100 dark:bg-blue-900 flex items-center justify-center">
                @if($user->avatar_url)
                    <img id="avatar-preview" src="{{ $user->avatar_url }}" alt="Avatar" class="h-full w-full object-cover">
                @else
                    <span id="avatar-initial" class="text-3xl font-bold text-blue-700 dark:text-blue-300">{{ mb_strtoupper(mb_substr($user->name, 0, 1)) }}</span>
                    <img id="avatar-preview" src="" alt="Avatar" class="hidden h-full w-full object-cover">
                @endif
            </div>
            <div class="space-y-2">
                <label class="block text-sm font-semibold text-gray-800 dark:text-gray-100">Profil suraty</label>
                <input type="file" name="avatar" accept=".jpg,.jpeg,.png,image/jpeg,image/png"
                    onchange="previewAvatar(event)"
                    class="block w-full text-sm text-gray-600 dark:text-gray-300 file:mr-3 file:rounded-lg file:border-0 file:bg-blue-600 file:px-3 file:py-2 file:text-white">
                <p class="text-xs text-gray-500 dark:text-gray-400">JPG/JPEG/PNG, iň köp 3 MB. Serwerde WebP edilýär.</p>
                @if($user->avatar_url)
                    <label class="inline-flex items-center gap-2 text-sm text-red-600 dark:text-red-400">
                        <input type="checkbox" name="remove_avatar" value="1" class="rounded border-red-300">
                        Häzirki suraty aýyr
                    </label>
                @endif
            </div>
        </div>
        
        @method('PUT')

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

        <div class="rounded-xl border border-slate-200 p-4 dark:border-slate-700">
            <p class="text-xs font-semibold uppercase tracking-wider text-slate-500">Esasy rol</p>
            <p class="mt-1 font-bold">{{ ucfirst($user->role) }}</p>
            <p class="mt-1 text-xs text-slate-500">Roly we klinika çägini aşakdaky RBAC bölüminden belläň.</p>
        </div>

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

    <section class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-700 dark:bg-slate-800">
        <h2 class="mb-3 text-lg font-bold">RBAC — ulanyjy roluny bellemek</h2>
        <p class="mb-4 text-xs text-slate-500">Admin diňe öz rugsatlarynyň çägindäki rollary we özüne bellenen klinikalary saýlap biler.</p>
        <form method="POST" action="{{ route('admin.users.roles',$user->id) }}" class="grid gap-4 sm:grid-cols-2">@csrf @method('PUT')
            <label class="text-sm font-semibold">Rol<select name="role_id" required class="mt-1 w-full rounded-xl border p-3 dark:bg-slate-900">@foreach($roles as $role)<option value="{{ $role->id }}" @selected($user->roles->contains('id',$role->id))>{{ $role->name }}</option>@endforeach</select></label>
            <label class="text-sm font-semibold">Klinika çägi<select name="clinic_id" class="mt-1 w-full rounded-xl border p-3 dark:bg-slate-900">@if(in_array(strtolower((string)auth()->user()->role), ['superadmin','super_admin']))<option value="">Global (SuperAdmin)</option>@else<option value="" disabled selected>Klinika saýlaň</option>@endif @foreach($clinics as $clinic)<option value="{{ $clinic->id }}">{{ $clinic->name }}</option>@endforeach</select></label>
            <div class="sm:col-span-2"><button class="rounded-xl bg-emerald-600 px-5 py-2.5 text-sm font-semibold text-white">Roly belle</button></div>
        </form>
        <div class="mt-4 flex flex-wrap gap-2">@foreach($user->roles as $role)<span class="rounded-lg bg-slate-100 px-3 py-1 text-xs dark:bg-slate-700">{{ $role->name }} · {{ $role->pivot->clinic_id ? 'Klinika #'.$role->pivot->clinic_id : 'Global' }}</span>@endforeach</div>
        <div class="my-5 border-t border-slate-200 dark:border-slate-700"></div>
        <h3 class="mb-3 font-bold">Şahsy rugsat üýtgetmeleri (Allow / Deny)</h3>
        <form method="POST" action="{{ route('admin.users.overrides',$user->id) }}" class="grid gap-3 sm:grid-cols-4">@csrf @method('PUT')
            <select name="permission_id" required class="rounded-xl border p-2 text-sm dark:bg-slate-900">@foreach($permissions as $permission)<option value="{{ $permission->id }}">{{ $permission->name }}</option>@endforeach</select>
            <select name="effect" required class="rounded-xl border p-2 text-sm dark:bg-slate-900"><option value="allow">Allow</option><option value="deny">Deny</option></select>
            <select name="clinic_id" class="rounded-xl border p-2 text-sm dark:bg-slate-900">@if(in_array(strtolower((string)auth()->user()->role), ['superadmin','super_admin']))<option value="">Global</option>@else<option value="" disabled selected>Klinika</option>@endif @foreach($clinics as $clinic)<option value="{{ $clinic->id }}">{{ $clinic->name }}</option>@endforeach</select>
            <button class="rounded-xl bg-blue-600 px-4 py-2 text-sm font-semibold text-white">Rugsat goş</button>
        </form>
        <div class="mt-3 flex flex-wrap gap-2">@foreach($user->permissionOverrides as $override)<span class="rounded-lg px-3 py-1 text-xs {{ $override->effect==='deny'?'bg-red-100 text-red-700':'bg-emerald-100 text-emerald-700' }}">{{ $override->permission?->name }}: {{ strtoupper($override->effect) }} · {{ $override->clinic_id ? 'Klinika #'.$override->clinic_id : 'Global' }}</span>@endforeach</div>
    </section>
</div>

@push('scripts')
<script>
function previewAvatar(event) {
    const file = event.target.files && event.target.files[0];
    if (!file) return;
    if (!['image/jpeg', 'image/png'].includes(file.type) || file.size > 3 * 1024 * 1024) {
        alert('Diňe JPG/JPEG/PNG we iň köp 3 MB surat saýlaň.');
        event.target.value = '';
        return;
    }
    const preview = document.getElementById('avatar-preview');
    const initial = document.getElementById('avatar-initial');
    if (preview) {
        preview.src = URL.createObjectURL(file);
        preview.classList.remove('hidden');
    }
    if (initial) initial.classList.add('hidden');
}
</script>
@endpush

@endsection