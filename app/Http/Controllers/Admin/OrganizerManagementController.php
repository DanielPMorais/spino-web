<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rules\Password;
use Inertia\Inertia;
use Inertia\Response;

class OrganizerManagementController extends Controller
{
    public function index(): Response
    {
        $organizers = User::query()
            ->where('role', 'organizer')
            ->orderBy('name')
            ->get(['id', 'name', 'email', 'created_at'])
            ->map(fn (User $user) => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'createdAt' => $user->created_at?->toIso8601String(),
            ]);

        return Inertia::render('Admin/Organizers/Index', compact('organizers'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'confirmed', Password::min(12)],
        ]);

        User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => $data['password'],
            'role' => 'organizer',
            'email_verified_at' => now(),
        ]);

        return to_route('admin.organizers.index')->with('success', 'Organizador criado. Compartilhe a senha inicial por um canal seguro.');
    }
}
