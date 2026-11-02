<?php
namespace App\Http\Controllers;

use App\Models\User;
use App\Services\ActivityRecorder;
use App\Services\AdminAccountService;
use App\Services\PersonRecordService;
use App\Services\SchoolCsvService;
use App\Services\SchoolCsvSnapshotService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class SchoolCsvController extends Controller
{
    public function __construct(private SchoolCsvService $csv,private PersonRecordService $records,private AdminAccountService $admins,private ActivityRecorder $audit,private SchoolCsvSnapshotService $snapshot) {}

    public function index(string $type)
    {
        $this->type($type);
        return view('admin.csv-import',['type'=>$type,'columns'=>$this->csv->columns($type),'preview'=>null]);
    }

    public function preview(Request $request,string $type)
    {
        $this->type($type);
        foreach (Storage::disk('local')->files('imports/previews') as $stale) {
            if (Storage::disk('local')->lastModified($stale) < now()->subHour()->timestamp) Storage::disk('local')->delete($stale);
        }
        $data = $request->validate(['file'=>'required|file|mimes:csv,txt|max:2048']);
        $path = $data['file']->store('imports/previews','local');
        try {
            $rows = $this->csv->parse(Storage::disk('local')->path($path),$type);
            $preview = $this->csv->inspect($type,$rows);
        } catch (\Throwable $e) {
            Storage::disk('local')->delete($path);
            return back()->withErrors(['file'=>$e->getMessage()]);
        }
        $old = $request->session()->get('school_import');
        if ($old && isset($old['path'])) Storage::disk('local')->delete($old['path']);
        $token = (string)Str::uuid();
        $request->session()->put('school_import',[
            'type'=>$type,'path'=>$path,'token'=>$token,'expires'=>now()->addMinutes(20)->timestamp,
        ]);
        return view('admin.csv-import',['type'=>$type,'columns'=>$this->csv->columns($type),'preview'=>$preview,'token'=>$token]);
    }

    public function confirm(Request $request,string $type)
    {
        $this->type($type);
        $data = $request->validate([
            'token'=>'required|uuid',
            'mode'=>'required|in:add_only,add_and_update',
            'lines'=>'required|array|min:1|max:1000',
            'lines.*'=>'required|integer|distinct|min:2',
        ]);
        $job = $request->session()->get('school_import');
        if (!$job || $job['type'] !== $type || $job['token'] !== $data['token'] || now()->timestamp > $job['expires'] || !Storage::disk('local')->exists($job['path'])) {
            throw ValidationException::withMessages(['file'=>'Preview expired. Upload the CSV again.']);
        }
        $rows = $this->csv->parse(Storage::disk('local')->path($job['path']),$type);
        $preview = $this->csv->inspect($type,$rows);
        $selected = array_map('intval',$data['lines']);
        $valid = [];
        foreach ($preview as $row) {
            if (!in_array($row['line'],$selected,true)) continue;
            $valid[] = $row['line'];
            if (!in_array($row['state'],$data['mode'] === 'add_only' ? ['new'] : ['new','changed'],true)) {
                throw ValidationException::withMessages(['file'=>'Selected data has conflicts or was changed. Upload and preview again.']);
            }
        }
        if (count($valid) !== count($selected)) throw ValidationException::withMessages(['lines'=>'Invalid selected CSV rows.']);
        $count = ['created'=>0,'updated'=>0];
        DB::transaction(function () use ($preview,$selected,$type,&$count) {
            foreach ($preview as $row) {
                if (!in_array($row['line'],$selected,true)) continue;
                if ($type === 'admins') {
                    $existing = $this->admins->matched($row['data']);
                    $this->admins->save($row['data'],$existing,'csv_import');
                } else {
                    $model = PersonRecordService::model($type);
                    $key = PersonRecordService::key($type);
                    $existing = $model::where($key,$row['data'][$key])->first();
                    $this->records->save($type,$row['data'],$existing,'csv_import');
                }
                $count[$existing ? 'updated' : 'created']++;
            }
            $this->audit->record('csv_import',$type,null,null,null,$count);
        });
        Storage::disk('local')->delete($job['path']);
        $request->session()->forget('school_import');
        $synced = $this->snapshot->refresh($type);
        $message = "Imported {$count['created']} new and {$count['updated']} updated {$type} records.";
        if ($type === 'admins' && $count['created']) $message .= ' New administrators need a password set through User Management before login.';
        if (!$synced) $message .= ' Private CSV mirror could not be refreshed; download a new export and check Laravel logs.';
        return redirect()->route('admin.imports.index',$type)->with('success',$message);
    }

    public function export(string $type)
    {
        $this->type($type);
        $columns = $this->csv->columns($type);
        $query = $type === 'admins'
            ? User::where('role','admin')->where('name','!=','ghostman')->orderBy('id')
            : (PersonRecordService::model($type))::orderBy('id');
        $rows = $query->get();
        $this->audit->record('csv_export',$type,null,null,null,['rows'=>$rows->count()]);
        return response()->streamDownload(function () use ($rows,$columns) {
            $out = fopen('php://output','wb');
            fwrite($out,"\xEF\xBB\xBF");
            fputcsv($out,$columns);
            foreach ($rows as $record) {
                $values = [];
                foreach ($columns as $column) {
                    $value = $column === 'admin_id' ? $record->id : $record->{$column};
                    $text = (string)($value ?? '');
                    if (preg_match('/^[\s\x00-\x1F]*[=+\-@]/u',$text)) $text = "'".$text;
                    $values[] = $text;
                }
                fputcsv($out,$values);
            }
            fclose($out);
        },$type.'-'.now()->format('Y-m-d-His').'.csv',['Content-Type'=>'text/csv; charset=UTF-8','Cache-Control'=>'no-store, private']);
    }

    private function type(string $type): void
    {
        abort_unless(in_array($type,['students','teachers','admins'],true),404);
    }
}
