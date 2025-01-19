<?php

namespace App\Http\Controllers;

use App\Http\Requests\Auth\AuthRequest;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\ValidationException;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function register(AuthRequest $request)
    {
        try {

            //by default role will be coach unless provided in the $request
            $validatedData = $request->validated();
            $validatedData['password'] = Hash::make($validatedData['password']);

            $user = User::create($validatedData);

            //TODO: replace it with resource method
            return response()->json(['success' => true, 'message' => 'User Created Successfuly', 'data' => $user], 201);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function login(LoginRequest $request)
    {
        try {
            $validated = $request->validated();

            $user = User::where('email', $validated['email'])->first();

            if (!$user || !Hash::check($validated['password'], $user->password)) {

                RateLimiter::hit($request->throttleKey(), 60);

                return response()->json([
                    'success' => false,
                    'message' => 'Incorrect Email or Password',
                ], 401);
            }

            RateLimiter::clear($request->throttleKey());

            $token = $user->createToken('auth_token')->plainTextToken;

            //TODO: replace it with resource method

            return response()->json([
                'success' => true,
                'token' => $token,
                'data' => $user,
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while logging in'. $e->getMessage()
            ], 500);
        }
    }
}
