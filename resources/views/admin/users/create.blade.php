@extends('layouts.app')

@section('title', 'Täze Ulanyjy Goşmak')

@section('header', 'Ulanyjy Dolandyryşy')

@section('content')
<div class="max-w-3xl mx-auto space-y-6">

    <div class="flex items-center justify-between">
        <h5 class="font-bold text-gray-900 dark:text-white text-xl flex items-center gap-2">
            <i class="ti ti-user-plus text-blue-600 dark:text-blue-400"></i>
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
        <form action="{{ route('admin.users.store') }}" method="POST" enctype="multipart/form-data" class="space-y-6">
            @csrf

            <div class="flex flex-col sm:flex-row sm:items-center gap-4 rounded-2xl border border-blue-200 dark:border-blue-900 bg-blue-50/50 dark:bg-blue-950/20 p-4">
                <div class="h-20 w-20 shrink-0 overflow-hidden rounded-full bg-blue-100 dark:bg-blue-900 flex items-center justify-center">
                    <span id="avatar-initial" class="text-3xl font-bold text-blue-700 dark:text-blue-300">{{ mb_strtoupper(mb_substr(old('name', 'U'), 0, 1)) }}</span>
                    <img id="avatar-preview" src="" alt="Avatar" class="hidden h-full w-full object-cover">
                </div>
                <div class="space-y-2">
                    <label class="block text-sm font-semibold text-gray-800 dark:text-gray-100">Profil suraty (islege görä)</label>
                    <input type="file" name="avatar" accept=".jpg,.jpeg,.png,image/jpeg,image/png"
                        onchange="previewAvatar(event)"
                        class="block w-full text-sm text-gray-600 dark:text-gray-300 file:mr-3 file:rounded-lg file:border-0 file:bg-blue-600 file:px-3 file:py-2 file:text-white">
                    <p class="text-xs text-gray-500 dark:text-gray-400">JPG/JPEG/PNG, iň köp 3 MB. Serwerde WebP edilýär.</p>
                </div>
            </div>


            <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                <div class="space-y-1.5">
                    <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300">Doly adyňyz</label>
                    <input type="text" name="name" value="{{ old('name') }}" required placeholder="Ady Familiýasy"
                        class="w-full px-4 py-3 rounded-xl border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900 text-gray-900 dark:text-white text-sm focus:outline-none focus:ring-2 focus:ring-blue-500" />
                </div>

                <div class="space-y-1.5">
                    <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300">Email Salgysy</label>
                    <input type="email" name="email" value="{{ old('email') }}" required placeholder="example@mail.com"
                        class="w-full px-4 py-3 rounded-xl border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900 text-gray-900 dark:text-white text-sm focus:outline-none focus:ring-2 focus:ring-blue-500" />
                </div>

                <div class="space-y-1.5">
                    <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300">Telefon Belgi</label>
                    <input type="text" name="phone" value="{{ old('phone') }}" placeholder="+99365XXXXXX"
                        class="w-full px-4 py-3 rounded-xl border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900 text-gray-900 dark:text-white text-sm focus:outline-none focus:ring-2 focus:ring-blue-500" />
                </div>

                <div class="space-y-1.5">
                    <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300">Ulgamdaky Roly</label>
                    <select name="role" required class="w-full px-4 py-3 rounded-xl border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900 text-gray-900 dark:text-white text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="patient" {{ old('role') == 'patient' ? 'selected' : '' }}>Näsag (Patient)</option>
                        <option value="doctor" {{ old('role') == 'doctor' ? 'selected' : '' }}>Lukman (Doctor)</option>
                        <option value="receptionist" {{ old('role') == 'receptionist' ? 'selected' : '' }}>Registratura</option>
                        <option value="admin" {{ old('role') == 'admin' ? 'selected' : '' }}>Admin</option>
                    </select>
                </div>

                <div id="clinic-assignment" class="hidden space-y-2 sm:col-span-2">
                    <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300">Klinikalar (birnäçe saýlap bilersiňiz)</label>
                    <select name="clinic_ids[]" multiple size="4" class="w-full rounded-xl border border-gray-200 bg-gray-50 px-4 py-3 text-sm dark:border-gray-700 dark:bg-gray-900 dark:text-white">
                        @foreach($clinics as $clinic)
                        <option value="{{ $clinic->id }}" @selected(in_array($clinic->id, old('clinic_ids', [])))>{{ $clinic->name }} — {{ $clinic->city }}</option>
                        @endforeach
                    </select>
                    <p class="text-xs text-gray-500">Mac-da birnäçe klinikany saýlamak üçin Command düwmesini basyp saklaň.</p>
                </div>

                <div id="doctor-fields" class="hidden grid grid-cols-1 gap-4 sm:col-span-2 sm:grid-cols-2">
                    <div><label class="mb-1 block text-xs font-semibold">Hünär ugry</label>
                        <select name="specialization_id" class="w-full rounded-xl border border-gray-200 bg-gray-50 px-4 py-3 text-sm dark:border-gray-700 dark:bg-gray-900">
                            <option value="">Hünär saýlaň</option>
                            @foreach($specializations as $specialization)
                            <option value="{{ $specialization->id }}" @selected(old('specialization_id') == $specialization->id)>{{ $specialization->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div><label class="mb-1 block text-xs font-semibold">Tejribe (ýyl)</label><input type="number" min="0" max="70" name="experience_years" value="{{ old('experience_years', 0) }}" class="w-full rounded-xl border border-gray-200 bg-gray-50 px-4 py-3 text-sm dark:bg-gray-900"></div>
                    <div><label class="mb-1 block text-xs font-semibold">Konsultasiýa bahasy</label><input type="number" min="0" step="0.01" name="consultation_fee" value="{{ old('consultation_fee', 0) }}" class="w-full rounded-xl border border-gray-200 bg-gray-50 px-4 py-3 text-sm dark:bg-gray-900"></div>
                    <div><label class="mb-1 block text-xs font-semibold">Walýuta</label><input type="text" maxlength="3" name="currency" value="{{ old('currency', 'TMT') }}" class="w-full rounded-xl border border-gray-200 bg-gray-50 px-4 py-3 text-sm uppercase dark:bg-gray-900"></div>
                    <div class="sm:col-span-2"><label class="mb-1 block text-xs font-semibold">Bilimi</label><input name="education" value="{{ old('education') }}" class="w-full rounded-xl border border-gray-200 bg-gray-50 px-4 py-3 text-sm dark:bg-gray-900"></div>
                    <div class="sm:col-span-2"><label class="mb-1 block text-xs font-semibold">Terjimehaly</label><textarea name="biography" rows="3" class="w-full rounded-xl border border-gray-200 bg-gray-50 px-4 py-3 text-sm dark:bg-gray-900">{{ old('biography') }}</textarea></div>
                </div>

                <div class="space-y-1.5 sm:col-span-2">
                    <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300">Açar sözi (Password)</label>
                    <input type="password" name="password" required placeholder="Iň az 8 simwol bolmaly"
                        class="w-full px-4 py-3 rounded-xl border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900 text-gray-900 dark:text-white text-sm focus:outline-none focus:ring-2 focus:ring-blue-500" />
                </div>

                <div class="sm:col-span-2 flex items-center gap-3 pt-2">
                    <input type="checkbox" name="is_active" id="is_active" value="1" {{ old('is_active', true) ? 'checked' : '' }}
                        class="w-5 h-5 rounded border-gray-300 text-blue-600 focus:ring-blue-500" />
                    <label for="is_active" class="text-sm font-medium text-gray-700 dark:text-gray-300 cursor-pointer">Hasaby aktiw ýagdaýda döretmek</label>
                </div>
            </div>

            <div class="flex items-center justify-end gap-3 pt-4 border-t border-gray-200 dark:border-gray-700">
                <a href="{{ route('admin.users.index') }}" class="px-5 py-2.5 rounded-xl border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 text-sm hover:bg-gray-100 dark:hover:bg-gray-700 transition">
                    Ýatyr
                </a>
                <button type="submit" class="px-6 py-2.5 rounded-xl bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold shadow-sm transition flex items-center gap-2">
                    <i class="ti ti-device-floppy"></i> Ulanyjyny Ýatda Sakla
                </button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
    function toggleRoleFields() {
        const role = document.querySelector('select[name="role"]')?.value;
        const doctorFields = document.getElementById('doctor-fields');
        const clinicFields = document.getElementById('clinic-assignment');
        doctorFields?.classList.toggle('hidden', role !== 'doctor');
        clinicFields?.classList.toggle('hidden', !['doctor', 'receptionist'].includes(role));
        doctorFields?.querySelectorAll('input,select,textarea').forEach(el => el.disabled = role !== 'doctor');
        clinicFields?.querySelectorAll('select').forEach(el => el.disabled = !['doctor', 'receptionist'].includes(role));
    }
    document.addEventListener('DOMContentLoaded', () => {
        document.querySelector('select[name="role"]')?.addEventListener('change', toggleRoleFields);
        toggleRoleFields();
    });

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