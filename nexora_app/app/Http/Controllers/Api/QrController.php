<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;
use App\Models\QrKey;
use App\Models\Session;
use App\Models\DiningTable;

class QrController extends Controller
{
    public function status(string $token)
    {
        $qr = QrKey::query()->where('token', $token)->first();
        if (!$qr) {
            return response()->json(['error' => 'not_found'], 404);
        }
        
        $session = null;
        if ($qr->dining_table_id) {
            $session = Session::where('dining_table_id', $qr->dining_table_id)
                ->where('status', 'open')
                ->latest()
                ->first();
        }

        return response()->json([
            'status' => $qr->status,
            'dining_table_id' => $qr->dining_table_id,
            'session_id' => $session ? $session->id : null,
            'table_label' => $qr->diningTable ? $qr->diningTable->label : null,
        ]);
    }

    public function activate(Request $request)
    {
        $data = $request->validate([
            'token' => ['required', 'string'],
            'table_id' => ['nullable', 'integer'],
            'currency' => ['nullable', 'string', 'size:3'],
        ]);

        $qr = QrKey::query()->where('token', $data['token'])->lockForUpdate()->first();
        if (!$qr) {
            return response()->json(['error' => 'not_found'], 404);
        }
        if ($qr->status !== 'available' && $qr->dining_table_id) {
            return response()->json(['error' => 'already_active'], 409);
        }

        if (empty($data['table_id'])) {
            $tables = DiningTable::query()->select('id', 'label', 'capacity')->orderBy('label')->get();
            return response()->json(['require_table' => true, 'tables' => $tables]);
        }

        $table = DiningTable::query()->find($data['table_id']);
        if (!$table) {
            return response()->json(['error' => 'invalid_table'], 422);
        }

        $session = DB::transaction(function () use ($qr, $table, $data) {
            $qr->dining_table_id = $table->id;
            $qr->status = 'active';
            $qr->assigned_at = Carbon::now();
            $qr->save();

            $type = 'table';
            if ($table->label === 'ONLINE') $type = 'online';
            if ($table->label === 'TAKEAWAY') $type = 'takeaway';

            return Session::query()->create([
                'tenant_id' => $table->tenant_id,
                'dining_table_id' => $table->id,
                'qr_key_id' => $qr->id,
                'status' => 'open',
                'type' => $type,
                'currency' => $data['currency'] ?? 'EUR',
                'total_amount' => 0,
                'opened_at' => Carbon::now(),
            ]);
        });

        return response()->json([
            'session_id' => $session->id,
            'qr_key_id' => $qr->id,
            'table_id' => $table->id,
        ], 201);
    }
}
