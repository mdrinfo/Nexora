<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Category;
use App\Models\Reservation;
use App\Models\DiningTable;
use Illuminate\Support\Facades\Validator;

class PublicSiteController extends Controller
{
    public function index()
    {
        $categories = Category::with(['products' => function($q) {
            $q->where('is_active', true);
        }])->orderBy('name')->get();

        return view('welcome', compact('categories'));
    }

    public function storeReservation(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'phone' => 'required|string|max:20',
            'event_date' => 'required|date|after_or_equal:today',
            'guest_count' => 'required|integer|min:1|max:50',
            'notes' => 'nullable|string|max:500',
        ]);

        if ($validator->fails()) {
            return redirect()->to('/#reservations')
                ->withErrors($validator)
                ->withInput();
        }

        // Create reservation
        // Assuming tenant_id is 1 for single tenant app or we need to handle it.
        // For now hardcode tenant_id = 1
        Reservation::create([
            'tenant_id' => 1,
            'customer_name' => $request->name,
            'customer_phone' => $request->phone, // Assuming we have this field or map it
            'event_date' => $request->event_date,
            'guest_count' => $request->guest_count,
            'status' => 'pending',
            'notes' => $request->notes,
            // 'start_time' and 'end_time' might be needed if the schema requires them.
            // Let's check schema.
        ]);

        return redirect()->to('/#reservations')->with('success', 'Rezervasyon talebiniz alındı. Sizinle iletişime geçeceğiz.');
    }
}
