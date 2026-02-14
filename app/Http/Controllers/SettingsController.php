<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\ActivityLog;
use App\Models\Tag;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
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

    /**
     * Assign role to user.
     */
    public function assignRole(Request $request, User $user)
    {
        $validated = $request->validate([
            'role' => 'required|string|exists:roles,name',
        ]);

        $user->syncRoles([$validated['role']]);
        activity_log('update', 'user', $user->id, "Assigned role '{$validated['role']}' to {$user->name}");

        return back()->with('success', "Role '{$validated['role']}' assigned to {$user->name}.");
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

    /**
     * System diagnostics page — helps debug "UI not changing" issues.
     */
    public function diagnostics()
    {
        // Check critical tables
        $tableNames = ['nodes', 'edges', 'cases', 'case_tasks', 'case_items', 'tags', 'taggables', 'activity_logs', 'integrations'];
        $tables = [];
        foreach ($tableNames as $t) {
            $exists = Schema::hasTable($t);
            $tables[$t] = [
                'exists' => $exists,
                'count'  => $exists ? \DB::table($t)->count() : 0,
            ];
        }

        // Quick nav links
        $links = [
            'Dashboard & Analysis' => [
                ['label' => 'CTI Dashboard', 'url' => route('cti.dashboard'), 'route' => '/cti', 'icon' => 'ri-dashboard-2-line'],
                ['label' => 'Threat Actors', 'url' => route('threats.actors.index'), 'route' => '/threats/actors', 'icon' => 'ri-skull-line'],
                ['label' => 'Knowledge Entities', 'url' => route('knowledge.entities.index'), 'route' => '/knowledge/entities', 'icon' => 'ri-mind-map'],
                ['label' => 'Graph Explorer', 'url' => route('knowledge.graph'), 'route' => '/knowledge/graph', 'icon' => 'ri-share-line'],
            ],
            'Events & Cases' => [
                ['label' => 'Observations', 'url' => route('observations.index'), 'route' => '/observations', 'icon' => 'ri-radar-line'],
                ['label' => 'Cases / Incidents', 'url' => route('cases.incidents.index'), 'route' => '/cases/incidents', 'icon' => 'ri-folder-shield-2-line'],
                ['label' => 'Investigations', 'url' => route('investigations.index'), 'route' => '/investigations', 'icon' => 'ri-search-eye-line'],
            ],
            'Data & Settings' => [
                ['label' => 'Connectors', 'url' => route('ingestion.connectors'), 'route' => '/ingestion/connectors', 'icon' => 'ri-database-2-line'],
                ['label' => 'Import STIX', 'url' => route('ingestion.import'), 'route' => '/ingestion/import', 'icon' => 'ri-upload-2-line'],
                ['label' => 'Sentinel Dashboard', 'url' => route('sentinel.dashboard'), 'route' => '/sentinel/dashboard', 'icon' => 'ri-shield-check-line'],
                ['label' => 'Log Explorer', 'url' => route('sentinel.logs'), 'route' => '/sentinel/logs', 'icon' => 'ri-file-list-3-line'],
            ],
        ];

        $configCached = file_exists(app()->getCachedConfigPath());
        $routesCached = file_exists(app()->getCachedRoutesPath());

        return view('cti.settings.diagnostics', compact('tables', 'links', 'configCached', 'routesCached'));
    }
}
