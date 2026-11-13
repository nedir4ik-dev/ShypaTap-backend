<aside id="app-sidebar" class="fixed inset-y-0 left-0 z-50 w-70 bg-white/20 dark:bg-slate-900/20 backdrop-blur-sm border border-gray-200
 dark:border-slate-800 rounded-[50px] flex flex-col justify-between p-5 transition-transform duration-300 transform -translate-x-full md:translate-x-0 
 md:static md:h-screen shadow-2xl m-0 md:m-2">
    <div>
        {{-- Logotip / Başlyk --}}
        <div class="flex items-center justify-between px-3 my-2">
            <a href="{{ route('admin.dashboard') }}" class="text-2xl font-extrabold text-center uppercase text-blue-600 dark:text-blue-400 no-underline flex items-center gap-2">
                ŞypaTap
            </a>
            <!-- Mobil üçin ýapmak (X) düwmesi -->
            <button onclick="toggleSidebar()" class="md:hidden text-gray-500 dark:text-slate-400 hover:text-gray-900 dark:hover:text-white p-1">
                <i class="ti ti-x text-xl"></i>
            </button>
        </div>

        <hr class="border-gray-200 dark:border-slate-800 my-4">

        {{-- Menýu Sanawy --}}
        <nav class="space-y-1.5">
            <a href="{{ route('admin.dashboard') }}" class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-sm font-medium transition-all {{ request()->routeIs('admin.dashboard') ? 'bg-blue-600 text-white shadow-lg shadow-blue-500/30 font-semibold' : 'text-gray-700 dark:text-slate-300 hover:bg-gray-100 dark:hover:bg-white/10' }}">
                <i class="ti ti-chart-arrows text-lg"></i>
                Dashboard
            </a>
            <a href="{{ route('admin.doctors.index') }}" class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-sm font-medium transition-all {{ request()->routeIs('admin.doctors*') ? 'bg-blue-600 text-white shadow-lg shadow-blue-500/30 font-semibold' : 'text-gray-700 dark:text-slate-300 hover:bg-gray-100 dark:hover:bg-white/10' }}">
                <i class="ti ti-stethoscope text-lg"></i>
                Lukmanlar
            </a>
            <a href="{{ route('admin.clinics.index') }}" class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-sm font-medium transition-all {{ request()->routeIs('admin.clinics*') ? 'bg-blue-600 text-white shadow-lg shadow-blue-500/30 font-semibold' : 'text-gray-700 dark:text-slate-300 hover:bg-gray-100 dark:hover:bg-white/10' }}">
                <i class="ti ti-building-hospital text-lg"></i>
                Klinikalar
            </a>
            <a href="{{ route('admin.specializations.index') }}" class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-sm font-medium transition-all {{ request()->routeIs('admin.specializations*') ? 'bg-blue-600 text-white shadow-lg shadow-blue-500/30 font-semibold' : 'text-gray-700 dark:text-slate-300 hover:bg-gray-100 dark:hover:bg-white/10' }}">
                <i class="ti ti-award text-lg"></i>
                Hünärler
            </a>
            <a href="{{ route('admin.users.index') }}" class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-sm font-medium transition-all {{ request()->routeIs('admin.users*') ? 'bg-blue-600 text-white shadow-lg shadow-blue-500/30 font-semibold' : 'text-gray-700 dark:text-slate-300 hover:bg-gray-100 dark:hover:bg-white/10' }}">
                <i class="ti ti-users text-lg"></i>
                Ulanyjylar
            </a>
        </nav>
    </div>

    {{-- Aşaky Bölek: Profil we Logout --}}
    <div class="space-y-3">

        <div class="flex items-center gap-3 px-3 py-2.5 rounded-2xl bg-gray-100 dark:bg-white/5 border border-gray-200 dark:border-white/5">
            <div class="w-9 h-9 rounded-xl bg-blue-600/20 text-blue-600 dark:text-blue-400 flex items-center justify-center font-bold text-sm border border-blue-500/30">
                {{ strtoupper(substr(auth()->user()->name ?? 'A', 0, 1)) }}
            </div>
            <div class="overflow-hidden">
                <div class="text-xs text-gray-500 dark:text-slate-400">Giren ulanyjy:</div>
                <div class="text-sm font-semibold text-gray-800 dark:text-slate-200 truncate">
                    {{ auth()->user()->name ?? 'ŞypaTap Admin' }}
                </div>
            </div>
        </div>

        <hr class="border-gray-200 dark:border-slate-800 my-2">

        <form action="{{ route('logout') }}" method="POST">
            @csrf
            <button type="submit" class="w-full flex items-center justify-center gap-2 px-4 py-2.5 border border-red-500/30 text-red-600 dark:text-red-400 hover:bg-red-500/10 hover:border-red-500 rounded-xl text-sm font-medium transition-all shadow-sm">
                <i class="ti ti-logout text-lg"></i>
                Ulgamdan Çyk
            </button>
        </form>
    </div>
</aside>