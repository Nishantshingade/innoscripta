<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\jsonResponse;
use App\Traits\CreateModelTrait;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Validation\ValidationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Str;
use App\Http\Requests\createLoginRequest;
use App\Http\Requests\createRegisterRequest;
use App\Http\Requests\createResetpasswordRequest;
use App\Models\User;

class AuthController extends Controller
{
    use CreateModelTrait;

    public function login(createLoginRequest $request) :jsonResponse{
        $user = User::where('email',$request->email)->first();
        if(!$user || !Hash::check($request->password, $user->password)){
            return response()->json([ 'message' => 'User credentials do not match',], 401);
        }
        $token = $user->createToken($user->name.'-Auth-token')->plainTextToken;
        return response()->json(['message' => 'Login Successful','type' => 'Bearer','token' => $token], 200);
    }

    Public function register(createRegisterRequest $request) :jsonResponse{
        try {
            $validated = $request->validated();
            $validated['password'] = Hash::make($validated['password']);
            $user = $this->createModelRecord(User::class, $validated);
            if ($user) {
                $token = $user->createToken($user->name . '-Auth-token')->plainTextToken;
                return response()->json(['message' => 'Registration Successful','type' => 'Bearer','token' => $token,], 201);
            }
        } catch (\Exception $e) {
            \Log::error('User registration failed: ' . $e->getMessage());
            return response()->json(['message' => 'An error occurred during registration.','error' => $e->getMessage()], 500);
        }
    }

    public function logout(Request $request){
            if(is_numeric(auth()->user()->tokens()->delete())){
                return response()->json([ 'message' => 'Logged Out'], 200);
            }
            return response()->json([ 'message' => 'Unauthenticated'], 404);
    }

    public function reset(createResetpasswordRequest $request) :JsonResponse{
        try {
            $user = User::where('email', $request->email)->firstOrFail();
            $user->password = Hash::make($request->password);
            $user->save();
            return response()->json(['message' => 'Password reset successfully.'], 200);
        }catch (ModelNotFoundException $e) {
            return response()->json(['message' => 'User not found.',], 404);
        } catch (\Exception $e) {
            \Log::error('Password reset failed: ' . $e->getMessage());
            return response()->json(['message' => 'An error occurred while resetting the password.','error' => $e->getMessage()], 500);
        }
    }
        
}
