<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    /**
     * Get all users
     */
    public function index()
    {
        try {
            $users = User::all();
            return response()->json($users);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to load users'], 500);
        }
    }

    /**
     * Get a specific user
     */
    public function show($id)
    {
        try {
            $user = User::findOrFail($id);
            return response()->json($user);
        } catch (\Exception $e) {
            return response()->json(['error' => 'User not found'], 404);
        }
    }

    /**
     * Create a new user
     */
    public function store(Request $request)
    {
        try {
            // Validate input
            $validated = $request->validate([
                'first_name' => 'required|string|max:255',
                'middle_name' => 'nullable|string|max:255',
                'last_name' => 'required|string|max:255',
                'suffix' => 'nullable|string|max:255',
                'email' => 'required|email|unique:users,email',
                'password' => 'required|string|min:8|confirmed',
                'password_confirmation' => 'required|string',
                'role' => 'required|in:admin,housekeeper',
                'contact_number' => 'nullable|string|max:20',
            ]);

            // Prevent creating users with 'user' role
            if ($validated['role'] === 'user') {
                return response()->json([
                    'errors' => ['role' => ['Cannot create users with the user role']]
                ], 422);
            }

            // Remove password_confirmation from validated data (it's only for validation)
            unset($validated['password_confirmation']);

            // Hash password
            $validated['password'] = Hash::make($validated['password']);

            // Create user
            $user = User::create($validated);

            return response()->json($user, 201);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json(['errors' => $e->errors()], 422);
        } catch (\Exception $e) {
            \Log::error('User creation error: ' . $e->getMessage(), ['exception' => $e, 'request' => $request->all()]);
            return response()->json(['error' => 'Failed to create user: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Update a user
     */
    public function update(Request $request, $id)
    {
        try {
            $user = User::findOrFail($id);

            // Validate input
            $rules = [
                'first_name' => 'required|string|max:255',
                'middle_name' => 'nullable|string|max:255',
                'last_name' => 'required|string|max:255',
                'suffix' => 'nullable|string|max:255',
                'email' => ['required', 'email', Rule::unique('users', 'email')->ignore($id)],
                'role' => 'required|in:admin,housekeeper,user',
                'contact_number' => 'nullable|string|max:20',
            ];

            // Only require password confirmation if password is provided
            if ($request->filled('password')) {
                $rules['password'] = 'required|string|min:8|confirmed';
                $rules['password_confirmation'] = 'required|string';
            }

            $validated = $request->validate($rules);

            // Remove password and password_confirmation if not provided
            if (!$request->filled('password')) {
                unset($validated['password']);
                unset($validated['password_confirmation']);
            } else {
                $validated['password'] = Hash::make($validated['password']);
                unset($validated['password_confirmation']); // Don't store confirmation in database
            }

            // Update user
            $user->update($validated);

            return response()->json($user);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json(['errors' => $e->errors()], 422);
        } catch (\Exception $e) {
            \Log::error('User update error: ' . $e->getMessage(), ['user_id' => $id, 'exception' => $e]);
            return response()->json(['error' => 'Failed to update user: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Delete a user
     */
    public function destroy($id)
    {
        try {
            $user = User::findOrFail($id);

            // Prevent deleting the last admin
            if ($user->role === 'admin') {
                $adminCount = User::where('role', 'admin')->count();
                if ($adminCount <= 1) {
                    return response()->json([
                        'error' => 'Cannot delete the last admin user'
                    ], 422);
                }
            }

            $user->delete();

            return response()->json(['message' => 'User deleted successfully']);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to delete user'], 500);
        }
    }
}
