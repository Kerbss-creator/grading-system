<?php
namespace App\Console\Commands;

use App\Services\SchoolCsvSnapshotService;
use Illuminate\Console\Command;

class SyncSchoolCsv extends Command
{
    protected $signature = 'school:csv-sync';
    protected $description = 'Refresh private CSV copies of the current student, teacher, and administrator records.';

    public function handle(SchoolCsvSnapshotService $snapshots): int
    {
        foreach (['students','teachers','admins'] as $type) {
            if (!$snapshots->refresh($type)) {
                $this->error('Could not update '.$type.'.csv; check storage permissions and logs.');
                return self::FAILURE;
            }
            $this->info('Updated '.$type.'.csv');
        }
        return self::SUCCESS;
    }
}
