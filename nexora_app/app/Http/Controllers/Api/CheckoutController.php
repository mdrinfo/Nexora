<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;
use App\Models\Session;
use App\Models\QrKey;

class CheckoutController extends Controller
{
    public function close(Request $request, Session $session)
    {
        $data = $request->validate([
            'payment_method' => ['required', 'string'],
            'paid_amount' => ['required', 'numeric', 'min:0'],
        ]);

        $orders = $session->orders()->with('items.product', 'user')->get();
        $total = $orders->sum('grand_total');

        DB::transaction(function () use ($session) {
            $session->status = 'closed';
            $session->closed_at = Carbon::now();
            $session->save();

            $qr = QrKey::query()->find($session->qr_key_id);
            if ($qr) {
                $qr->status = 'available';
                $qr->assigned_at = null;
                $qr->save();
            }
        });

        $summary = [
            'session_id' => $session->id,
            'currency' => $session->currency,
            'total' => $total,
            'orders' => $orders->map(function ($o) {
                return [
                    'order_id' => $o->id,
                    'waiter_id' => $o->user_id,
                    'items' => $o->items->map(function ($i) {
                        return [
                            'product' => optional($i->product)->name,
                            'quantity' => $i->quantity,
                            'unit_price' => $i->unit_price,
                            'line_total' => $i->line_total,
                            'notes' => $i->special_notes,
                        ];
                    }),
                ];
            }),
            'paid' => [
                'method' => $data['payment_method'],
                'amount' => $data['paid_amount'],
            ],
        ];

        return response()->json($summary);
    }
}

