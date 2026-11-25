<?php

namespace App\Services;

use Illuminate\Support\Facades\File;
use RuntimeException;
use Symfony\Component\Process\Process;

class DatabaseBackupService
{
    /**
     * Create a full database backup without overwriting existing files.
     *
     * An optional target database can be supplied for safe testing.
     */
    public function createFullBackup(
        string $schoolYear,
        ?string $targetDatabase = null
    ): string {
        $backupDirectory = storage_path('app/backups');

        if (!File::exists($backupDirectory)) {
            File::makeDirectory($backupDirectory, 0755, true);
        }

        $timestamp = now()->format('Ymd_His_u');

        $safeSchoolYear = preg_replace(
            '/[^A-Za-z0-9_-]/',
            '_',
            $schoolYear
        );

        $mysqldump = 'C:\\xampp\\mysql\\bin\\mysqldump.exe';

        if (!File::exists($mysqldump)) {
            throw new RuntimeException('mysqldump.exe was not found.');
        }

        $database = $this->getDatabaseConfig($targetDatabase);

        // Reserve a unique filename without overwriting existing backups.
        $counter = 0;

        do {
            $suffix = $counter > 0 ? "_{$counter}" : '';

            $fileName = "gradingsystem_backup_{$safeSchoolYear}_{$timestamp}{$suffix}.sql";

            $backupPath = $backupDirectory
                . DIRECTORY_SEPARATOR
                . $fileName;

            $fileHandle = @fopen($backupPath, 'x');

            if ($fileHandle !== false) {
                fclose($fileHandle);
                break;
            }

            if (!File::exists($backupPath)) {
                throw new RuntimeException(
                    'Unable to create the backup file. Check folder permissions.'
                );
            }

            $counter++;

        } while (true);

        $process = new Process([
            $mysqldump,
            '--host=' . $database['host'],
            '--port=' . $database['port'],
            '--user=' . $database['username'],
            '--single-transaction',
            '--routines',
            '--triggers',
            '--events',
            '--default-character-set=utf8mb4',
            $database['database'],
        ]);

        // Preserve existing Windows environment variables.
        $environment = getenv();

        if (!is_array($environment)) {
            $environment = [];
        }

        $environment['MYSQL_PWD'] = $database['password'] ?? '';

        $process->setEnv($environment);
        $process->setTimeout(600);

        try {
            $process->run(function ($type, $output) use ($backupPath) {
                if ($type === Process::OUT) {
                    File::append($backupPath, $output);
                }
            });

            if (!$process->isSuccessful()) {
                throw new RuntimeException(
                    'Database backup failed: ' . $process->getErrorOutput()
                );
            }

            if (
                !File::exists($backupPath) ||
                File::size($backupPath) === 0
            ) {
                throw new RuntimeException(
                    'The generated backup file is empty.'
                );
            }

            return $backupPath;

        } catch (\Throwable $exception) {

            // Delete only the incomplete backup created by this process.
            if (File::exists($backupPath)) {
                File::delete($backupPath);
            }

            throw $exception;
        }
    }

    /**
     * Restore a SQL backup into the configured database or an approved
     * test database.
     *
     * The filename must refer to a SQL file inside storage/app/backups.
     */
    public function restoreFromBackup(
        string $filename,
        ?string $targetDatabase = null
    ): void {
        if (
            preg_match('/\A[A-Za-z0-9_.-]+\.sql\z/i', $filename) !== 1
        ) {
            throw new RuntimeException('Invalid backup filename.');
        }

        $backupDirectory = storage_path('app/backups');
        $backupPath = $backupDirectory . DIRECTORY_SEPARATOR . $filename;

        $realBackupDirectory = realpath($backupDirectory);
        $realBackupPath = realpath($backupPath);

        if (
            $realBackupDirectory === false ||
            $realBackupPath === false ||
            !is_file($realBackupPath) ||
            !str_starts_with(
                strtolower($realBackupPath),
                strtolower($realBackupDirectory . DIRECTORY_SEPARATOR)
            )
        ) {
            throw new RuntimeException(
                'The selected backup file was not found.'
            );
        }

        if (
            strtolower(pathinfo($realBackupPath, PATHINFO_EXTENSION)) !== 'sql'
        ) {
            throw new RuntimeException(
                'Only SQL backup files can be restored.'
            );
        }

        if (!is_readable($realBackupPath) || filesize($realBackupPath) <= 0) {
            throw new RuntimeException(
                'The selected backup file is empty or unreadable.'
            );
        }

        $mysql = 'C:\\xampp\\mysql\\bin\\mysql.exe';

        if (!File::exists($mysql)) {
            throw new RuntimeException('mysql.exe was not found.');
        }

        $database = $this->getDatabaseConfig($targetDatabase);

        $input = fopen($realBackupPath, 'rb');

        if ($input === false) {
            throw new RuntimeException(
                'Unable to read the selected backup file.'
            );
        }

        $process = new Process([
            $mysql,
            '--host=' . $database['host'],
            '--port=' . $database['port'],
            '--user=' . $database['username'],
            '--default-character-set=utf8mb4',
            $database['database'],
        ]);

        // Preserve existing environment variables and supply the DB password.
        $environment = getenv();

        if (!is_array($environment)) {
            $environment = [];
        }

        $environment['MYSQL_PWD'] = $database['password'] ?? '';

        $process->setEnv($environment);
        $process->setInput($input);
        $process->setTimeout(1200);

        try {
            $process->run();

            if (!$process->isSuccessful()) {
                throw new RuntimeException(
                    'Database restore failed: ' . $process->getErrorOutput()
                );
            }

        } finally {
            if (is_resource($input)) {
                fclose($input);
            }
        }
    }

    /**
     * Get and validate the database connection configuration.
     *
     * Normal operations use the configured MySQL database.
     * Explicit testing operations may only target gradingsystem_restore_test.
     */
    private function getDatabaseConfig(
        ?string $targetDatabase = null
    ): array {
        $database = config('database.connections.mysql');

        if (
            empty($database['host']) ||
            empty($database['port']) ||
            empty($database['username']) ||
            empty($database['database'])
        ) {
            throw new RuntimeException(
                'Database configuration is incomplete.'
            );
        }

        if ($targetDatabase !== null) {
            if (
                preg_match('/\A[A-Za-z0-9_]+\z/', $targetDatabase) !== 1
            ) {
                throw new RuntimeException(
                    'Invalid target database name.'
                );
            }

            $configuredDatabase = $database['database'];

            $allowedDatabases = [
                $configuredDatabase,
                'gradingsystem_restore_test',
            ];

            if (!in_array($targetDatabase, $allowedDatabases, true)) {
                throw new RuntimeException(
                    'The requested target database is not allowed.'
                );
            }

            $database['database'] = $targetDatabase;
        }

        return $database;
    }
}
