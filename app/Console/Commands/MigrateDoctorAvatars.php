<?php
namespace App\Console\Commands;
use App\Models\DoctorProfile;
use App\Services\AvatarService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
class MigrateDoctorAvatars extends Command
{
    protected $signature='shypatap:migrate-doctor-avatars';
    protected $description='Köne lukman suratlaryny users.avatar_path meýdanyna WebP görnüşinde howpsuz geçirýär.';
    public function handle(AvatarService $avatars): int
    {
        $migrated=0;$skipped=0;
        DoctorProfile::with('user')->whereNotNull('photo_path')->orderBy('id')->chunkById(100,function($profiles)use($avatars,&$migrated,&$skipped){
            foreach($profiles as $profile){
                $user=$profile->user;$oldPath=$profile->photo_path;
                if(!$user){$skipped++;continue;}
                try{
                    if(!$user->avatar_path){
                        if(!Storage::disk('public')->exists($oldPath)){$skipped++;continue;}
                        $avatars->replaceContents($user,Storage::disk('public')->get($oldPath),false);
                    }
                    $profile->update(['photo_path'=>null]);
                    if($oldPath && $oldPath!==$user->fresh()->avatar_path)Storage::disk('public')->delete($oldPath);
                    $migrated++;
                }catch(\Throwable $e){report($e);$skipped++;}
            }
        });
        $this->info("Göçürildi: {$migrated}; geçirilmedi: {$skipped}.");
        return self::SUCCESS;
    }
}
