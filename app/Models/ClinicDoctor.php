<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Relations\Pivot;

#[Fillable(['clinic_id', 'doctor_profile_id'])]
class ClinicDoctor extends Pivot
{
    protected $table = 'clinic_doctors';

    public $incrementing = true;
}
