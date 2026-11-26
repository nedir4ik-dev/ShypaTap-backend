<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Review;
use Illuminate\Http\Request;

class AdminReviewController extends Controller
{
    public function index(Request $request)
    {
        $clinicIds = in_array(strtolower((string)$request->user()->role), ['superadmin','super_admin'], true) ? \App\Models\Clinic::pluck('id') : $request->user()->staffClinics()->pluck('clinics.id');
        $reviews = Review::whereHas('appointment',fn($q)=>$q->whereIn('clinic_id',$clinicIds))->with([
            'patient:id,name,email',
            'doctor.user:id,name',
            'appointment:id,booking_code,appointment_date,status',
        ])->latest()->paginate(25);

        if ($request->expectsJson() || $request->is('api/*')) {
            return response()->json($reviews);
        }

        return view('admin.reviews.index', compact('reviews'));
    }

    public function destroy(Request $request, Review $review)
    {
        abort_unless($request->user()->belongsToClinic((int)$review->appointment?->clinic_id),403);
        $review->delete();

        if ($request->expectsJson() || $request->is('api/*')) {
            return response()->json(['message' => 'Review pozuldy.']);
        }

        return back()->with('success', 'Bahalandyrma pozuldy.');
    }
}
