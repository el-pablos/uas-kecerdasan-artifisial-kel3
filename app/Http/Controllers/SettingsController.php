<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\ActivityLog;
use App\Models\Tag;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class SettingsController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function users()
    {
        $users = User::with('roles')->orderBy('name')->paginate(25);
        $roles = Role::all();
        return view('cti.settings.users', compact('users', 'roles'));
    }

    public function tokens()
    {
        $user = auth()->user();
        $tokens = $user->tokens()->orderBy('created_at', 'desc')->get();
        return view('cti.settings.tokens', compact('tokens'));
    }

    public function createToken(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
        ]);

        $token = auth()->user()->createToken($validated['name']);
        activity_log('create', 'token', null, "Created API token: {$validated['name']}");

        return back()->with('new_token', $token->plainTextToken)
            ->with('success', 'API token created. Copy it now — it won\'t be shown again.');
    }

    public function revokeToken(Request $request, $tokenId)
    {
        auth()->user()->tokens()->where('id', $tokenId)->delete();
        return back()->with('success', 'Token revoked.');
    }

    public function taxonomy()
    {
        $tags = Tag::withCount('taggables')->orderBy('name')->paginate(50);
        return view('cti.settings.taxonomy', compact('tags'));
    }

    public function tagStore(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:50|unique:tags,name',
            'color' => 'nullable|string|max:7',
        ]);

        Tag::create($validated);
        return back()->with('success', 'Label created.');
    }

    public function tagDestroy(Tag $tag)
    {
        $tag->taggables()->delete();
        $tag->delete();
        return back()->with('success', 'Label deleted.');
    }

    public function audit(Request $request)
    {
        $query = ActivityLog::with('user')->orderBy('created_at', 'desc');

        if ($request->filled('action')) {
            $query->where('action', $request->action);
        }
        if ($request->filled('search')) {
            $query->where('description', 'like', "%{$request->search}%");
        }

        $logs = $query->paginate(50)->withQueryString();
        return view('cti.settings.audit', compact('logs'));
    }
}
