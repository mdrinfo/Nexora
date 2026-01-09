<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class RestoreDatabase extends Command
{
    protected $signature = 'nexora:restore {filename}';
    protected $description = 'Restore database from a backup file';

    public function handle()
    {
        $filename = $this->argument('filename');
        $path = storage_path('app/backups/' . $filename);

        if (!file_exists($path)) {
            $this->error("Backup file not found: {$filename}");
            return 1;
        }

        $this->info("Restoring from: {$filename}...");

        // Get DB config
        $dbName = config('database.connections.mysql.database');
        $username = config('database.connections.mysql.username');
        $password = config('database.connections.mysql.password');
        $host = config('database.connections.mysql.host');

        // Build command (mysql)
        // Note: Assumes mysql client is available
        $restoreBinaryPath = env('DB_RESTORE_BINARY_PATH', 'mysql');
        $command = "{$restoreBinaryPath} --user='{$username}' --password='{$password}' --host='{$host}' '{$dbName}' < '{$path}'";

        // Execute
        $returnVar = null;
        $output = null;
        exec($command, $output, $returnVar);

        if ($returnVar !== 0) {
            $this->error("Restore failed. details: " . implode("\n", $output));
            $this->error("Ensure '{$restoreBinaryPath}' is installed and accessible.");
            return 1;
        }

        $this->info('Database restored successfully.');
        return 0;
    }
}
