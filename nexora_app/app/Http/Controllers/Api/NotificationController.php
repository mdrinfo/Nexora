<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Carbon;
use App\Models\Notification;
use App\Models\User;

class NotificationController extends Controller
{
    public function list(User $user)
    {
        $items = Notification::query()
            ->where('user_id', $user->id)
            ->orderByDesc('created_at')
            ->limit(50)
            ->get();
        return response()->json($items);
    }

    public function markRead(Notification $notification)
    {
        $notification->read_at = Carbon::now();
        $notification->save();
        return response()->json(['ok' => true]);
    }
}

