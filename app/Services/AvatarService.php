<?php

namespace App\Services;

use App\Models\DoctorProfile;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use RuntimeException;

class AvatarService
{
    public function replace(User $user, UploadedFile $file): void
    {
        $this->replaceContents($user, file_get_contents($file->getRealPath()));
    }

    public function replaceContents(User $user, string $contents, bool $deletePrevious = true): void
    {
        if (! function_exists('imagecreatefromstring') || ! function_exists('imagewebp')) {
            throw new RuntimeException('Avatar üçin PHP GD we WebP goldawy gerek. PHP GD extension-i işlediň.');
        }

        $imageInfo = @getimagesizefromstring($contents);
        if (! $imageInfo || ($imageInfo[0] * $imageInfo[1]) > 16000000) {
            throw ValidationException::withMessages([
                'avatar' => 'Suratyň ölçegi nädogry ýa-da örän uly.',
            ]);
        }

        $source = @imagecreatefromstring($contents);
        if (! $source) {
            throw ValidationException::withMessages([
                'avatar' => 'Suraty okap bolmady. JPG ýa-da PNG faýly saýlaň.',
            ]);
        }

        $width = imagesx($source);
        $height = imagesy($source);
        if ($width < 1 || $height < 1) {
            imagedestroy($source);
            throw ValidationException::withMessages(['avatar' => 'Suratyň ölçegi nädogry.']);
        }

        $scale = min(1, 512 / max($width, $height));
        $newWidth = max(1, (int) round($width * $scale));
        $newHeight = max(1, (int) round($height * $scale));
        $canvas = imagecreatetruecolor($newWidth, $newHeight);
        imagealphablending($canvas, false);
        imagesavealpha($canvas, true);
        $transparent = imagecolorallocatealpha($canvas, 0, 0, 0, 127);
        imagefill($canvas, 0, 0, $transparent);
        imagecopyresampled($canvas, $source, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);

        ob_start();
        $encoded = imagewebp($canvas, null, 85);
        $webp = ob_get_clean();

        imagedestroy($source);
        imagedestroy($canvas);

        if (! $encoded || ! is_string($webp) || $webp === '') {
            throw new RuntimeException('Suraty WebP görnüşine öwrüp bolmady.');
        }

        $path = 'avatars/'.$user->id.'/'.Str::uuid().'.webp';
        if (! Storage::disk('public')->put($path, $webp)) {
            throw new RuntimeException('Suraty serwerde saklap bolmady.');
        }

        $oldPath = $user->avatar_path;
        $legacyPath = DoctorProfile::where('user_id', $user->id)->value('photo_path');
        $user->forceFill(['avatar_path' => $path])->save();

        if ($deletePrevious) {
            foreach (array_unique(array_filter([$oldPath, $legacyPath])) as $previousPath) {
                if ($previousPath !== $path) {
                    Storage::disk('public')->delete($previousPath);
                }
            }

            if ($legacyPath) {
                DoctorProfile::where('user_id', $user->id)
                    ->where('photo_path', $legacyPath)
                    ->update(['photo_path' => null]);
            }
        }
    }

    public function remove(User $user): void
    {
        $oldPath = $user->avatar_path;
        $legacyPath = DoctorProfile::where('user_id', $user->id)->value('photo_path');
        $user->forceFill(['avatar_path' => null])->save();

        foreach (array_unique(array_filter([$oldPath, $legacyPath])) as $previousPath) {
            Storage::disk('public')->delete($previousPath);
        }

        if ($legacyPath) {
            DoctorProfile::where('user_id', $user->id)
                ->where('photo_path', $legacyPath)
                ->update(['photo_path' => null]);
        }
    }
}
