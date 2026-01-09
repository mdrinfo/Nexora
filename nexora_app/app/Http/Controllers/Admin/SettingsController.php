<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Storage;
use App\Models\Tenant;
use App\Models\Setting;

class SettingsController extends Controller
{
    public function index()
    {
        $tenant = Tenant::query()->where('slug', 'nexora')->firstOrFail();
        $setting = Setting::query()->where('tenant_id', $tenant->id)->first();

        // List backups
        $backups = [];
        if (Storage::exists('backups')) {
            $files = Storage::files('backups');
            foreach ($files as $file) {
                $backups[] = [
                    'filename' => basename($file),
                    'size' => Storage::size($file),
                    'last_modified' => Storage::lastModified($file),
                ];
            }
            // Sort by newness
            usort($backups, function($a, $b) {
                return $b['last_modified'] <=> $a['last_modified'];
            });
        }

        return view('admin.settings', [
            'tenant' => $tenant,
            'setting' => $setting,
            'backups' => $backups,
        ]);
    }

    public function update(Request $request)
    {
        $tenant = Tenant::query()->where('slug', 'nexora')->firstOrFail();
        $data = $request->validate([
            'brand_name' => ['required', 'string', 'max:255'],
            'logo_path' => ['nullable', 'string', 'max:255'],
            'primary_color_hex' => ['required', 'string', 'max:7'],
            'secondary_color_hex' => ['nullable', 'string', 'max:7'],
            'accent_color_hex' => ['nullable', 'string', 'max:7'],
            'currency' => ['required', 'string', 'size:3'],
            'retention_period_days' => ['required', 'integer', 'min:30'],
            'enable_data_purge' => ['nullable', 'boolean'],
        ]);

        // Checkbox handling
        $data['enable_data_purge'] = $request->has('enable_data_purge');

        Setting::query()->updateOrCreate(
            ['tenant_id' => $tenant->id],
            $data + ['tenant_id' => $tenant->id]
        );

        return redirect()->route('admin.settings')->with('success', 'Paramètres mis à jour.');
    }

    public function createBackup()
    {
        try {
            Artisan::call('nexora:backup');
            return redirect()->back()->with('success', 'Sauvegarde créée avec succès.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Erreur lors de la création de la sauvegarde: ' . $e->getMessage());
        }
    }

    public function downloadBackup($filename)
    {
        $path = 'backups/' . $filename;
        if (!Storage::exists($path)) {
            abort(404);
        }
        return Storage::download($path);
    }

    public function restoreBackup($filename)
    {
        try {
            // This is dangerous, but requested.
            Artisan::call('nexora:restore', ['filename' => $filename]);
            return redirect()->back()->with('success', 'Base de données restaurée avec succès.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Erreur lors de la restauration: ' . $e->getMessage());
        }
    }
}

