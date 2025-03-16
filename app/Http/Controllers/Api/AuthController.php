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
    
    /**
     * @OA\Post(
     *     path="/api/login",
     *     summary="User Login",
     *     description="Login a user and return a bearer token.",
     *     operationId="loginUser",
     *     tags={"Authentication"},
     *     requestBody={
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *                 type="object",
     *                 required={"email", "password"},
     *                 @OA\Property(property="email", type="string", example="user@example.com"),
     *                 @OA\Property(property="password", type="string", example="password123")
     *             )
     *         )
     *     },
     *     responses={
     *         @OA\Response(
     *             response=200,
     *             description="Login successful",
     *             @OA\JsonContent(
     *                 type="object",
     *                 @OA\Property(property="message", type="string", example="Login Successful"),
     *                 @OA\Property(property="type", type="string", example="Bearer"),
     *                 @OA\Property(property="token", type="string", example="your-access-token")
     *             )
     *         ),
     *         @OA\Response(
     *             response=401,
     *             description="Unauthorized - Invalid credentials",
     *             @OA\JsonContent(
     *                 type="object",
     *                 @OA\Property(property="message", type="string", example="User credentials do not match")
     *             )
     *         )
     *     }
     * )
     */
    public function login(createLoginRequest $request) :jsonResponse{
        $user = User::where('email',$request->email)->first();
        if(!$user || !Hash::check($request->password, $user->password)){
            return response()->json([ 'message' => 'User credentials do not match',], 401);
        }
        $token = $user->createToken($user->name.'-Auth-token')->plainTextToken;
        return response()->json(['message' => 'Login Successful','type' => 'Bearer','token' => $token], 200);
    }

    /**
        * @OA\Post(
        *     path="/api/register",
        *     operationId="registerUser",
        *     tags={"Register"},
        *     summary="Register a new user",
        *     description="User Registration Endpoint",
        *     @OA\RequestBody(
        *         @OA\JsonContent(),
        *         @OA\MediaType(
        *             mediaType="multipart/form-data",
        *             @OA\Schema(
        *                 type="object",
        *                 required={"name","email","password","password_confirmation"},
        *                 @OA\Property(property="name",type="text"),
        *                 @OA\Property(property="email",type="text"),
        *                 @OA\Property(property="password",type="password"),
        *                 @OA\Property(property="password_confirmation",type="password"),
        *             ),
        *         ),
        *     ),
        *     @OA\Response(
        *         response="201",
        *         description="User Registered Successfully",
        *         @OA\JsonContent()
        *     ),
        *     @OA\Response(
        *       response="200",
        *       description="Registered Successfull",
        *       @OA\JsonContent()
        *     ),
        *     @OA\Response(
        *         response="422",
        *         description="Unprocessable Entity",
        *         @OA\JsonContent()
        *     ),
        *     @OA\Response(
        *         response="400",
        *         description="Bad Request",
        *         @OA\JsonContent()
        *     ),
        * )
        */
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

    /**
     * @OA\Post(
     *     path="/api/logout",
     *     summary="User Logout",
     *     description="Logout the user and invalidate their access token.",
     *     operationId="logoutUser",
     *     tags={"Authentication"},
     *     security={{"sanctum": {}}},
     *     responses={
     *         @OA\Response(
     *             response=200,
     *             description="Successfully logged out",
     *             @OA\JsonContent(
     *                 type="object",
     *                 @OA\Property(property="message", type="string", example="Logged Out")
     *             )
     *         ),
     *         @OA\Response(
     *             response=401,
     *             description="Unauthorized - Invalid credentials",
     *             @OA\JsonContent(
     *                 type="object",
     *                 @OA\Property(property="message", type="string", example="Unauthenticated")
     *             )
     *         )
     *     }
     * )
     */
    public function logout(Request $request){
            if(is_numeric(auth()->user()->tokens()->delete())){
                return response()->json([ 'message' => 'Logged Out'], 200);
            }
            return response()->json([ 'message' => 'Unauthenticated'], 401);
    }

    /**
     * @OA\Post(
     *     path="/api/reset-password",
     *     summary="Reset User Password",
     *     description="Resets the password for a user using their email. The user must provide their new password.",
     *     operationId="resetPassword",
     *     tags={"Authentication"},
     *     requestBody={
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *                 type="object",
     *                 required={"email", "password"},
     *                 @OA\Property(property="email", type="string", example="user@example.com"),
     *                 @OA\Property(property="password", type="string", example="newPassword123")
     *             )
     *         )
     *     },
     *     responses={
     *         @OA\Response(
     *             response=200,
     *             description="Password reset successfully",
     *             @OA\JsonContent(
     *                 type="object",
     *                 @OA\Property(property="message", type="string", example="Password reset successfully.")
     *             )
     *         ),
     *         @OA\Response(
     *             response=404,
     *             description="User not found",
     *             @OA\JsonContent(
     *                 type="object",
     *                 @OA\Property(property="message", type="string", example="User not found.")
     *             )
     *         ),
     *         @OA\Response(
     *             response=500,
     *             description="Internal Server Error - Password reset failed",
     *             @OA\JsonContent(
     *                 type="object",
     *                 @OA\Property(property="message", type="string", example="An error occurred while resetting the password."),
     *                 @OA\Property(property="error", type="string", example="Error details")
     *             )
     *         )
     *     }
     * )
     */
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
