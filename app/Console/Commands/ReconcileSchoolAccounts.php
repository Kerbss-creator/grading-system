<?php

namespace App\Console\Commands;

use App\Models\Student;
use App\Models\Teacher;
use App\Services\PersonRecordService;
use Illuminate\Console\Command;
use Illuminate\Validation\ValidationException;

class ReconcileSchoolAccounts extends Command
{
    protected $signature = 'forbes:reconcile-accounts {--apply : Create/link missing accounts (defaults to dry run)}';
    protected $description = 'Review records without linked login accounts, without exposing or generating visible passwords.';

    public function handle(PersonRecordService $records): int
    {
        $apply = (bool)$this->option('apply');
        $failed = 0;
        foreach (['students'=>Student::class,'teachers'=>Teacher::class] as $type=>$model) {
            $unlinked = $model::whereNull('user_id')->get();
            $this->line(ucfirst($type) . ': ' . $unlinked->count() . ' record(s) without a linked account.');
            if (!$apply) continue;
            foreach ($unlinked as $record) {
                try {
                    $records->save($type, $record->only(PersonRecordService::columns($type)), $record, 'account_reconcile');
                } catch (ValidationException $e) {
                    $failed++;
                    $this->warn($type . ' database row ' . $record->id . ': ' . implode('; ', $e->validator->errors()->all()));
                }
            }
        }
        if (!$apply) {
            $this->warn('Dry run: no records were changed. Use --apply ONLY after reviewing the MySQL database backup.');
        } else {
            $this->info('Account reconciliation completed. Reset passwords securely before enabling logins.');
        }
        return $failed ? self::FAILURE : self::SUCCESS;
    }
}
