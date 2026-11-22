@php
$user = auth()->user();
$initial = mb_strtoupper(mb_substr($user?->name ?? 'A', 0, 1));

$navItems = [
['route' => 'admin.dashboard', 'active' => 'admin.dashboard', 'icon' => 'ti-layout-dashboard', 'label' => 'Dashboard'],
['route' => 'admin.doctors.index', 'active' => 'admin.doctors*', 'icon' => 'ti-stethoscope', 'label' => 'Lukmanlar'],
['route' => 'admin.clinics.index', 'active' => 'admin.clinics*', 'icon' => 'ti-building-hospital', 'label' => 'Klinikalar'],
['route' => 'admin.specializations.index', 'active' => 'admin.specializations*', 'icon' => 'ti-award', 'label' => 'Hünärler'],
['route' => 'admin.appointments.index', 'active' => 'admin.appointments*', 'icon' => 'ti-calendar-event', 'label' => 'Bron edilmeler'],
['route' => 'admin.users.index', 'active' => 'admin.users*', 'icon' => 'ti-users', 'label' => 'Ulanyjylar'],
['route' => 'admin.payments.index', 'active' => 'admin.payments*', 'icon' => 'ti-cash', 'label' => 'Tölegler'],
['route' => 'admin.reports.index', 'active' => 'admin.reports*', 'icon' => 'ti-chart-bar', 'label' => 'Hasabatlar'],
['route' => 'admin.permissions.index', 'active' => 'admin.permissions*', 'icon' => 'ti-shield-check', 'label' => 'Rugsatlar'],
['route' => 'admin.reviews.index', 'active' => 'admin.reviews*', 'icon' => 'ti-star', 'label' => 'Bahalandyrmalar'],
['route' => 'admin.settings.edit', 'active' => 'admin.settings*', 'icon' => 'ti-settings', 'label' => 'Sazlamalar'],
];

$navLink = 'flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-medium transition-all';
$navIdle = 'text-slate-600 hover:bg-white/60 hover:text-slate-950 dark:text-slate-300 dark:hover:bg-slate-800/60 dark:hover:text-white';
$navActive = 'bg-blue-600 text-white shadow-xl shadow-blue-600/25 ring-1 ring-white/25 backdrop-blur-md';
@endphp

<aside id="app-sidebar"
    class="fixed inset-y-0 left-0 z-50 flex w-72 -translate-x-full shrink-0 flex-col justify-between rounded-r-3xl 
    border border-white/60 bg-white/35 p-4 shadow-xl shadow-slate-900/10 ring-1 ring-slate-200/70 backdrop-blur-sm 
    transition-transform duration-300 dark:border-white/10 dark:bg-slate-900/35 dark:shadow-black/30 
    dark:ring-slate-700/60 md:sticky md:top-3 md:my-3 md:ml-3 md:h-[calc(100vh-1.5rem)] md:translate-x-0 md:rounded-3xl">
    <div class="min-h-0 flex-1">
        <div class="flex items-center justify-between px-2 py-1.5">
            <a href="{{ route('admin.dashboard') }}"
                class="flex items-center gap-2 text-xl font-extrabold tracking-tight text-blue-700 no-underline dark:text-blue-400">
                <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-2xl bg-linear-to-br from-blue-500 to-blue-700 text-white shadow-lg shadow-blue-600/25 ring-1 ring-white/30">
                    <i class="ti ti-heart-rate-monitor text-2xl leading-none"></i>
                </span>
                <span>ŞypaTap</span>
            </a>

            <button type="button" onclick="toggleSidebar()" aria-label="Menýuny ýap"
                class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full border border-slate-200/80 bg-white/20 text-slate-600 transition hover:bg-white/80 dark:border-slate-700/70 dark:bg-slate-800/30 dark:text-slate-300 dark:hover:bg-slate-800 md:hidden">
                <i class="ti ti-x text-xl leading-none"></i>
            </button>
        </div>

        <div class="my-3 border-t border-slate-200/70 dark:border-slate-700/60"></div>

        <nav class="max-h-[calc(100vh-13rem)] space-y-1 overflow-y-auto pr-1">
            @foreach ($navItems as $item)
            @php($isActive = request()->routeIs($item['active']))
            <a href="{{ route($item['route']) }}" class="{{ $navLink }} {{ $isActive ? $navActive : $navIdle }}">
                <span class="flex h-5 w-5 shrink-0 items-center justify-center">
                    <i class="ti {{ $item['icon'] }} text-lg leading-none"></i>
                </span>
                <span class="truncate">{{ $item['label'] }}</span>
            </a>
            @endforeach
        </nav>
    </div>

    <div class="mt-4 space-y-3">
        <div class="flex min-w-0 items-center gap-3 rounded-2xl border border-white/60 bg-white/35 px-3 py-2.5 shadow-sm backdrop-blur-sm dark:border-white/10 dark:bg-slate-800/55">
            @if ($user?->avatar_url)
            <img src="{{ $user->avatar_url }}" alt="Profil suraty" class="h-10 w-10 shrink-0 rounded-xl object-cover ring-1 ring-white/40">
            @else
            <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-blue-500/15 text-sm font-bold leading-none text-blue-700 ring-1 ring-blue-500/20 dark:bg-blue-400/15 dark:text-blue-300">
                {{ $initial }}
            </span>
            @endif
            <div class="min-w-0">
                <div class="text-xs text-slate-500 dark:text-slate-400">Giren ulanyjy:</div>
                <div class="truncate text-sm font-semibold text-slate-800 dark:text-slate-100">{{ $user?->name ?? 'ŞypaTap Admin' }}</div>
            </div>
        </div>

        <form action="{{ route('logout') }}" method="POST">
            @csrf
            <button type="submit"
                class="flex w-full items-center justify-center gap-2 rounded-xl border border-red-200/80 bg-red-500/10 px-4 py-2.5 text-sm font-semibold text-red-600 backdrop-blur-md transition hover:border-red-300 hover:bg-red-500/15 dark:border-red-500/30 dark:bg-red-500/10 dark:text-red-400 dark:hover:bg-red-500/20">
                <span class="flex h-5 w-5 shrink-0 items-center justify-center">
                    <i class="ti ti-logout text-lg leading-none"></i>
                </span>
                <span>Ulgamdan çyk</span>
            </button>
        </form>
    </div>
</aside>