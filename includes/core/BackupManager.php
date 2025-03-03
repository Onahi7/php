<?php
namespace Summit\Core;

class BackupManager {
    private static $instance = null;
    private $config;
    private $backupPath;

    private function __construct() {
        $this->config = include __DIR__ . '/../../config/backup.php';
        $this->backupPath = $this->config['backup_path'] ?? __DIR__ . '/../../storage/backups';
        
        if (!is_dir($this->backupPath)) {
            mkdir($this->backupPath, 0755, true);
        }
    }

    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function createBackup($type = 'full') {
        try {
            $timestamp = date('Y-m-d_H-i-s');
            $backupFile = "{$this->backupPath}/backup_{$type}_{$timestamp}";

            switch ($type) {
                case 'database':
                    $this->backupDatabase($backupFile);
                    break;
                case 'files':
                    $this->backupFiles($backupFile);
                    break;
                case 'full':
                    $this->backupDatabase($backupFile . '_db');
                    $this->backupFiles($backupFile . '_files');
                    break;
                default:
                    throw new \Exception("Invalid backup type: {$type}");
            }

            // Compress backups
            $this->compressBackups($backupFile);

            // Upload to remote storage if configured
            if ($this->config['remote_storage']['enabled']) {
                $this->uploadToRemoteStorage($backupFile . '.gz');
            }

            // Cleanup old backups
            $this->cleanupOldBackups();

            return true;
        } catch (\Exception $e) {
            ErrorHandler::logError("Backup failed: " . $e->getMessage());
            return false;
        }
    }

    private function backupDatabase($filename) {
        $dbConfig = include __DIR__ . '/../../config/database.php';
        
        $command = sprintf(
            'mysqldump -h%s -u%s -p%s %s > %s.sql',
            escapeshellarg($dbConfig['host']),
            escapeshellarg($dbConfig['username']),
            escapeshellarg($dbConfig['password']),
            escapeshellarg($dbConfig['database']),
            escapeshellarg($filename)
        );

        exec($command, $output, $returnVar);

        if ($returnVar !== 0) {
            throw new \Exception("Database backup failed");
        }
    }

    private function backupFiles($filename) {
        $sourcePath = __DIR__ . '/../../';
        $excludes = array_map(function($path) use ($sourcePath) {
            return '--exclude=' . escapeshellarg(str_replace($sourcePath, '', $path));
        }, $this->config['exclude_paths'] ?? []);

        $command = sprintf(
            'tar -czf %s.tar.gz -C %s %s .',
            escapeshellarg($filename),
            escapeshellarg($sourcePath),
            implode(' ', $excludes)
        );

        exec($command, $output, $returnVar);

        if ($returnVar !== 0) {
            throw new \Exception("Files backup failed");
        }
    }

    private function compressBackups($filename) {
        if (file_exists($filename . '.sql')) {
            exec("gzip {$filename}.sql");
        }
        if (file_exists($filename . '.tar.gz')) {
            // Already compressed by tar
            return;
        }
    }

    private function uploadToRemoteStorage($filename) {
        switch ($this->config['remote_storage']['type']) {
            case 's3':
                $this->uploadToS3($filename);
                break;
            case 'ftp':
                $this->uploadToFTP($filename);
                break;
            default:
                throw new \Exception("Unsupported remote storage type");
        }
    }

    private function uploadToS3($filename) {
        // Implementation for S3 upload
        // This would use AWS SDK
        try {
            $s3Config = $this->config['remote_storage']['s3'];
            // S3 upload implementation
        } catch (\Exception $e) {
            ErrorHandler::logError("S3 upload failed: " . $e->getMessage());
            throw $e;
        }
    }

    private function uploadToFTP($filename) {
        $ftpConfig = $this->config['remote_storage']['ftp'];
        
        $conn = ftp_connect($ftpConfig['host']);
        if (!$conn) {
            throw new \Exception("FTP connection failed");
        }

        try {
            if (!ftp_login($conn, $ftpConfig['username'], $ftpConfig['password'])) {
                throw new \Exception("FTP login failed");
            }

            if (!ftp_put($conn, basename($filename), $filename, FTP_BINARY)) {
                throw new \Exception("FTP upload failed");
            }
        } finally {
            ftp_close($conn);
        }
    }

    private function cleanupOldBackups() {
        $retention = $this->config['retention_days'] ?? 30;
        $threshold = time() - ($retention * 86400);

        foreach (glob("{$this->backupPath}/*") as $file) {
            if (filemtime($file) < $threshold) {
                unlink($file);
            }
        }
    }

    public function restoreBackup($filename) {
        try {
            if (!file_exists($filename)) {
                throw new \Exception("Backup file not found");
            }

            if (strpos($filename, '_db_') !== false) {
                $this->restoreDatabase($filename);
            } elseif (strpos($filename, '_files_') !== false) {
                $this->restoreFiles($filename);
            } else {
                throw new \Exception("Invalid backup file");
            }

            return true;
        } catch (\Exception $e) {
            ErrorHandler::logError("Restore failed: " . $e->getMessage());
            return false;
        }
    }

    private function restoreDatabase($filename) {
        $dbConfig = include __DIR__ . '/../../config/database.php';
        
        // Uncompress if needed
        if (substr($filename, -3) === '.gz') {
            exec("gunzip -c {$filename} > {$filename}.sql");
            $filename = "{$filename}.sql";
        }

        $command = sprintf(
            'mysql -h%s -u%s -p%s %s < %s',
            escapeshellarg($dbConfig['host']),
            escapeshellarg($dbConfig['username']),
            escapeshellarg($dbConfig['password']),
            escapeshellarg($dbConfig['database']),
            escapeshellarg($filename)
        );

        exec($command, $output, $returnVar);

        if ($returnVar !== 0) {
            throw new \Exception("Database restore failed");
        }
    }

    private function restoreFiles($filename) {
        $targetPath = __DIR__ . '/../../';
        
        if (substr($filename, -7) === '.tar.gz') {
            $command = sprintf(
                'tar -xzf %s -C %s',
                escapeshellarg($filename),
                escapeshellarg($targetPath)
            );
        } else {
            throw new \Exception("Unsupported backup format");
        }

        exec($command, $output, $returnVar);

        if ($returnVar !== 0) {
            throw new \Exception("Files restore failed");
        }
    }

    public function listBackups() {
        $backups = [];
        foreach (glob("{$this->backupPath}/*") as $file) {
            $backups[] = [
                'filename' => basename($file),
                'size' => $this->formatBytes(filesize($file)),
                'date' => date('Y-m-d H:i:s', filemtime($file)),
                'type' => strpos($file, '_db_') !== false ? 'database' : 'files'
            ];
        }
        return $backups;
    }

    private function formatBytes($bytes) {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        $bytes /= pow(1024, $pow);
        return round($bytes, 2) . ' ' . $units[$pow];
    }
}
