<?php

namespace App\Http\Controllers\Api;

use App\User;
use Validator;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class AuthController extends Controller
{
    //https://jwt-auth.readthedocs.io/en/develop/quick-start/
    
    /**
     * Get a JWT via given credentials.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function login(Request $request)
    {
        $credentials = $request->only(['email', 'password']);

        if (! $token = auth('api')->attempt($credentials)) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        return $this->respondWithToken($token);
    }

    /**
     * Get the authenticated User.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function me()
    {
        return response()->json(auth('api')->user());
    }

    /**
     * Log the user out (Invalidate the token).
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function logout()
    {
        auth('api')->logout();

        return response()->json(['message' => 'Successfully logged out']);
    }

    /**
     * Refresh a token.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function refresh()
    {
        return $this->respondWithToken(auth('api')->refresh());
    }

    /**
     * Get the token array structure.
     *
     * @param  string $token
     *
     * @return \Illuminate\Http\JsonResponse
     */
    protected function respondWithToken($token)
    {
        return response()->json([
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => auth('api')->factory()->getTTL() * 60
        ]);
    }

    /**
     * Registrar user
     *
     * @return  \Illuminate\Http\Request  $request
     */
    public function register(Request $request)
    {
        // Rules 
        $rules = [
            'name'        => 'required|min:2|max:120',
            'email'       => 'required|min:2|max:120||unique:users',
            'password'    => 'required|min:6|max:8',
        ];
    
        // Validator
        $validator = Validator::make($request->all() , $rules);

        if ($validator->fails()) {
            return response()->json(formatMessage(404, $validator->messages()), 404);
            die;
        }

        // Create User
        $user = User::create([
            'name'     => $request->name,
            'email'    => $request->email,
            'password' => $request->password,
         ]);

        $token = auth()->login($user);

        // return $this->respondWithToken($token));
        return response()->json(formatMessage(201, $this->respondWithToken($token)), 201);
    }
}
