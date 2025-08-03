<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

class BackupDatabase extends Command
{
    protected $signature = 'backup:database';
    protected $description = 'Create a database backup';

    public function handle()
    {
        $this->info('Creating database backup...');

        $filename = 'backup-' . Carbon::now()->format('Y-m-d-H-i-s') . '.sql';
        $path = storage_path('app/backups/' . $filename);

        // Ensure backup directory exists
        if (!file_exists(dirname($path))) {
            mkdir(dirname($path), 0755, true);
        }

        // Create backup using pg_dump
        $command = sprintf(
            'pg_dump -h %s -p %s -U %s -d %s > %s',
            config('database.connections.pgsql.host'),
            config('database.connections.pgsql.port'),
            config('database.connections.pgsql.username'),
            config('database.connections.pgsql.database'),
            $path
        );

        exec($command, $output, $returnCode);

        if ($returnCode === 0) {
            $this->info('Database backup created successfully: ' . $filename);
            
            // Clean up old backups (keep last 7 days)
            $this->cleanupOldBackups();
        } else {
            $this->error('Database backup failed');
        }
    }

    private function cleanupOldBackups()
    {
        $backupPath = storage_path('app/backups');
        $files = glob($backupPath . '/backup-*.sql');
        
        foreach ($files as $file) {
            if (filemtime($file) < now()->subDays(7)->timestamp) {
                unlink($file);
                $this->line('Removed old backup: ' . basename($file));
            }
        }
    }
} 