<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class AdminAccess
{
    public function handle(Request $request, Closure $next)
    {
        $user = auth()->user();
        if (!$user) {
            return redirect()->route('login');
        }

        if (strtolower($user->email) === 'admin@nexora.local') {
            return $next($request);
        }

        $allowed = false;
        foreach ($user->roles as $role) {
            if (in_array($role->key, ['owner', 'manager'], true)) {
                $allowed = true;
                break;
            }
        }
        if (!$allowed) {
            foreach ($user->roles as $role) {
                foreach ($role->permissions as $p) {
                    if (in_array($p->key, ['can_view_reports', 'can_edit_stock', 'can_manage_reservations'])) {
                        $allowed = true;
                        break 2;
                    }
                }
            }
        }
        if (!$allowed) {
            abort(403);
        }
        return $next($request);
    }
}
