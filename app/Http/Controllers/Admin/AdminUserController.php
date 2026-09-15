<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

class AdminUserController extends Controller
{
    /**
     * Display a listing of all registered users.
     */
    public function index(Request $request)
    {
        $query = User::withCount('cvs');

        if ($request->filled('role')) {
            $query->where('role', $request->role);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        $users = $query->latest()->paginate(15)->withQueryString();

        return view('admin.users.index', compact('users'));
    }

    /**
     * Toggle a user's role between admin and user.
     */
    public function toggleRole(Request $request, User $user)
    {
        if ($user->id === auth()->id()) {
            return back()->with('error', 'You cannot change your own admin role.');
        }

        $user->role = ($user->role === 'admin') ? 'user' : 'admin';
        $user->save();

        return back()->with('success', "Role for {$user->name} updated to {$user->role}.");
    }
}
