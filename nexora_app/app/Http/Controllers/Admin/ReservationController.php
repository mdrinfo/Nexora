<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Reservation;
use App\Models\DiningTable;
use App\Models\Floor;
use Illuminate\Http\Request;

class ReservationController extends Controller
{
    public function index()
    {
        $reservations = Reservation::where('tenant_id', 1)
            ->with(['table.floor'])
            ->orderBy('event_date', 'desc')
            ->orderBy('start_time', 'desc')
            ->paginate(20);
            
        $floors = Floor::with('tables')->where('tenant_id', 1)->get();
        
        return view('admin.reservations.index', compact('reservations', 'floors'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'customer_name' => 'required|string',
            'customer_phone' => 'nullable|string',
            'event_date' => 'required|date',
            'start_time' => 'required',
            'guest_count' => 'required|integer|min:1',
            'dining_table_id' => 'nullable|exists:dining_tables,id',
        ]);

        Reservation::create([
            'tenant_id' => 1,
            'customer_name' => $request->customer_name,
            'customer_phone' => $request->customer_phone,
            'event_date' => $request->event_date,
            'start_time' => $request->start_time,
            'guest_count' => $request->guest_count,
            'dining_table_id' => $request->dining_table_id,
            'status' => 'confirmed',
            'notes' => $request->notes,
        ]);

        return redirect()->back()->with('success', 'Réservation créée.');
    }

    public function update(Request $request, Reservation $reservation)
    {
        $request->validate([
            'status' => 'required|in:pending,confirmed,cancelled,completed,no_show',
        ]);

        $reservation->update($request->all());

        return redirect()->back()->with('success', 'Réservation mise à jour.');
    }

    public function destroy(Reservation $reservation)
    {
        $reservation->delete();
        return redirect()->back()->with('success', 'Réservation supprimée.');
    }
}
