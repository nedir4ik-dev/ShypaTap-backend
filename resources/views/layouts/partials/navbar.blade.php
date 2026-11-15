<header class="sticky top-0 z-30 bg-linear-to-r from-white/40 via-white/20 to-white/30 dark:from-gray-900/40
dark:via-gray-800/25 dark:to-gray-900/30 backdrop-blur-sm border border-white/50 dark:border-white/10 w-full
mt-5 mb-2 mx-auto px-6 py-3 flex justify-between items-center rounded-[200px] transition-all duration-300 
shadow-[0_8px_32px_0_rgba(0,0,0,0.08)] dark:shadow-[0_8px_32px_0_rgba(0,0,0,0.37)] ring-1 ring-white/20 
dark:ring-white/5">

    <div class="flex items-center gap-3">
        <button onclick="toggleSidebar()" class="md:hidden pt-2 pb-1 px-2 rounded-full bg-white/40 dark:bg-gray-800/40 text-gray-800 dark:text-gray-100 hover:bg-white/60 dark:hover:bg-gray-700/50 backdrop-blur-md border border-white/40 dark:border-white/10 transition shadow-sm focus:outline-none">
            <i class="ti ti-menu-2 text-xl"></i>
        </button>

        <div class="text-lg font-semibold text-gray-900 dark:text-white drop-shadow-sm">
            @yield('header', 'Dolandyryş Paneli')
        </div>
    </div>

    <div class="flex items-center gap-4">
        <button onclick="toggleDarkMode()"
            class="pt-2 pb-1 px-2 rounded-4xl bg-white/40 dark:bg-gray-800/40 text-gray-800 dark:text-gray-100 hover:bg-white/60 dark:hover:bg-gray-700/50 backdrop-blur-md border border-white/40 dark:border-white/10 transition shadow-sm focus:outline-none"
            title="Rejimi Üýtget">
            <i id="theme-icon" class="ti ti-moon text-xl"></i>
        </button>

        <a href="{{ route('admin.dashboard') }}" class="hidden md:flex items-center gap-2.5 rounded-full px-2 py-1 text-sm font-medium transition text-gray-800 dark:text-gray-100 bg-white/30 dark:bg-gray-800/30 hover:bg-white/50 dark:hover:bg-white/10 backdrop-blur-md border border-white/40 dark:border-white/10 shadow-sm" title="Şahsy profil">
            @if (auth()->user()?->avatar_url)
            <img src="{{ auth()->user()->avatar_url }}" alt="Profil" class="h-9 w-9 rounded-full object-cover ring-2 ring-white/50 dark:ring-white/10">
            @else
            <span class="flex h-9 w-9 items-center justify-center rounded-full bg-emerald-500/20 font-bold text-emerald-800 dark:text-emerald-300 ring-2 ring-white/50 dark:ring-white/10">{{\Illuminate\Support\Str::upper(\Illuminate\Support\Str::substr(auth()->user()->name ?? 'A', 0, 1))}}</span>
            @endif
            <span class="hidden sm:inline font-medium">{{ auth()->user()->name ?? 'Admin' }}</span>
        </a>
    </div>
</header>