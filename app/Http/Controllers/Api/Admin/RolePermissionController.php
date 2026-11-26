<?php
namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\{Clinic,Permission,Role,User,UserPermissionOverride};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class RolePermissionController extends Controller
{
    public function index() { return response()->json(['roles'=>Role::with('permissions')->orderBy('name')->get(),'permissions'=>Permission::orderBy('group')->orderBy('name')->get()]); }

    public function store(Request $request)
    {
        $data=$request->validate(['name'=>['required','string','max:80','unique:roles,name'],'slug'=>['required','alpha_dash','max:100','unique:roles,slug'],'permissions'=>['array'],'permissions.*'=>['integer','exists:permissions,id'],'clinic_ids'=>['array'],'clinic_ids.*'=>['integer','exists:clinics,id']]);
        $this->assertGrantable($request->user(),$data['permissions']??[]);
        $role=DB::transaction(function()use($data){$r=Role::create(['name'=>$data['name'],'slug'=>$data['slug'],'is_system'=>false]);$r->permissions()->sync($data['permissions']??[]);$r->clinics()->sync($data['clinic_ids']??[]);return $r;});
        return response()->json(['data'=>$role->load('permissions')],201);
    }

    public function update(Request $request, Role $role)
    {
        abort_if($role->is_system || in_array($role->slug,['superadmin','admin'],true),403,'Ulgam rollaryny üýtgedip bolmaýar.');
        $data=$request->validate(['name'=>['sometimes','required','string','max:80',Rule::unique('roles','name')->ignore($role->id)],'permissions'=>['sometimes','array'],'permissions.*'=>['integer','exists:permissions,id'],'clinic_ids'=>['sometimes','array'],'clinic_ids.*'=>['integer','exists:clinics,id']]);
        $this->assertGrantable($request->user(),$data['permissions']??[]);
        DB::transaction(function()use($role,$data){if(isset($data['name']))$role->update(['name'=>$data['name']]);if(isset($data['permissions']))$role->permissions()->sync($data['permissions']);if(isset($data['clinic_ids']))$role->clinics()->sync($data['clinic_ids']);});
        return response()->json(['data'=>$role->fresh()->load('permissions')]);
    }

    public function destroy(Request $request, Role $role)
    {
        abort_if($role->is_system || in_array($role->slug,['superadmin','admin'],true),403,'Ulgam rollaryny pozup bolmaýar.');
        $role->delete(); return response()->json(['message'=>'Rol pozuldy.']);
    }

    public function assignUserRoles(Request $request, User $user)
    {
        $this->assertUserScope($request,$user);
        abort_if(in_array(strtolower((string)$user->role),['superadmin','super_admin'],true),403,'SuperAdmin roly üýtgedilip bilinmez.');
        $data=$request->validate(['assignments'=>'required|array','assignments.*.role_id'=>'required|integer|exists:roles,id','assignments.*.clinic_id'=>'nullable|integer|exists:clinics,id']);
        $roleIds=collect($data['assignments'])->pluck('role_id')->unique();
        $roles=Role::with('permissions')->whereIn('id',$roleIds)->get();
        abort_if($roles->contains(fn($r)=>$r->slug==='superadmin'),403,'SuperAdmin hukugyny berip bolmaýar.');
        foreach($roles as $role) $this->assertGrantable($request->user(), $role->permissions->pluck('id')->all());
        foreach($data['assignments'] as $a){$clinicId=$a['clinic_id']??null;if($clinicId)abort_unless($request->user()->belongsToClinic((int)$clinicId),403);}
        DB::transaction(function()use($user,$data){
            DB::table('user_roles')->where('user_id',$user->id)->delete();
            foreach($data['assignments'] as $a) DB::table('user_roles')->insert([
                'user_id'=>$user->id,'role_id'=>$a['role_id'],'clinic_id'=>$a['clinic_id']??null,
                'created_at'=>now(),'updated_at'=>now(),
            ]);
        });
        return response()->json(['message'=>'Ulanyjynyň rollary täzelendi.']);
    }

    public function overrides(Request $request, User $user)
    {
        $this->assertUserScope($request,$user);
        abort_if(in_array(strtolower((string)$user->role),['superadmin','super_admin'],true),403);
        $data=$request->validate(['overrides'=>'required|array','overrides.*.permission_id'=>'required|integer|exists:permissions,id','overrides.*.clinic_id'=>'nullable|integer|exists:clinics,id','overrides.*.effect'=>['required',Rule::in(['allow','deny'])]]);
        $this->assertGrantable($request->user(),collect($data['overrides'])->where('effect','allow')->pluck('permission_id')->all());
        foreach($data['overrides'] as $o){if(!empty($o['clinic_id']))abort_unless($request->user()->belongsToClinic((int)$o['clinic_id']),403);}
        DB::transaction(function()use($user,$data){foreach($data['overrides'] as $o)UserPermissionOverride::updateOrCreate(['user_id'=>$user->id,'permission_id'=>$o['permission_id'],'clinic_id'=>$o['clinic_id']??null],['effect'=>$o['effect']]);});
        return response()->json(['message'=>'Ulanyjy rugsatlary täzelendi.']);
    }

    private function assertUserScope(Request $request, User $user): void
    {
        if(in_array(strtolower((string)$request->user()->role),['superadmin','super_admin'],true))return;
        $ids=$request->user()->staffClinics()->pluck('clinics.id');
        $allowed=$user->staffClinics()->whereIn('clinics.id',$ids)->exists()
            || $user->doctorProfile?->clinics()->whereIn('clinics.id',$ids)->exists()
            || $user->patientAppointments()->whereIn('clinic_id',$ids)->exists();
        abort_unless($allowed,403,'Bu ulanyjy siziň klinikalaryňyza degişli däl.');
    }

    private function assertGrantable(User $actor, array $permissionIds): void
    {
        if(in_array(strtolower((string)$actor->role),['superadmin','super_admin'],true))return;
        $names=Permission::whereIn('id',$permissionIds)->pluck('name');
        foreach($names as $name)abort_unless($actor->hasPermission($name),403,'Özüňizde bolmadyk rugsady berip bilmersiňiz: '.$name);
    }
}
