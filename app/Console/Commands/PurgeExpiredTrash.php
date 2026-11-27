<?php
namespace App\Console\Commands;

use App\Models\Appointment;
use App\Models\Clinic;
use App\Models\DoctorProfile;
use App\Models\Review;
use App\Models\User;
use Illuminate\Console\Command;

class PurgeExpiredTrash extends Command
{
    protected $signature = 'shypatap:purge-trash';
    protected $description = '90 günden köne Trash ýazgylaryny hemişelik pozýar (töleg taryhyny saklaýar).';

    public function handle(): int
    {
        $cutoff = now()->subDays(90);
        $models = [Review::class, Appointment::class, DoctorProfile::class, Clinic::class, User::class];
        foreach ($models as $model) {
            $model::onlyTrashed()->where('deleted_at','<=',$cutoff)->orderBy('id')->chunkById(100, function ($rows) use ($model) {
                foreach ($rows as $row) {
                    // Appointment FK restricts deletion while a payment exists; this preserves financial history.
                    if ($model === Appointment::class && $row->payment()->exists()) continue;
                    try { $row->forceDelete(); } catch (\Throwable $e) { report($e); }
                }
            });
        }
        return self::SUCCESS;
    }
}
