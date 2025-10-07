<?php


namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CheckSingleSession
{
    public function handle(Request $request, Closure $next)
    {
        $user = $request->user();
        
        if ($user && $user->login_token !== $request->header('X-Login-Token')) {
            $tokenId = $request->user()->currentAccessToken()->id;

            DB::delete('delete from personal_access_tokens where id = ?', [$tokenId]);
            return response()->json(['message' => 'Logged in from another browser'], 401);
        }

        return $next($request);
    }
}