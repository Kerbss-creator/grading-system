<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SchoolYear;
use App\Services\DatabaseBackupService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Throwable;

class DatabaseBackupController extends Controller
{
    /**
     * Display available database backups.
     */
    public function index(): JsonResponse
    {
        $backupDirectory = storage_path('app/backups');

        if (!File::isDirectory($backupDirectory)) {
            return response()->json([
                'backups' => [],
            ]);
        }

        $files = File::files($backupDirectory);

        $backups = collect($files)
            ->filter(function ($file) {
                return strtolower($file->getExtension()) === 'sql';
            })
            ->map(function ($file) {
                return [
                    'filename' => $file->getFilename(),
                    'size' => $file->getSize(),
                    'created_at' => date(
                        'Y-m-d H:i:s',
                        $file->getMTime()
                    ),
                ];
            })
            ->sortByDesc('created_at')
            ->values();

        return response()->json([
            'backups' => $backups,
        ]);
    }

    /**
     * Create a manual full database backup.
     */
    public function store(
        DatabaseBackupService $backupService
    ): RedirectResponse {
        $activeSchoolYear = SchoolYear::active();

        $schoolYearLabel = $activeSchoolYear
            ? $activeSchoolYear->year_name
            : 'NoActiveSchoolYear';

        try {
            $backupPath = $backupService->createFullBackup(
                $schoolYearLabel
            );

            if (
                !is_file($backupPath) ||
                filesize($backupPath) <= 0
            ) {
                throw new \RuntimeException(
                    'The generated backup could not be verified.'
                );
            }

            Log::info('Manual database backup created.', [
                'school_year' => $schoolYearLabel,
                'filename' => basename($backupPath),
            ]);

            return redirect()
                ->route('admin.settings')
                ->with(
                    'success',
                    'Database backup created successfully.'
                );

        } catch (Throwable $exception) {

            Log::error('Manual database backup failed.', [
                'school_year' => $schoolYearLabel,
                'error' => $exception->getMessage(),
            ]);

            return redirect()
                ->route('admin.settings')
                ->with(
                    'error',
                    'Database backup failed. Please check your database connection and backup configuration.'
                );
        }
    }

    /**
     * Download a selected database backup.
     */
    /**
     * Download a selected database backup.
     */
    public function download(
        string $filename
    ): BinaryFileResponse {
        // Validate filename.
        abort_unless(
            preg_match('/\A[A-Za-z0-9_.-]+\.sql\z/i', $filename) === 1,
            404
        );

        $backupDirectory = storage_path('app/backups');

        $resolvedDirectory = realpath($backupDirectory);

        $backupPath = $backupDirectory
            . DIRECTORY_SEPARATOR
            . $filename;

        $resolvedBackupPath = realpath($backupPath);

        // Verify that the file exists, is readable,
        // and is inside the designated backup directory.
        abort_unless(
            $resolvedDirectory !== false &&
            $resolvedBackupPath !== false &&
            is_file($resolvedBackupPath) &&
            is_readable($resolvedBackupPath) &&
            filesize($resolvedBackupPath) > 0 &&
            str_starts_with(
                strtolower($resolvedBackupPath),
                strtolower($resolvedDirectory . DIRECTORY_SEPARATOR)
            ),
            404
        );

        return response()->download(
            $resolvedBackupPath,
            $filename,
            [
                'Content-Type' => 'application/sql',
            ]
        );
    }

    /**
     * Restore the database from a selected backup.
     *
     * A new pre-restore backup is created before the restore begins.
     */

    /**
     * Restore the database from a selected backup.
     *
     * A new pre-restore backup is created before the restore begins.
     */
    public function restore(
        string $filename,
        DatabaseBackupService $backupService
    ): RedirectResponse {
        // Validate filename.
        abort_unless(
            preg_match('/\A[A-Za-z0-9_.-]+\.sql\z/i', $filename) === 1,
            404
        );

        $backupDirectory = storage_path('app/backups');

        $resolvedDirectory = realpath($backupDirectory);

        $backupPath = $backupDirectory
            . DIRECTORY_SEPARATOR
            . $filename;

        $resolvedBackupPath = realpath($backupPath);

        // Verify that the selected backup is a valid file
        // located inside the designated backup directory.
        abort_unless(
            $resolvedDirectory !== false &&
            $resolvedBackupPath !== false &&
            is_file($resolvedBackupPath) &&
            is_readable($resolvedBackupPath) &&
            filesize($resolvedBackupPath) > 0 &&
            str_starts_with(
                strtolower($resolvedBackupPath),
                strtolower($resolvedDirectory . DIRECTORY_SEPARATOR)
            ),
            404
        );

        $activeSchoolYear = SchoolYear::active();

        $schoolYearLabel = $activeSchoolYear
            ? $activeSchoolYear->year_name
            : 'NoActiveSchoolYear';

        try {
            // Create a safety backup of the CURRENT database first.
            $preRestoreBackupPath = $backupService->createFullBackup(
                'PreRestore_' . $schoolYearLabel
            );

            if (
                !is_file($preRestoreBackupPath) ||
                filesize($preRestoreBackupPath) <= 0
            ) {
                throw new \RuntimeException(
                    'The pre-restore backup could not be verified. Restore was cancelled.'
                );
            }

            // Restore only after the safety backup has been verified.
            $backupService->restoreFromBackup($filename);

            Log::warning('Database restored by administrator.', [
                'restored_filename' => $filename,
                'pre_restore_backup' => basename($preRestoreBackupPath),
                'school_year' => $schoolYearLabel,
            ]);

            return redirect()
                ->route('admin.settings')
                ->with(
                    'success',
                    'Database restored successfully from ' . $filename .
                    '. A pre-restore backup was also created.'
                );

        } catch (Throwable $exception) {

            Log::error('Database restore failed.', [
                'restored_filename' => $filename,
                'school_year' => $schoolYearLabel,
                'error' => $exception->getMessage(),
            ]);

            return redirect()
                ->route('admin.settings')
                ->with(
                    'error',
                    'Database restore failed. The pre-restore backup, if created, remains available in Backup History. Check the application log before attempting another restore.'
                );
        }
    }

}
