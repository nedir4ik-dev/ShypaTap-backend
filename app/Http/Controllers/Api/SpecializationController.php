<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Specialization;
use Illuminate\Http\JsonResponse;

class SpecializationController extends Controller
{
    public function index(): JsonResponse
    {
        $specializations = Specialization::where('is_active', true)->withCount('doctors')->get();
        return response()->json($specializations);
    }
}
