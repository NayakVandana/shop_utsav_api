<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Controller;
use App\Models\LoginToken;
use Carbon\Carbon;

class CheckAdmin extends Controller
{
    public function handle($request, Closure $next)
    {
        if (Auth::user()->role !== 'ADMIN') {
           return $this->sendJsonResponse(false, 'Unauthorized');
        }
         $token = $request->bearerToken() ?? $request->get('Authorization');

        if (!$token) {
            return $this->sendJsonResponse(false, 'Unauthorized');
        }

        $userToken = LoginToken::where('token', $token)
            ->where('expires_at', '>=', Carbon::now())
            ->first();

        if (!$userToken) {
             return $this->sendJsonResponse(false, 'Unauthorized');
        }

        return $next($request);
    }
}