<!DOCTYPE html>
<html lang="tk" class="h-full">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ŞypaTap - Ulgama Giriş</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="h-full bg-gray-50 dark:bg-gray-900 flex items-center justify-center p-4 transition-colors duration-200">

    <div class="w-full max-w-md bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-2xl shadow-xl p-8 transition-colors">

        <div class="text-center mb-8">
            <h1 class="text-3xl font-extrabold text-blue-600 dark:text-blue-400 mb-2">ŞypaTap</h1>
            <p class="text-sm text-gray-500 dark:text-gray-400">Ulgama girmek üçin maglumatlaryňyzy giriziň</p>
        </div>

        @if ($errors->any())
        <div class="mb-6 bg-red-50 dark:bg-red-500/10 border border-red-200 dark:border-red-500/30 text-red-700 dark:text-red-400 px-4 py-3 rounded-xl text-sm flex items-center gap-2">
            <i class="ti ti-alert-circle text-lg shrink-0"></i>
            <span>{{ $errors->first() }}</span>
        </div>
        @endif

        <form action="{{ route('login') }}" method="POST" class="space-y-5">
            @csrf

            <div>
                <label class="block text-xs font-semibold uppercase tracking-wider text-gray-700 dark:text-gray-300 mb-2">Email Salgyňyz</label>
                <div class="relative">
                    <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-gray-400">
                        <i class="ti ti-mail text-lg"></i>
                    </span>
                    <input
                        type="email"
                        name="email"
                        value="{{ old('email') }}"
                        required
                        autofocus
                        class="w-full pl-10 pr-4 py-2.5 bg-gray-50 dark:bg-gray-900 border border-gray-300 dark:border-gray-700 rounded-xl text-gray-900 dark:text-gray-100 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-blue-500 transition text-sm"
                        placeholder="admin@example.com">
                </div>
            </div>

            <div>
                <label class="block text-xs font-semibold uppercase tracking-wider text-gray-700 dark:text-gray-300 mb-2">Parolyňyz</label>
                <div class="relative">
                    <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-gray-400">
                        <i class="ti ti-lock text-lg"></i>
                    </span>
                    <input
                        type="password"
                        name="password"
                        required
                        class="w-full pl-10 pr-4 py-2.5 bg-gray-50 dark:bg-gray-900 border border-gray-300 dark:border-gray-700 rounded-xl text-gray-900 dark:text-gray-100 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-blue-500 transition text-sm"
                        placeholder="••••••••">
                </div>
            </div>

            <button type="submit" class="w-full mt-2 bg-blue-600 hover:bg-blue-700 text-white font-semibold py-3 px-4 rounded-xl shadow-lg shadow-blue-500/25 transition duration-200 flex items-center justify-center gap-2">
                <i class="ti ti-login text-xl"></i>
                Giriş Et
            </button>
        </form>

    </div>

</body>

</html>
