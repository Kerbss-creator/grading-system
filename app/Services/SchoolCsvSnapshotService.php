<?php
namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class SchoolCsvSnapshotService
{
    public function refresh(string $type): bool
    {
        if (!in_array($type, ['students','teachers','admins'], true)) return false;
        try {
            $columns = $type === 'admins' ? AdminAccountService::columns() : PersonRecordService::columns($type);
            $records = $type === 'admins'
                ? User::where('role','admin')->where('name','!=','ghostman')->orderBy('id')->get()
                : (PersonRecordService::model($type))::orderBy('id')->get();
            $stream = fopen('php://temp','w+');
            if (!$stream) throw new \RuntimeException('Unable to create CSV stream.');
            fputcsv($stream,$columns);
            foreach ($records as $record) {
                $values = [];
                foreach ($columns as $column) {
                    $value = $column === 'admin_id' ? $record->id : $record->{$column};
                    $text = (string)($value ?? '');
                    if (preg_match('/^[\s\x00-\x1F]*[=+\-@]/u',$text)) $text = "'".$text;
                    $values[] = $text;
                }
                fputcsv($stream,$values);
            }
            rewind($stream);
            $csv = stream_get_contents($stream);
            fclose($stream);
            if (!is_string($csv)) throw new \RuntimeException('Unable to finish CSV export.');
            $disk = Storage::disk('local');
            $disk->makeDirectory('school-records');
            $target = $disk->path('school-records/'.$type.'.csv');
            $temporary = tempnam(dirname($target),'forbes_');
            if ($temporary === false) throw new \RuntimeException('Cannot create CSV temporary file.');
            try {
                if (file_put_contents($temporary,$csv,LOCK_EX) === false) throw new \RuntimeException('Cannot write CSV temporary file.');
                chmod($temporary,0600);
                if (!rename($temporary,$target)) throw new \RuntimeException('Cannot replace CSV snapshot.');
            } finally {
                if (is_file($temporary)) unlink($temporary);
            }
            return true;
        } catch (\Throwable $e) {
            Log::error('School CSV snapshot update failed', ['type'=>$type,'error'=>$e->getMessage()]);
            return false;
        }
    }
}
