<?php
namespace App\Services;
use App\Models\Role;
use App\Models\User;
use Illuminate\Support\Facades\DB;
class RoleAssignmentService
{
    public function assignDefault(User $user): void
    {
        $slug = strtolower((string)$user->role);
        if ($slug === 'super_admin') $slug = 'superadmin';
        $roleId = Role::where('slug',$slug)->value('id');
        if (!$roleId) return;
        DB::table('user_roles')->updateOrInsert(
            ['user_id'=>$user->id,'role_id'=>$roleId,'clinic_id'=>null],
            ['created_at'=>now(),'updated_at'=>now()]
        );
    }
}
