<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

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

    // Deactivate user (Admin)
    public function deactivate(User $user)
    {
        $user->update(['is_active' => false]);
        return response()->json(['message' => 'User deactivated']);
    }

    // Activate user (Admin)
    public function activate(User $user)
    {
        $user->update(['is_active' => true]);
        return response()->json(['message' => 'User activated']);
    }
}
