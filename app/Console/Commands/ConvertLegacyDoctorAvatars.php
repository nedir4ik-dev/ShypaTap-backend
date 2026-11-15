<?php

namespace App\Console\Commands;

use App\Models\DoctorProfile;
use App\Services\AvatarService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use Throwable;

class ConvertLegacyDoctorAvatars extends Command
{
    protected $signature = 'avatars:convert-legacy-doctor-photos';
    protected $description = 'Öňki lukman suratlaryny WebP avatar ulgamyna geçirýär.';

    public function handle(AvatarService $avatars): int
    {
        $converted = 0;
        $skipped = 0;
        $failed = 0;

        DoctorProfile::query()
            ->whereNotNull('photo_path')
            ->with('user')
            ->chunkById(100, function ($doctors) use ($avatars, &$converted, &$skipped, &$failed) {
                foreach ($doctors as $doctor) {
                    $user = $doctor->user;
                    $legacyPath = $doctor->photo_path;

                    if (! $user || ! $legacyPath || $user->avatar_path !== $legacyPath) {
                        $skipped++;
                        continue;
                    }

                    if (strtolower(pathinfo($legacyPath, PATHINFO_EXTENSION)) === 'webp') {
                        $skipped++;
                        continue;
                    }

                    try {
                        $disk = Storage::disk('public');
                        if (! $disk->exists($legacyPath)) {
                            $this->warn("Faýl tapylmady: {$legacyPath}");
                            $skipped++;
                            continue;
                        }

                        $avatars->replaceContents($user, $disk->get($legacyPath), false);
                        $converted++;
                    } catch (Throwable $exception) {
                        $failed++;
                        $this->error("{$legacyPath}: {$exception->getMessage()}");
                    }
                }
            });

        $this->info("WebP-e öwrüldi: {$converted}; geçirilmedi: {$skipped}; ýalňyşlyk: {$failed}");

        return $failed > 0 ? self::FAILURE : self::SUCCESS;
    }
}
