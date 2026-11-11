<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = $request->user();

        if (!$user) {
            return response()->json(['message' => 'Ulgama girmek gerek.'], 401);
        }

        if (!$user->is_active) {
            return response()->json(['message' => 'Hasabyňyz işjeň däl.'], 403);
        }

        if (!in_array($user->role, $roles, true)) {
            return response()->json(['message' => 'Bu amaly ýerine ýetirmäge rugsadyňyz ýok.'], 403);
        }

        return $next($request);
    }
}
