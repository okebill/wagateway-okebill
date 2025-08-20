<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Process;
use Carbon\Carbon;

class SystemUpdateController extends Controller
{
    private $backupPath;
    private $logFile;
    
    public function __construct()
    {
        $this->backupPath = storage_path('backups');
        $this->logFile = storage_path('logs/system-update.log');
        
        // Ensure backup directory exists
        if (!File::exists($this->backupPath)) {
            File::makeDirectory($this->backupPath, 0755, true);
        }
    }

    /**
     * Show system update page
     */
    public function index()
    {
        $currentVersion = $this->getCurrentVersion();
        $latestVersion = $this->getLatestVersionFromGitHub();
        $updateAvailable = $this->isUpdateAvailable($currentVersion, $latestVersion);
        $backups = $this->getAvailableBackups();
        $updateLogs = $this->getUpdateLogs();
        
        return view('system.update', compact(
            'currentVersion', 
            'latestVersion', 
            'updateAvailable',
            'backups',
            'updateLogs'
        ));
    }

    /**
     * Check for updates from GitHub
     */
    public function checkUpdates()
    {
        try {
            $currentVersion = $this->getCurrentVersion();
            $latestVersion = $this->getLatestVersionFromGitHub();
            $updateAvailable = $this->isUpdateAvailable($currentVersion, $latestVersion);
            
            $this->logUpdate("Update check performed. Current: {$currentVersion}, Latest: {$latestVersion}");
            
            return response()->json([
                'success' => true,
                'current_version' => $currentVersion,
                'latest_version' => $latestVersion,
                'update_available' => $updateAvailable,
                'message' => $updateAvailable ? 'Update tersedia!' : 'System sudah up-to-date'
            ]);
        } catch (\Exception $e) {
            $this->logUpdate("Error checking updates: " . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Gagal memeriksa update: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Perform system update
     */
    public function performUpdate(Request $request)
    {
        try {
            set_time_limit(300); // 5 minutes timeout
            
            $this->logUpdate("=== Starting System Update ===");
            
            // Step 1: Create backup
            $backupResult = $this->createBackup();
            if (!$backupResult['success']) {
                throw new \Exception($backupResult['message']);
            }
            
            // Step 2: Pull latest code from GitHub
            $this->logUpdate("Pulling latest code from GitHub...");
            $pullResult = $this->pullFromGitHub();
            if (!$pullResult['success']) {
                throw new \Exception($pullResult['message']);
            }
            
            // Step 3: Install/Update dependencies
            $this->logUpdate("Updating dependencies...");
            $this->updateDependencies();
            
            // Step 4: Run database migrations
            $this->logUpdate("Running database migrations...");
            $this->runMigrations();
            
            // Step 5: Clear cache and optimize
            $this->logUpdate("Clearing cache and optimizing...");
            $this->clearCacheAndOptimize();
            
            // Step 6: Update version file
            $this->updateVersionFile();
            
            $this->logUpdate("=== System Update Completed Successfully ===");
            
            return response()->json([
                'success' => true,
                'message' => 'System berhasil diupdate! Silakan refresh halaman.',
                'backup_created' => $backupResult['backup_name']
            ]);
            
        } catch (\Exception $e) {
            $this->logUpdate("Update failed: " . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Update gagal: ' . $e->getMessage(),
                'suggestion' => 'Silakan coba lagi atau restore dari backup jika diperlukan.'
            ], 500);
        }
    }

    /**
     * Create system backup
     */
    public function createBackup()
    {
        try {
            $timestamp = Carbon::now()->format('Y-m-d_H-i-s');
            $backupName = "backup_{$timestamp}";
            $backupDir = $this->backupPath . '/' . $backupName;
            
            // Create backup directory
            File::makeDirectory($backupDir, 0755, true);
            
            $this->logUpdate("Creating backup: {$backupName}");
            
            // Files to backup (exclude node_modules, vendor, etc)
            $excludePaths = [
                'node_modules',
                'vendor',
                '.git',
                'wa-sessions',
                '.wwebjs_cache',
                'storage/logs',
                'storage/backups'
            ];
            
            // Get all files except excluded ones
            $basePath = base_path();
            $this->copyDirectoryExcluding($basePath, $backupDir, $excludePaths);
            
            // Backup database
            $this->backupDatabase($backupDir);
            
            // Create backup info file
            $backupInfo = [
                'created_at' => Carbon::now()->toISOString(),
                'version' => $this->getCurrentVersion(),
                'size' => $this->getDirectorySize($backupDir),
                'files_count' => $this->countFiles($backupDir)
            ];
            
            File::put($backupDir . '/backup_info.json', json_encode($backupInfo, JSON_PRETTY_PRINT));
            
            $this->logUpdate("Backup created successfully: {$backupName}");
            
            return [
                'success' => true,
                'backup_name' => $backupName,
                'message' => 'Backup berhasil dibuat'
            ];
            
        } catch (\Exception $e) {
            $this->logUpdate("Backup failed: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Gagal membuat backup: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Restore from backup
     */
    public function restoreBackup(Request $request)
    {
        $backupName = $request->input('backup_name');
        
        if (!$backupName) {
            return response()->json([
                'success' => false,
                'message' => 'Nama backup tidak valid'
            ], 400);
        }
        
        try {
            $backupDir = $this->backupPath . '/' . $backupName;
            
            if (!File::exists($backupDir)) {
                throw new \Exception("Backup tidak ditemukan: {$backupName}");
            }
            
            $this->logUpdate("=== Starting System Restore from {$backupName} ===");
            
            // Restore files (exclude some directories)
            $basePath = base_path();
            $excludePaths = ['storage/backups', 'wa-sessions', '.wwebjs_cache'];
            
            $this->copyDirectoryExcluding($backupDir, $basePath, $excludePaths);
            
            // Restore database if exists
            $this->restoreDatabase($backupDir);
            
            // Clear cache
            $this->clearCacheAndOptimize();
            
            $this->logUpdate("=== System Restore Completed Successfully ===");
            
            return response()->json([
                'success' => true,
                'message' => 'System berhasil di-restore dari backup!'
            ]);
            
        } catch (\Exception $e) {
            $this->logUpdate("Restore failed: " . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Restore gagal: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete backup
     */
    public function deleteBackup(Request $request)
    {
        $backupName = $request->input('backup_name');
        
        if (!$backupName) {
            return response()->json([
                'success' => false,
                'message' => 'Nama backup tidak valid'
            ], 400);
        }
        
        try {
            $backupDir = $this->backupPath . '/' . $backupName;
            
            if (!File::exists($backupDir)) {
                throw new \Exception("Backup tidak ditemukan: {$backupName}");
            }
            
            File::deleteDirectory($backupDir);
            
            $this->logUpdate("Backup deleted: {$backupName}");
            
            return response()->json([
                'success' => true,
                'message' => 'Backup berhasil dihapus'
            ]);
            
        } catch (\Exception $e) {
            $this->logUpdate("Delete backup failed: " . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Gagal menghapus backup: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Helper methods
     */
    private function getCurrentVersion()
    {
        $versionFile = base_path('VERSION');
        if (File::exists($versionFile)) {
            return trim(File::get($versionFile));
        }
        
        // Try to get from git
        try {
            $result = Process::run('git describe --tags --abbrev=0');
            if ($result->successful()) {
                return trim($result->output());
            }
        } catch (\Exception $e) {
            // Ignore git errors
        }
        
        return 'v1.0.0'; // Default version
    }

    private function getLatestVersionFromGitHub()
    {
        try {
            // Get latest commit hash from GitHub API
            $url = 'https://api.github.com/repos/okebill/wagateway-okebill/commits/main';
            $context = stream_context_create([
                'http' => [
                    'method' => 'GET',
                    'header' => 'User-Agent: WhatsApp-Gateway/1.0'
                ]
            ]);
            
            $response = @file_get_contents($url, false, $context);
            if ($response) {
                $data = json_decode($response, true);
                return substr($data['sha'] ?? 'unknown', 0, 7); // Short hash
            }
        } catch (\Exception $e) {
            $this->logUpdate("Error fetching latest version: " . $e->getMessage());
        }
        
        return 'unknown';
    }

    private function isUpdateAvailable($current, $latest)
    {
        return $current !== $latest && $latest !== 'unknown';
    }

    private function pullFromGitHub()
    {
        try {
            // Check if .git directory exists
            if (!File::exists(base_path('.git'))) {
                // If not a git repository, download as zip
                return $this->downloadFromGitHub();
            }
            
            // Pull latest changes
            $result = Process::run('git pull origin main', base_path());
            
            if ($result->successful()) {
                return [
                    'success' => true,
                    'message' => 'Code updated from GitHub'
                ];
            } else {
                throw new \Exception('Git pull failed: ' . $result->errorOutput());
            }
            
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    private function downloadFromGitHub()
    {
        try {
            $zipUrl = 'https://github.com/okebill/wagateway-okebill/archive/refs/heads/main.zip';
            $tempFile = storage_path('temp/github-update.zip');
            
            // Create temp directory
            File::makeDirectory(dirname($tempFile), 0755, true);
            
            // Download zip file
            $zipContent = file_get_contents($zipUrl);
            if (!$zipContent) {
                throw new \Exception('Failed to download update from GitHub');
            }
            
            File::put($tempFile, $zipContent);
            
            // Extract and replace files
            $zip = new \ZipArchive();
            if ($zip->open($tempFile) === TRUE) {
                $extractPath = storage_path('temp/extracted');
                $zip->extractTo($extractPath);
                $zip->close();
                
                // Move files to project root
                $sourceDir = $extractPath . '/wagateway-okebill-main';
                if (File::exists($sourceDir)) {
                    $this->copyDirectory($sourceDir, base_path());
                }
                
                // Cleanup
                File::delete($tempFile);
                File::deleteDirectory($extractPath);
                
                return [
                    'success' => true,
                    'message' => 'Code downloaded and updated from GitHub'
                ];
            } else {
                throw new \Exception('Failed to extract update archive');
            }
            
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    private function updateDependencies()
    {
        // Update PHP dependencies
        if (File::exists(base_path('composer.json'))) {
            Process::run('composer install --no-dev --optimize-autoloader', base_path());
        }
        
        // Update Node.js dependencies
        if (File::exists(base_path('package.json'))) {
            Process::run('npm install --production', base_path());
        }
    }

    private function runMigrations()
    {
        Artisan::call('migrate', ['--force' => true]);
    }

    private function clearCacheAndOptimize()
    {
        Artisan::call('cache:clear');
        Artisan::call('config:clear');
        Artisan::call('route:clear');
        Artisan::call('view:clear');
        
        Artisan::call('config:cache');
        Artisan::call('route:cache');
        Artisan::call('view:cache');
    }

    private function updateVersionFile()
    {
        $newVersion = $this->getLatestVersionFromGitHub();
        File::put(base_path('VERSION'), $newVersion);
    }

    private function getAvailableBackups()
    {
        $backups = [];
        $backupDirs = File::directories($this->backupPath);
        
        foreach ($backupDirs as $dir) {
            $backupName = basename($dir);
            $infoFile = $dir . '/backup_info.json';
            
            if (File::exists($infoFile)) {
                $info = json_decode(File::get($infoFile), true);
                $backups[] = array_merge($info, ['name' => $backupName]);
            } else {
                // Fallback for backups without info file
                $backups[] = [
                    'name' => $backupName,
                    'created_at' => Carbon::createFromTimestamp(File::lastModified($dir))->toISOString(),
                    'size' => $this->getDirectorySize($dir),
                    'version' => 'unknown'
                ];
            }
        }
        
        // Sort by creation date (newest first)
        usort($backups, function($a, $b) {
            return strtotime($b['created_at']) - strtotime($a['created_at']);
        });
        
        return $backups;
    }

    private function getUpdateLogs()
    {
        if (!File::exists($this->logFile)) {
            return [];
        }
        
        $logs = File::get($this->logFile);
        $lines = array_filter(explode("\n", $logs));
        
        // Return last 50 lines
        return array_slice($lines, -50);
    }

    private function logUpdate($message)
    {
        $timestamp = Carbon::now()->format('Y-m-d H:i:s');
        $logMessage = "[{$timestamp}] {$message}" . PHP_EOL;
        
        File::append($this->logFile, $logMessage);
        Log::info("System Update: {$message}");
    }

    private function copyDirectoryExcluding($source, $destination, $excludePaths = [])
    {
        if (!File::exists($destination)) {
            File::makeDirectory($destination, 0755, true);
        }
        
        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($source, \RecursiveDirectoryIterator::SKIP_DOTS),
            \RecursiveIteratorIterator::SELF_FIRST
        );
        
        foreach ($iterator as $item) {
            $relativePath = str_replace($source . DIRECTORY_SEPARATOR, '', $item->getPathname());
            
            // Check if path should be excluded
            $excluded = false;
            foreach ($excludePaths as $excludePath) {
                if (str_starts_with($relativePath, $excludePath)) {
                    $excluded = true;
                    break;
                }
            }
            
            if ($excluded) {
                continue;
            }
            
            $target = $destination . DIRECTORY_SEPARATOR . $relativePath;
            
            if ($item->isDir()) {
                File::makeDirectory($target, 0755, true);
            } else {
                File::copy($item->getPathname(), $target);
            }
        }
    }

    private function copyDirectory($source, $destination)
    {
        if (!File::exists($destination)) {
            File::makeDirectory($destination, 0755, true);
        }
        
        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($source, \RecursiveDirectoryIterator::SKIP_DOTS),
            \RecursiveIteratorIterator::SELF_FIRST
        );
        
        foreach ($iterator as $item) {
            $target = $destination . DIRECTORY_SEPARATOR . $iterator->getSubPathName();
            
            if ($item->isDir()) {
                File::makeDirectory($target, 0755, true);
            } else {
                File::copy($item->getPathname(), $target);
            }
        }
    }

    private function getDirectorySize($directory)
    {
        $size = 0;
        foreach (new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($directory)) as $file) {
            $size += $file->getSize();
        }
        return $this->formatBytes($size);
    }

    private function countFiles($directory)
    {
        $count = 0;
        foreach (new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($directory)) as $file) {
            if ($file->isFile()) {
                $count++;
            }
        }
        return $count;
    }

    private function formatBytes($size, $precision = 2)
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        for ($i = 0; $size > 1024 && $i < count($units) - 1; $i++) {
            $size /= 1024;
        }
        return round($size, $precision) . ' ' . $units[$i];
    }

    private function backupDatabase($backupDir)
    {
        try {
            $dbConfig = config('database.connections.' . config('database.default'));
            
            if ($dbConfig['driver'] === 'mysql') {
                $command = sprintf(
                    'mysqldump -h %s -P %s -u %s %s %s > %s',
                    $dbConfig['host'],
                    $dbConfig['port'],
                    $dbConfig['username'],
                    $dbConfig['password'] ? '-p' . $dbConfig['password'] : '',
                    $dbConfig['database'],
                    $backupDir . '/database.sql'
                );
                
                Process::run($command);
            }
        } catch (\Exception $e) {
            $this->logUpdate("Database backup failed: " . $e->getMessage());
        }
    }

    private function restoreDatabase($backupDir)
    {
        try {
            $sqlFile = $backupDir . '/database.sql';
            if (File::exists($sqlFile)) {
                $dbConfig = config('database.connections.' . config('database.default'));
                
                if ($dbConfig['driver'] === 'mysql') {
                    $command = sprintf(
                        'mysql -h %s -P %s -u %s %s %s < %s',
                        $dbConfig['host'],
                        $dbConfig['port'],
                        $dbConfig['username'],
                        $dbConfig['password'] ? '-p' . $dbConfig['password'] : '',
                        $dbConfig['database'],
                        $sqlFile
                    );
                    
                    Process::run($command);
                }
            }
        } catch (\Exception $e) {
            $this->logUpdate("Database restore failed: " . $e->getMessage());
        }
    }
}
