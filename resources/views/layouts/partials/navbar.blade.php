<header class="sticky top-0 z-30 bg-white/20 dark:bg-gray-800/25 backdrop-blur-sm border border-gray-200/80 
dark:border-gray-400/20 w-full mt-5 mb-2 mx-auto px-6 py-3 flex justify-between items-center rounded-[200px] transition-colors duration-200 shadow-lg">

    <div class="flex items-center gap-3">
        <button onclick="toggleSidebar()" class="md:hidden pt-2 pb-1 px-2 rounded-full bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-200 hover:bg-gray-200 dark:hover:bg-gray-600 transition focus:outline-none">
            <i class="ti ti-menu-2 text-xl"></i>
        </button>

        <div class="text-lg font-semibold text-gray-800 dark:text-white">
            @yield('header', 'Dolandyryş Paneli')
        </div>
    </div>

    <div class="flex items-center gap-4">
        <a href="{{ route('admin.dashboard') }}" class="flex items-center gap-2 rounded-full px-2 py-1 text-sm font-medium text-gray-700 hover:bg-gray-100 dark:text-gray-200 dark:hover:bg-white/10" title="Şahsy profil">
            @if (auth()->user()?->avatar_url)
            <img src="{{ auth()->user()->avatar_url }}" alt="Profil" class="h-9 w-9 rounded-full object-cover">
            @else
            <span class="flex h-9 w-9 items-center justify-center rounded-full bg-emerald-100 font-bold text-emerald-800">{{ mb_strtoupper(mb_substr(auth()->user()->name ?? 'A', 0, 1)) }}</span>
            @endif
            <span class="hidden sm:inline">{{ auth()->user()->name ?? 'Admin' }}</span>
        </a>
        <button onclick="toggleDarkMode()"
            class="pt-2 pb-1 px-2 rounded-4xl bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-200 hover:bg-gray-200 dark:hover:bg-gray-600 transition shadow-sm focus:outline-none"
            title="Rejimi Üýtget">
            <i id="theme-icon" class="ti ti-moon text-xl"></i>
        </button>
    </div>
</header>