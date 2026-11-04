<?php
namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Validator;

class SchoolCsvService
{
    public function __construct(private PersonRecordService $records,private AdminAccountService $admins) {}

    public function columns(string $type): array
    {
        return $type === 'admins' ? AdminAccountService::columns() : PersonRecordService::columns($type);
    }

    public function parse(string $path,string $type): array
    {
        $headers = $this->columns($type);
        $handle = fopen($path,'rb');
        if (!$handle) throw new \RuntimeException('Cannot read uploaded CSV.');
        try {
            $actual = fgetcsv($handle);
            if (!$actual) throw new \RuntimeException('CSV must have headers.');
            $actual[0] = preg_replace('/^\xEF\xBB\xBF/','',(string)$actual[0]);
            $actual = array_map(fn($x)=>trim((string)$x),$actual);
            if (count($actual) !== count($headers) || count($actual) !== count(array_unique($actual)) || array_diff($headers,$actual) || array_diff($actual,$headers)) {
                throw new \RuntimeException('CSV headers must match: '.implode(', ',$headers));
            }
            $rows = [];
            $line = 1;
            while (($cells = fgetcsv($handle)) !== false) {
                $line++;
                if ($cells === [null] || !count(array_filter($cells,fn($v)=>trim((string)$v) !== ''))) continue;
                if (count($rows) >= 1000) throw new \RuntimeException('Maximum 1,000 records per upload.');
                if (count($cells) !== count($actual)) {
                    $rows[] = ['line'=>$line,'data'=>null,'errors'=>['Incorrect number of columns.']];
                    continue;
                }
                $values = array_combine($actual,array_map(fn($v)=>trim((string)$v),$cells));
                $values = array_map(fn($v)=>$v === '' ? null : $v,$values);
                $rules = $type === 'admins' ? AdminAccountService::rules() : PersonRecordService::rules($type);
                $validator = Validator::make($values,$rules);
                $rows[] = ['line'=>$line,'data'=>$values,'errors'=>$validator->fails() ? $validator->errors()->all() : []];
            }
            if (!$rows) throw new \RuntimeException('CSV has no data rows.');
            return $rows;
        } finally {
            fclose($handle);
        }
    }

    public function inspect(string $type,array $rows): array
    {
        if ($type === 'admins') return $this->inspectAdmins($rows);
        $model = PersonRecordService::model($type);
        $key = PersonRecordService::key($type);
        $results = [];
        $idCounts = [];
        $emailCounts = [];
        foreach ($rows as $candidate) {
            if (!$candidate['data'] || $candidate['errors']) continue;
            $candidateId = strtolower($candidate['data'][$key]);
            $candidateEmail = strtolower(PersonRecordService::accountEmail($type,$candidate['data']));
            $idCounts[$candidateId] = ($idCounts[$candidateId] ?? 0) + 1;
            $emailCounts[$candidateEmail] = ($emailCounts[$candidateEmail] ?? 0) + 1;
        }
        foreach ($rows as $row) {
            $data = $row['data'];
            $issues = $row['errors'];
            $state = 'invalid';
            $difference = [];
            if ($data && !$issues) {
                $id = strtolower($data[$key]);
                $email = strtolower(PersonRecordService::accountEmail($type,$data));
                if (($idCounts[$id] ?? 0) > 1) $issues[] = 'Duplicate ID in uploaded CSV.';
                if (($emailCounts[$email] ?? 0) > 1) $issues[] = 'Duplicate email in uploaded CSV.';
                if (!$issues) {
                    $existing = $model::withTrashed()->where($key,$data[$key])->first();
                    $account = $existing ? $this->records->linkedAccount($type,$existing) : null;
                    $occupied = User::withTrashed()->where('email',$email)->first();
                    if ($existing?->trashed()) $issues[] = 'This record is in Recycle Bin; restore it before import.';
                    if ($existing && (!$account || $account->trashed())) $issues[] = 'Existing record has a missing or deleted login account.';
                    if ($existing && $account && $account->role !== substr($type,0,-1)) $issues[] = 'Linked account role is incorrect.';
                    if ($occupied && (!$account || $occupied->id !== $account->id)) $issues[] = 'Email belongs to another account.';
                    if (!$issues) {
                        if (!$existing) $state = 'new';
                        else {
                            foreach ($this->columns($type) as $column) {
                                if ((string)($existing->{$column} ?? '') !== (string)($data[$column] ?? '')) {
                                    $difference[] = $column.': '.($existing->{$column} ?? '(blank)').' → '.($data[$column] ?? '(blank)');
                                }
                            }
                            $state = $difference ? 'changed' : 'unchanged';
                        }
                    }
                }
            }
            if ($issues) $state = 'conflict';
            $results[] = $row + ['state'=>$state,'issues'=>$issues,'differences'=>$difference];
        }
        return $results;
    }

    private function inspectAdmins(array $rows): array
    {
        $idCounts = [];
        $emailCounts = [];
        foreach ($rows as $row) {
            if (!$row['data'] || $row['errors']) continue;
            $id = $row['data']['admin_id'] ?? null;
            if ($id) $idCounts[$id] = ($idCounts[$id] ?? 0) + 1;
            $email = strtolower($row['data']['email']);
            $emailCounts[$email] = ($emailCounts[$email] ?? 0) + 1;
        }
        $out = [];
        foreach ($rows as $row) {
            $data = $row['data'];
            $issues = $row['errors'];
            $state = 'invalid';
            $difference = [];
            if ($data && !$issues) {
                $id = $data['admin_id'] ?? null;
                $email = strtolower($data['email']);
                if ($id && ($idCounts[$id] ?? 0) > 1) $issues[] = 'Duplicate admin ID in uploaded CSV.';
                if (($emailCounts[$email] ?? 0) > 1) $issues[] = 'Duplicate email in uploaded CSV.';
                $existing = $this->admins->matched($data);
                $occupied = User::withTrashed()->where('email',$data['email'])->first();
                if ($id && !$existing) $issues[] = 'Admin ID not found; clear ID to create a new administrator.';
                if ($existing && ($existing->role !== 'admin' || $existing->trashed())) $issues[] = 'Account is deleted or is not an administrator.';
                if ($existing && $existing->name === 'ghostman') $issues[] = 'The selected account cannot be managed through CSV.';
                if ($occupied && (!$existing || $occupied->id !== $existing->id)) $issues[] = 'Email belongs to a different account.';
                if (!$issues) {
                    if (!$existing) $state = 'new';
                    else {
                        foreach (['name','email'] as $column) {
                            if ($existing->{$column} !== $data[$column]) $difference[] = $column.': '.$existing->{$column}.' → '.$data[$column];
                        }
                        $state = $difference ? 'changed' : 'unchanged';
                    }
                }
            }
            if ($issues) $state = 'conflict';
            $out[] = $row + ['state'=>$state,'issues'=>$issues,'differences'=>$difference];
        }
        return $out;
    }
}
