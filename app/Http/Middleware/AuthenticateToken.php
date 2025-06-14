<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Http\Controllers\Controller;
use App\Models\LoginToken;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class AuthenticateToken extends Controller
{
    public function handle($request, Closure $next)
    {
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

        Auth::login($userToken->user);

        return $next($request);
    }
}
