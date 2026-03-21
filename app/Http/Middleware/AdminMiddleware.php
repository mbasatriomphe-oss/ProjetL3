<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AdminMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        // Vérifier si l'utilisateur est authentifié avec sanctum
        if (!Auth::guard('sanctum')->check()) {
            return response()->json([
                'status_code' => 401,
                'status_message' => 'Non authentifié',
                'data' => null
            ], 401);
        }

        $user = Auth::guard('sanctum')->user();
        
        // Vérifier si l'utilisateur a le rôle admin
        if (!$user->isAdmin()) {
            return response()->json([
                'status_code' => 403,
                'status_message' => 'Accès interdit. Vous devez être administrateur.',
                'data' => null
            ], 403);
        }

        return $next($request);
    }
}