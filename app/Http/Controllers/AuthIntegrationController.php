<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AuthIntegrationController extends Controller
{
    private const DASHBOARD_TABS = [
        'dashboard' => [
            'title' => 'Dashboard',
            'view' => 'admin.tabs.dashboard',
        ],
        'user-management' => [
            'title' => 'User Management',
            'view' => 'admin.tabs.user-management',
        ],
        'audit-log' => [
            'title' => 'Audit Log',
            'view' => 'admin.tabs.audit-log',
        ],
        'system-activity' => [
            'title' => 'System Activity',
            'view' => 'admin.tabs.system-activity',
        ],
        'sms-settings' => [
            'title' => 'SMS Settings',
            'view' => 'admin.tabs.sms-settings',
        ],
        'system-settings' => [
            'title' => 'System Settings',
            'view' => 'admin.tabs.system-settings',
        ],
        'account-settings' => [
            'title' => 'Account Settings',
            'view' => 'admin.tabs.account-settings',
        ],
    ];

    public function showLogin(): View
    {
        return view('auth.login', [
            'supabaseUrl' => config('supabase.url'),
            'supabaseAnonKey' => config('supabase.anon_key'),
        ]);
    }

    public function session(Request $request): JsonResponse
    {
        $user = $request->user();

        return response()->json([
            'message' => 'Supabase token validated.',
            'user' => [
                'user_id' => $user?->user_id,
                'email' => $user?->email,
                'first_name' => $user?->first_name,
                'last_name' => $user?->last_name,
                'role' => $user?->role,
                'status' => $user?->status,
            ],
            'supabase_user' => $request->attributes->get('supabase_user'),
        ]);
    }

    public function dashboard(Request $request): View
    {
        $requestedTab = (string) $request->query('tab', 'dashboard');
        $tabKey = array_key_exists($requestedTab, self::DASHBOARD_TABS) ? $requestedTab : 'dashboard';
        $tabConfig = self::DASHBOARD_TABS[$tabKey];

        return view('admin.dashboard', [
            'activeTab' => $tabKey,
            'pageTitle' => $tabConfig['title'],
            'initialTabView' => $tabConfig['view'],
            'sidebarRole' => (string) $request->query('role', 'admin'),
            'sidebarFullName' => (string) $request->query('name', 'Loading user...'),
        ]);
    }

    public function dashboardContent(string $tab): View
    {
        if (!array_key_exists($tab, self::DASHBOARD_TABS)) {
            abort(404);
        }

        return view(self::DASHBOARD_TABS[$tab]['view'], [
            'tabKey' => $tab,
            'pageTitle' => self::DASHBOARD_TABS[$tab]['title'],
        ]);
    }

    public function adminPing(Request $request): JsonResponse
    {
        return response()->json([
            'message' => 'Admin route access granted.',
            'email' => $request->user()?->email,
            'role' => $request->user()?->role,
        ]);
    }

    public function logout(): JsonResponse
    {
        return response()->json([
            'message' => 'Client should clear Supabase token locally.',
        ]);
    }
}
