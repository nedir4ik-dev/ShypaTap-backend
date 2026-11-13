# ŞypaTap — Backend

## Talaplar
PHP 8.3+, Composer, PostgreSQL 14+.

## Gurmak
1. `composer install`
2. `.env.example` faýlyny `.env` diýip göçüriň.
3. PostgreSQL-de `shypatap` atly baza dörediň we `.env` maglumatlaryny sazlaň.
4. `php artisan key:generate`
5. `php artisan migrate` — **migrate:fresh ýa-da db:wipe ulanmaň**.
6. `php artisan storage:link`
7. `php artisan serve`

## API
Frontend `.env` faýlynda `VITE_API_URL=http://127.0.0.1:8000/api` ulanyň.
Default timezone: `Asia/Ashgabat`; default currency: `TMT`.

## Queue / Scheduler
Worker: `php artisan queue:work`
Cron: `* * * * * cd /path/to/backend && php artisan schedule:run >> /dev/null 2>&1`

Demo admin hasaby awtomatik döredilmeýär. Ilkinji administrator hasabyny howpsuz deployment prosedurasy bilen dörediň.
