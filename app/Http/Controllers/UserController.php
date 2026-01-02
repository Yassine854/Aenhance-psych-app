<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    // List all users (Admin)
    public function index()
    {
        return User::all();
    }

    // Create a new user (Admin)
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required',
            'email' => 'required|email|unique:users',
            'password' => 'required|min:6',
            'role' => 'required|in:ADMIN,PSYCHOLOGIST,PATIENT',
        ]);

        return User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => $request->role,
        ]);
    }

    // Update a user (Admin)
    public function update(Request $request, User $user)
    {
        $validated = $request->validate([
            'name' => ['required','string','max:255'],
            'email' => ['required','email', Rule::unique('users')->ignore($user->id)],
            'password' => ['nullable','string','min:6'],
        ]);

        $data = [
            'name' => $validated['name'],
            'email' => $validated['email'],
        ];

        if (!empty($validated['password'])) {
            $data['password'] = Hash::make($validated['password']);
        }

        $user->update($data);

        return response()->noContent();
    }

    // Deactivate user (Admin)
    public function deactivate(User $user)
    {
        $user->update(['is_active' => 0]);
        return response()->json(['message' => 'User deactivated', 'is_active' => 0]);
    }

    // Activate user (Admin)
    public function activate(User $user)
    {
        $user->update(['is_active' => 1]);
        return response()->json(['message' => 'User activated', 'is_active' => 1]);
    }
}
