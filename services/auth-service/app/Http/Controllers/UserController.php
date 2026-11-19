<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class UserController extends Controller
{
    public function index()
    {
        return User::orderBy('name')->paginate(20);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'unique:users,email'],
            'password' => ['required', 'confirmed', Password::min(8)],
            'role' => ['required', Rule::in([User::ROLE_ADMIN, User::ROLE_AGENT, User::ROLE_RESPONSABLE])],
        ]);

        $user = User::create($data);

        return response()->json($user->only(['id', 'name', 'email', 'role']), 201);
    }

    public function show(User $user)
    {
        return $user->only(['id', 'name', 'email', 'role', 'created_at']);
    }

    public function update(Request $request, User $user)
    {
        $data = $request->validate([
            'name' => ['sometimes', 'string', 'max:255'],
            'email' => ['sometimes', 'email', Rule::unique('users', 'email')->ignore($user->id)],
            'password' => ['sometimes', 'confirmed', Password::min(8)],
            'role' => ['sometimes', Rule::in([User::ROLE_ADMIN, User::ROLE_AGENT, User::ROLE_RESPONSABLE])],
        ]);

        $user->update($data);

        return $user->only(['id', 'name', 'email', 'role']);
    }

    public function destroy(User $user)
    {
        if ($user->id === auth()->id()) {
            return response()->json(['message' => 'Vous ne pouvez pas supprimer votre propre compte.'], 422);
        }

        $user->delete();

        return response()->json(null, 204);
    }
}
