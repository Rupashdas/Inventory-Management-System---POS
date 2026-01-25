<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Helper\JWTToken;

class TokenVerificationMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response 
    {
        $token = $request->cookie('reset_token');
        $result = JWTToken::verifyToken($token);
        if($result === "unauthorized"){
            return response()->json([
                'message' => 'Unauthorized access',
                'status' => "failed",
            ], 401);
        }else{
            $request->headers->set('userEmail', $result);
            return $next($request);
        }
    }
}
