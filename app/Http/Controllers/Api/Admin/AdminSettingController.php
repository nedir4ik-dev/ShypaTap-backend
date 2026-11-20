<?php
namespace App\Http\Controllers\Api\Admin;
use App\Http\Controllers\Controller;use App\Models\Setting;use Illuminate\Http\Request;
class AdminSettingController extends Controller {
 public function index(){return response()->json(Setting::orderBy('key')->get()->pluck('value','key'));}
 public function update(Request $r){
  $v=$r->validate(['timezone'=>'sometimes|string|max:80','currency'=>'sometimes|string|size:3','cancellation_hours'=>'sometimes|integer|min:0|max:168','default_slot_duration'=>'sometimes|integer|min:5|max:240','system_name'=>'sometimes|string|max:100','contact_email'=>'sometimes|nullable|email|max:255','contact_phone'=>'sometimes|nullable|string|max:30','maintenance_mode'=>'sometimes|boolean']);
  foreach($v as $key=>$value)Setting::updateOrCreate(['key'=>$key],['value'=>is_bool($value) ? ($value ? '1' : '0') : (string)$value]);
  return response()->json(['message'=>'Ulgam sazlamalary saklandy.','data'=>Setting::orderBy('key')->get()->pluck('value','key')]);
 }
}
