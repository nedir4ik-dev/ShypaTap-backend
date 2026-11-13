<?php
namespace App\Http\Controllers\Api;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Notification;
class NotificationController extends Controller {
 public function index(Request $request) {
  return response()->json(['unread_count'=>$request->user()->notifications()->whereNull('read_at')->count(),
   'data'=>$request->user()->notifications()->latest()->paginate(20)]);
 }
 public function read(Request $request,int $id) {
  $n=$request->user()->notifications()->findOrFail($id); $n->update(['read_at'=>now()]);
  return response()->json(['message'=>'Bildiriş okalan hökmünde bellendi.','data'=>$n]);
 }
 public function readAll(Request $request) {
  $request->user()->notifications()->whereNull('read_at')->update(['read_at'=>now()]);
  return response()->json(['message'=>'Ähli bildirişler okalan hökmünde bellendi.']);
 }
}
