<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\QrKey;
use App\Models\DiningTable;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class QrCodeController extends Controller
{
    public function index()
    {
        $qrKeys = QrKey::with('diningTable')->latest()->get();
        return view('admin.qr.index', compact('qrKeys'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'count' => 'required|integer|min:1|max:50',
        ]);

        $tenantId = 1; // Default tenant

        for ($i = 0; $i < $request->count; $i++) {
            QrKey::create([
                'tenant_id' => $tenantId,
                'token' => Str::random(10),
                'status' => 'available',
            ]);
        }

        return redirect()->back()->with('success', $request->count . ' QR Codes générés avec succès.');
    }

    public function destroy($id)
    {
        $qrKey = QrKey::findOrFail($id);
        $qrKey->delete();
        return redirect()->back()->with('success', 'QR Code supprimé.');
    }
}
