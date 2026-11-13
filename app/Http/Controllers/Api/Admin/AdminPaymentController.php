<?php
namespace App\Http\Controllers\Api\Admin;
use App\Http\Controllers\Controller;use App\Models\Payment;use Illuminate\Http\Request;use Illuminate\Support\Facades\DB;
class AdminPaymentController extends Controller {
 public function index(){return response()->json(Payment::with(['appointment.patient','appointment.doctor.user','appointment.clinic'])->latest()->paginate(20));}
 public function show(Payment $payment){return response()->json(['data'=>$payment->load('appointment')]);}
 public function update(Request $r,Payment $payment){
  $v=$r->validate(['status'=>'required|in:pending,paid,refunded,cancelled','payment_method'=>'sometimes|in:cash','paid_at'=>'nullable|date']);
  if($v['status']==='paid' && !$payment->paid_at)$v['paid_at']=now();
  if($v['status']!=='paid')$v['paid_at']=null;
  $payment->update($v);return response()->json(['message'=>'Töleg ýagdaýy täzelendi.','data'=>$payment]);
 }
}
