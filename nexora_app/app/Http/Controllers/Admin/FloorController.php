<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Floor;
use App\Models\DiningTable;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class FloorController extends Controller
{
    public function index()
    {
        $floors = Floor::where('tenant_id', 1)->orderBy('level')->get(); // Hardcoded tenant_id for now or auth()->user()->tenant_id
        return view('admin.floors.index', compact('floors'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'level' => 'required|integer',
        ]);

        $data = $request->only(['name', 'level']);
        $data['tenant_id'] = 1; // TODO: dynamic

        if ($request->hasFile('image')) {
            $path = $request->file('image')->store('floors', 'public');
            $data['image_path'] = $path;
        }

        Floor::create($data);

        return redirect()->route('admin.floors.index')->with('success', 'Étage ajouté avec succès.');
    }

    public function edit(Floor $floor)
    {
        $floor->load('tables');
        return view('admin.floors.edit', compact('floor'));
    }

    public function update(Request $request, Floor $floor)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'level' => 'required|integer',
        ]);

        $floor->update($request->only(['name', 'level']));

        if ($request->hasFile('image')) {
            if ($floor->image_path) {
                Storage::disk('public')->delete($floor->image_path);
            }
            $path = $request->file('image')->store('floors', 'public');
            $floor->update(['image_path' => $path]);
        }

        return redirect()->back()->with('success', 'Étage mis à jour.');
    }

    public function updateTables(Request $request, Floor $floor)
    {
        $data = $request->validate([
            'tables' => 'present|array',
            'tables.*.id' => 'nullable|integer', // null if new
            'tables.*.label' => 'required|string',
            'tables.*.x' => 'required|numeric',
            'tables.*.y' => 'required|numeric',
            'tables.*.width' => 'required|numeric',
            'tables.*.height' => 'required|numeric',
            'tables.*.shape' => 'required|string',
            'tables.*.capacity' => 'required|integer',
            'tables.*.rotation' => 'nullable|numeric',
        ]);

        $tenantId = 1; // TODO: Use Auth::user()->tenant_id
        
        // Collect IDs of tables being kept/updated
        $incomingIds = [];
        foreach ($data['tables'] as $t) {
            if (isset($t['id'])) {
                $incomingIds[] = $t['id'];
            }
        }

        return \Illuminate\Support\Facades\DB::transaction(function () use ($data, $floor, $tenantId, $incomingIds) {
            // 1. Handle deletions first! 
            // Delete tables currently on THIS floor that are NOT in the incoming list.
            $floor->tables()->whereNotIn('id', $incomingIds)->delete();

            foreach ($data['tables'] as $tableData) {
                if (isset($tableData['id'])) {
                    // Update existing table
                    $table = DiningTable::find($tableData['id']);
                    if ($table && $table->floor_id == $floor->id) {
                        $table->update([
                            'label' => $tableData['label'],
                            'x_position' => $tableData['x'],
                            'y_position' => $tableData['y'],
                            'width' => $tableData['width'],
                            'height' => $tableData['height'],
                            'shape' => $tableData['shape'],
                            'capacity' => $tableData['capacity'],
                            'rotation' => $tableData['rotation'] ?? 0,
                        ]);
                    }
                } else {
                    // Create new table OR Claim orphan
                    // Check uniqueness of label within tenant
                    $existing = DiningTable::where('tenant_id', $tenantId)
                                ->where('label', $tableData['label'])
                                ->first();

                    if ($existing) {
                        // Claim the table regardless of where it was (orphan or another floor)
                        // This allows moving tables between floors by just placing them
                        $existing->update([
                            'floor_id' => $floor->id,
                            'x_position' => $tableData['x'],
                            'y_position' => $tableData['y'],
                            'width' => $tableData['width'],
                            'height' => $tableData['height'],
                            'shape' => $tableData['shape'],
                            'capacity' => $tableData['capacity'],
                            'rotation' => $tableData['rotation'] ?? 0,
                        ]);
                        continue;
                    }

                    // Create completely new
                    DiningTable::create([
                        'tenant_id' => $tenantId,
                        'floor_id' => $floor->id,
                        'label' => $tableData['label'],
                        'x_position' => $tableData['x'],
                        'y_position' => $tableData['y'],
                        'width' => $tableData['width'],
                        'height' => $tableData['height'],
                        'shape' => $tableData['shape'],
                        'capacity' => $tableData['capacity'],
                        'rotation' => $tableData['rotation'] ?? 0,
                    ]);
                }
            }
            
            return response()->json(['success' => true]);
        });
    }

    public function destroy(Floor $floor)
    {
        if ($floor->image_path) {
            Storage::disk('public')->delete($floor->image_path);
        }
        $floor->delete();
        return redirect()->route('admin.floors.index')->with('success', 'Étage supprimé.');
    }
}
