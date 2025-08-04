<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class TestPermissions extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test:permissions';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test Laravel Cloud permissions and directory access';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🔍 Testing Laravel Cloud Permissions');

        $content = 'Test write at ' . date('Y-m-d H:i:s');

        // Test storage directories
        $this->testDirectory('storage/framework/cache', $content);
        $this->testDirectory('storage/framework/sessions', $content);
        $this->testDirectory('storage/framework/views', $content);
        $this->testDirectory('storage/logs', $content);
        $this->testDirectory('bootstrap/cache', $content);

        // Check directory permissions
        $this->info("\n📁 Directory Permissions:");
        $dirs = [
            storage_path('framework/cache'),
            storage_path('framework/sessions'),
            storage_path('framework/views'),
            storage_path('logs'),
            base_path('bootstrap/cache')
        ];

        foreach ($dirs as $dir) {
            if (is_dir($dir)) {
                $perms = substr(sprintf('%o', fileperms($dir)), -4);
                $writable = is_writable($dir) ? '✅' : '❌';
                $this->line("$writable $dir ($perms)");
            } else {
                $this->error("❌ $dir (does not exist)");
            }
        }

        $this->info("\n✅ Permission test completed!");
    }

    private function testDirectory($relativePath, $content)
    {
        $fullPath = base_path($relativePath);
        $testFile = $fullPath . '/test-permissions.txt';

        try {
            if (!is_dir($fullPath)) {
                $this->info("📁 Creating directory: $relativePath");
                if (!mkdir($fullPath, 0777, true)) {
                    $this->error("❌ Failed to create directory: $relativePath");
                    return;
                }
            }

            file_put_contents($testFile, $content);
            $this->info("✅ Can write to $relativePath");
            unlink($testFile);
        } catch (\Exception $e) {
            $this->error("❌ Cannot write to $relativePath: " . $e->getMessage());
        }
    }
}
