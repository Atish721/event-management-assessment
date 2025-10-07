<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        $request->validate([
            'username' => 'required|string',
            'password' => 'required|string',
            'timezone' => 'required|string'
        ]);

        
        $user = DB::selectOne('
            select id, username, password, timezone, login_token, last_login_at 
            from users 
            where username = ?', 
            [$request->username]
        );

        if (!$user || !Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'username' => ['Credentials are incorrect.'],
            ]);
        }

        
        $newLoginToken = Str::random(60);
        
        
        DB::delete('
            delete from personal_access_tokens 
            where tokenable_id = ? and tokenable_type = ?', 
            [$user->id, 'App\Models\User']
        );
        
        
        $plainTextToken = Str::random(40);
        $hashedToken = hash('sha256', $plainTextToken);
        
        DB::insert('
            insert into personal_access_tokens 
            (tokenable_type, tokenable_id, name, token, abilities, created_at, updated_at) 
            values (?, ?, ?, ?, ?, ?, ?)',
            [
                'App\Models\User',
                $user->id,
                'auth-token',
                $hashedToken,
                '["*"]',
                now(),
                now()
            ]
        );

        $tokenId = DB::getPdo()->lastInsertId();

        
        DB::update('
            update users 
            SET login_token = ?, timezone = ?, last_login_at = ?, updated_at = ? 
            where id = ?',
            [
                $newLoginToken,
                $request->timezone,
                now(),
                now(),
                $user->id
            ]
        );

        
        $updatedUser = DB::selectOne('
            select id, username, timezone 
            from users 
            where id = ?', 
            [$user->id]
        );

        return response()->json([
            'user' => $updatedUser,
            'token' => $tokenId . '|' . $plainTextToken,
            'login_token' => $newLoginToken,
        ]);
    }

    public function logout(Request $request)
    {
        $authHeader = $request->header('Authorization');
        
        if (str_starts_with($authHeader, 'Bearer ')) {
            $token = substr($authHeader, 7);
            $tokenParts = explode('|', $token);
            
            if (count($tokenParts) === 2) {
                $tokenId = $tokenParts[0];
                
                DB::delete('delete from personal_access_tokens where id = ?', [$tokenId]);
            }
        }
        
        return response()->json(['message' => 'Logged out successfully']);
    }

    public function checkAuth(Request $request)
    {
        $authHeader = $request->header('Authorization');
        
        if (!str_starts_with($authHeader, 'Bearer ')) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        $token = substr($authHeader, 7);
        $tokenParts = explode('|', $token);
        
        if (count($tokenParts) !== 2) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        $tokenId = $tokenParts[0];
        $plainTextToken = $tokenParts[1];
        $hashedToken = hash('sha256', $plainTextToken);

        
        $tokenRecord = DB::selectOne('
            select pat.*, u.id as user_id, u.username, u.timezone, u.login_token 
            from personal_access_tokens pat 
            inner join users u on pat.tokenable_id = u.id 
            where pat.id = ? and pat.token = ? and pat.tokenable_type = ?',
            [$tokenId, $hashedToken, 'App\Models\User']
        );

        if (!$tokenRecord) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        
        if ($tokenRecord->login_token !== $request->header('X-Login-Token')) {
            
            DB::delete('
                delete from personal_access_tokens 
                where tokenable_id = ? and tokenable_type = ?',
                [$tokenRecord->user_id, 'App\Models\User']
            );
            
            return response()->json(['message' => 'Logged in from another browser'], 401);
        }

        return response()->json([
            'user' => [
                'id' => $tokenRecord->user_id,
                'username' => $tokenRecord->username,
                'timezone' => $tokenRecord->timezone,
            ]
        ]);
    }
}