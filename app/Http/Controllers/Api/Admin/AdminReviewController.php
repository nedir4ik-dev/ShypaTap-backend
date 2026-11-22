<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Review;
use Illuminate\Http\Request;

class AdminReviewController extends Controller
{
    public function index(Request $request)
    {
        $query = $request->boolean('archived') ? Review::onlyTrashed() : Review::query();
        $reviews = $query->with([
            'patient:id,name,email',
            'doctor.user:id,name',
            'appointment:id,booking_code,appointment_date,status',
        ])->latest()->paginate(25);

        if ($request->expectsJson() || $request->is('api/*')) {
            return response()->json($reviews);
        }

        return view('admin.reviews.index', compact('reviews'));
    }

    public function restore(Request $request, int $id)
    {
        $review = Review::onlyTrashed()->findOrFail($id);
        $review->restore();
        if ($request->expectsJson() || $request->is('api/*')) {
            return response()->json(['message' => 'Review dikeldildi.', 'data' => $review]);
        }
        return back()->with('success', 'Bahalandyrma dikeldildi.');
    }

    public function destroy(Request $request, Review $review)
    {
        $review->delete();

        if ($request->expectsJson() || $request->is('api/*')) {
            return response()->json(['message' => 'Review arhiwe geçirildi.']);
        }

        return back()->with('success', 'Bahalandyrma arhiwe geçirildi.');
    }
}
