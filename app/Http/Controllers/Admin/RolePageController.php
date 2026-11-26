<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\{Permission,Role,User,Clinic};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class RolePageController extends Controller
{
    public function index()
    {
        return view('admin.roles.index',['roles'=>Role::with('permissions')->orderBy('name')->get(),'permissions'=>Permission::orderBy('group')->orderBy('name')->get()]);
    }
    public function store(Request $request)
    {
        $data=$request->validate(['name'=>'required|string|max:80|unique:roles,name','permissions'=>'array','permissions.*'=>'integer|exists:permissions,id']);
        $this->assertGrantable($request,$data['permissions']??[]);
        $slug=Str::slug($data['name']);
        abort_if(Role::where('slug',$slug)->exists(),422,'Bu rol ady eýýäm ulanylýar.');
        DB::transaction(function()use($data,$slug){$role=Role::create(['name'=>$data['name'],'slug'=>$slug,'is_system'=>false]);$role->permissions()->sync($data['permissions']??[]);});
        return back()->with('success','Täze rol döredildi.');
    }
    public function update(Request $request, Role $role)
    {
        abort_if($role->is_system || in_array($role->slug,['superadmin','admin'],true),403,'Ulgam rollaryny üýtgedip bolmaýar.');
        $data=$request->validate(['name'=>'required|string|max:80|unique:roles,name,'.$role->id,'permissions'=>'array','permissions.*'=>'integer|exists:permissions,id']);
        $this->assertGrantable($request,$data['permissions']??[]);
        DB::transaction(function()use($role,$data){$role->update(['name'=>$data['name']]);$role->permissions()->sync($data['permissions']??[]);});
        return back()->with('success','Rol täzelendi.');
    }
    public function destroy(Request $request, Role $role)
    {
        abort_if($role->is_system || in_array($role->slug,['superadmin','admin'],true),403,'Ulgam rollaryny pozup bolmaýar.');
        $role->delete(); return back()->with('success','Rol pozuldy.');
    }
    public function storePermission(Request $request)
    {
        abort_unless(in_array(strtolower((string)$request->user()->role),['superadmin','super_admin'],true),403,'Täze rugsat döretmek diňe SuperAdmin üçin elýeterlidir.');
        $data=$request->validate(['name'=>'required|string|max:120|regex:/^[a-z][a-z0-9_]*(\.[a-z][a-z0-9_]*)+$/|unique:permissions,name','group'=>'required|string|max:80','description'=>'nullable|string|max:255']);
        Permission::create($data);
        return back()->with('success','Täze rugsat döredildi.');
    }

    public function assignUser(Request $request, User $user)
    {
        $this->assertUserScope($request,$user);
        abort_if(in_array(strtolower((string)$user->role),['superadmin','super_admin'],true),403,'SuperAdmin roluny üýtgedip bolmaýar.');
        $data=$request->validate(['role_id'=>'required|integer|exists:roles,id','clinic_id'=>'nullable|integer|exists:clinics,id']);
        $role=Role::with('permissions')->findOrFail($data['role_id']);
        abort_if($role->slug==='superadmin',403,'SuperAdmin hukugyny berip bolmaýar.');
        $this->assertGrantable($request,$role->permissions->pluck('id')->all());
        if(!in_array(strtolower((string)$request->user()->role),['superadmin','super_admin'],true)) {
            abort_unless(!empty($data['clinic_id']) && $request->user()->belongsToClinic((int)$data['clinic_id']),403,'Admin diňe öz klinikasyna rol belläp biler.');
        }
        if(!empty($data['clinic_id'])) abort_unless($request->user()->role==='superadmin' || $request->user()->belongsToClinic((int)$data['clinic_id']),403);
        DB::transaction(function()use($user,$data){
            DB::table('user_roles')->where('user_id',$user->id)->delete();
            DB::table('user_roles')->insert(['user_id'=>$user->id,'role_id'=>$data['role_id'],'clinic_id'=>$data['clinic_id']??null,'created_at'=>now(),'updated_at'=>now()]);
        });
        return back()->with('success','Ulanyjynyň roly we klinika çägi täzelendi.');
    }

    public function assignOverrides(Request $request, User $user)
    {
        $this->assertUserScope($request,$user);
        abort_if(in_array(strtolower((string)$user->role),['superadmin','super_admin'],true),403,'SuperAdmin rugsatlaryny üýtgedip bolmaýar.');
        $data=$request->validate(['permission_id'=>'required|integer|exists:permissions,id','effect'=>'required|in:allow,deny','clinic_id'=>'nullable|integer|exists:clinics,id']);
        $this->assertGrantable($request,[$data['permission_id']]);
        if(!in_array(strtolower((string)$request->user()->role),['superadmin','super_admin'],true)) abort_unless(!empty($data['clinic_id']) && $request->user()->belongsToClinic((int)$data['clinic_id']),403,'Admin diňe öz klinikasy üçin rugsat üýtgedip biler.');
        DB::table('user_permission_overrides')->updateOrInsert(
            ['user_id'=>$user->id,'permission_id'=>$data['permission_id'],'clinic_id'=>$data['clinic_id']??null],
            ['effect'=>$data['effect'],'created_at'=>now(),'updated_at'=>now()]
        );
        return back()->with('success','Ulanyjy üçin aýratyn rugsat üýtgedildi.');
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

    private function assertGrantable(Request $request,array $ids): void
    {
        if(in_array(strtolower((string)$request->user()->role),['superadmin','super_admin'],true))return;
        foreach(Permission::whereIn('id',$ids)->pluck('name') as $name) abort_unless($request->user()->hasPermission($name),403,'Özüňizde bolmadyk rugsady berip bilmersiňiz: '.$name);
    }
}
