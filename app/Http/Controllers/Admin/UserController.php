<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class UserController extends Controller
{
    public function index(Request $request): View
    {
        $q = User::query()->where('is_admin', false);
        if ($request->filled('q')) {
            $s = $request->string('q');
            $q->where(function ($q2) use ($s) {
                $q2->where('name', 'like', '%'.$s.'%')
                    ->orWhere('email', 'like', '%'.$s.'%');
            });
        }
        $users = $q->latest()->paginate(30)->withQueryString();

        return view('admin.users.index', compact('users'));
    }

    public function toggleBlock(User $user): RedirectResponse
    {
        if ($user->is_admin) {
            abort(403);
        }
        $user->update(['is_blocked' => ! $user->is_blocked]);

        return back()->with('status', 'Đã cập nhật người dùng.');
    }
}
