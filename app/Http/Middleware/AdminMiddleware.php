<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

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
        // Vérifier si l'utilisateur est authentifié
        if (!$request->user()) {
            return response()->json([
                'message' => 'Non authentifié'
            ], 401);
        }
        
        // Vérifier si l'utilisateur a le rôle admin
        if ($request->user()->role !== 'admin') {
            return response()->json([
                'message' => 'Accès non autorisé. Droits administrateur requis.'
            ], 403);
        }
        
        return $next($request);
    }
}