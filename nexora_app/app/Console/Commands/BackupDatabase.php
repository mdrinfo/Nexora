<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

class BackupDatabase extends Command
{
    protected $signature = 'nexora:backup';
    protected $description = 'Create a backup of the database';

    public function handle()
    {
        $this->info('Starting database backup...');

        $filename = 'backup-' . Carbon::now()->format('Y-m-d-H-i-s') . '.sql';
        $path = storage_path('app/backups/' . $filename);
        
        // Ensure backups directory exists
        if (!Storage::exists('backups')) {
            Storage::makeDirectory('backups');
        }

        // Get DB config
        $dbName = config('database.connections.mysql.database');
        $username = config('database.connections.mysql.username');
        $password = config('database.connections.mysql.password');
        $host = config('database.connections.mysql.host');

        // Build command (mysqldump)
        // Note: This assumes mysqldump is available in the system path
        // Using --no-tablespaces to avoid permission issues on some setups
        $dumpBinaryPath = env('DB_DUMP_BINARY_PATH', 'mysqldump');
        $command = "{$dumpBinaryPath} --user='{$username}' --password='{$password}' --host='{$host}' --no-tablespaces '{$dbName}' > '{$path}'";

        // Execute
        $returnVar = null;
        $output = null;
        exec($command, $output, $returnVar);

        if ($returnVar !== 0) {
            $this->error("Backup failed. details: " . implode("\n", $output));
            $this->error("Ensure '{$dumpBinaryPath}' is installed and accessible.");
            // Remove empty file
            if (file_exists($path)) {
                unlink($path);
            }
            return 1;
        }

        $this->info("Backup created successfully: {$filename}");

        // Prune old backups (keep last 7)
        $files = Storage::files('backups');
        // Sort files by modification time descending
        usort($files, function($a, $b) {
            return Storage::lastModified($b) <=> Storage::lastModified($a);
        });
        
        if (count($files) > 7) {
            $filesToDelete = array_slice($files, 7);
            Storage::delete($filesToDelete);
            $this->info('Pruned ' . count($filesToDelete) . ' old backups.');
        }

        return 0;
    }
}
