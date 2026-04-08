<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class LoginController extends Controller
{
    /**
     * Show login form
     */
    public function show()
    {
        return view('auth.login');
    }

    /**
     * Handle login request (both web form and API)
     */
    public function login(Request $request)
    {
        $validated = $request->validate([
            'email' => 'required|string',
            'password' => 'required',
        ]);

        // Try to login with email or student_id (exclude soft deleted users)
        $user = User::withoutTrashed()
            ->where('email', $validated['email'])
            ->orWhere('student_id', $validated['email'])
            ->first();

        if (!$user || !Hash::check($validated['password'], $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.'],
            ]);
        }

        // Log the user in for session
        Auth::login($user, true);

        // Create token for the user
        $token = $user->createToken('auth_token')->plainTextToken;

        // Return appropriate response based on request type
        if ($request->wantsJson()) {
            return response()->json([
                'message' => 'Login successful',
                'user' => $user,
                'token' => $token,
                'role' => $user->role,
            ]);
        }

        // Redirect to dashboard
        return redirect()->route('dashboard')->with('success', 'Login successful');
    }

    /**
     * Handle logout request
     */
    public function logout(Request $request)
    {
        // Revoke all tokens
        $request->user()->tokens()->delete();

        // Log out user
        Auth::logout();

        if ($request->wantsJson()) {
            return response()->json(['message' => 'Logged out successfully']);
        }

        return redirect()->route('landing')->with('success', 'Logged out successfully');
    }

    /**
     * Get current user
     */
    public function user(Request $request)
    {
        return response()->json($request->user());
    }
}
