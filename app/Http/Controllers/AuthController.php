<?php

namespace App\Http\Controllers;

use App\Models\User;
use Dotenv\Exception\ValidationException;
use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Events\Verified;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Response;
use Illuminate\Validation\ValidationException as ValidationValidationException;




class AuthController extends Controller
{
    public function allUsers()
    {
        return response()->json(User::all());
    }
    /**
     * Register a new user
     */
    public function register(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'phone' => 'sometimes|string|max:20',
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'phone' => $request->phone,
            'role' => 'user', // Only users can register, admins are created manually
        ]);

        // Send email verification
        event(new Registered($user));

        return response()->json([
            'message' => 'User registered successfully. Please verify your email before logging in.',
            'user' => $user->only(['id', 'name', 'email', 'role']),
        ]);
    }

    /**
     * Login user (only verified users and admins)
     */
  public function login(Request $request)
{
    $request->validate([
        'email' => 'required|email',
        'password' => 'required',
    ]);

    $user = User::where('email', $request->email)->first();

    if (!$user || !Hash::check($request->password, $user->password)) {
        throw ValidationValidationException::withMessages([
            'email' => ['The provided credentials are incorrect.'],
        ]);
    }

    // Check if user is admin (admins don't need email verification)
    if ($user->isUser() && !$user->hasVerifiedEmail()) {
        return response()->json([
            'message' => 'Please verify your email before logging in.',
        ], 403); // Add status code for unverified users
    }

    $token = $user->createToken('auth_token')->plainTextToken;

    return response()->json([
        'message' => 'Login successful',
        'user' => [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'role' => $user->role,
            'email_verified_at' => $user->email_verified_at, // Add this line
            'is_verified' => $user->hasVerifiedEmail(), // Add this line for convenience
        ],
        'access_token' => $token,
        'token_type' => 'Bearer',
    ]);
}

    /**
     * Logout user
     */
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'message' => 'Logged out successfully'
        ]);
    }

    /**
     * Get authenticated user
     */
    public function me(Request $request)
    {
        return response()->json([
            'user' => $request->user()
        ]);
    }

    /**
     * Resend email verification
     */
    public function resendVerification(Request $request)
    {
        $request->validate([
            'email' => 'required|email|exists:users,email'
        ]);

        $user = User::where('email', $request->email)->first();

        if ($user->hasVerifiedEmail()) {
            return response()->json([
                'message' => 'Email already verified.'
            ]);
        }

        $user->sendEmailVerificationNotification();

        return response()->json([
            'message' => 'Verification email sent successfully.'
        ]);
    }

    /**
     * Verify email
     */
    public function verifyEmail(Request $request)
{
    $user = User::where('id', $request->route('id'))->first();

    if (!$user) {
        return response()->json([
            'message' => 'User not found.'
        ]);
    }

    if ($user->hasVerifiedEmail()) {
        return response()->json([
            'message' => 'Email already verified.'
        ]);
    }

    if ($user->markEmailAsVerified()) {
        event(new Verified($user));
    }

    return response()->json([
        'message' => 'Email verified successfully.'
    ]);
}

}
