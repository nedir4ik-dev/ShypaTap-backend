<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Relations\Pivot;

#[Fillable(['clinic_id', 'user_id'])]
class ClinicStaff extends Pivot
{
    protected $table = 'clinic_staff';


    public $incrementing = true;
}
