<?php
namespace App\Http\Controllers\Api;
use App\Http\Controllers\Controller;use App\Models\DoctorSchedule;use App\Models\DoctorDayOff;use Illuminate\Http\Request;
class DoctorScheduleController extends Controller {
 private function profile(Request $r){$p=$r->user()->doctorProfile;abort_unless($p,404,'Lukman profili tapylmady.');return $p;}
 public function index(Request $r){$p=$this->profile($r);return response()->json($p->schedules()->with('clinic')->orderBy('day_of_week')->get());}
 public function store(Request $r){$p=$this->profile($r);$v=$r->validate(['clinic_id'=>'required|exists:clinics,id','day_of_week'=>'required|integer|min:1|max:7','start_time'=>'required|date_format:H:i','end_time'=>'required|date_format:H:i|after:start_time','break_start'=>'nullable|date_format:H:i','break_end'=>'nullable|date_format:H:i|after:break_start','slot_duration_minutes'=>'required|integer|min:5|max:240','is_active'=>'sometimes|boolean']);abort_unless($p->clinics()->where('clinics.id',$v['clinic_id'])->exists(),403);$s=$p->schedules()->create($v);return response()->json(['data'=>$s],201);}
 public function update(Request $r,int $id){$p=$this->profile($r);$s=$p->schedules()->findOrFail($id);$v=$r->validate(['start_time'=>'sometimes|date_format:H:i','end_time'=>'sometimes|date_format:H:i','break_start'=>'nullable|date_format:H:i','break_end'=>'nullable|date_format:H:i','slot_duration_minutes'=>'sometimes|integer|min:5|max:240','is_active'=>'sometimes|boolean']);$s->update($v);return response()->json(['data'=>$s]);}
 public function destroy(Request $r,int $id){$this->profile($r)->schedules()->findOrFail($id)->delete();return response()->json(['message'=>'Iş tertibi pozuldy.']);}
 public function daysOff(Request $r){return response()->json($this->profile($r)->daysOff()->with('clinic')->orderBy('off_date')->get());}
 public function addDayOff(Request $r){$p=$this->profile($r);$v=$r->validate(['clinic_id'=>'required|exists:clinics,id','off_date'=>'required|date|after_or_equal:today','reason'=>'nullable|string|max:255']);abort_unless($p->clinics()->where('clinics.id',$v['clinic_id'])->exists(),403);$d=$p->daysOff()->create($v);return response()->json(['data'=>$d],201);}
 public function deleteDayOff(Request $r,int $id){$this->profile($r)->daysOff()->findOrFail($id)->delete();return response()->json(['message'=>'Dynç güni pozuldy.']);}
}
