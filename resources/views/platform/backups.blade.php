@extends('layouts.platform')
@section('title','Backups')
@section('heading','Company database backups')
@section('content')

<style>
    .backup-row { display: flex; justify-content: space-between; align-items: center; gap: 16px; flex-wrap: wrap; padding: 14px 0; border-top: 1px solid #f1f5f9; }
    .backup-row:first-child { border-top: none; }
    .backup-name { font-weight: 700; font-size: 13.5px; color: #1f2937; word-break: break-all; }
    .backup-meta { font-size: 12px; color: #94a3b8; margin-top: 2px; }
    .backup-actions { display: flex; gap: 8px; flex-shrink: 0; }
    .empty-state { text-align: center; padding: 34px 20px; color: #64748b; }
    .empty-state i { font-size: 28px; color: #cbd5e1; margin-bottom: 12px; display: block; }
</style>

<p class="text-muted mb-4" style="font-size:13.5px;max-width:760px">
    A full SQL backup is saved here automatically before "Delete company" drops a company's database.
    These files live on the server's disk, not in any database — delete the ones you no longer need to
    free up space once you're confident you won't need to restore them.
</p>

<div class="card"><div class="card-body">
@if($backups->isEmpty())
    <div class="empty-state">
        <i class="fa fa-database"></i>
        No backups yet — one is created automatically the first time you delete a company.
    </div>
@else
    @foreach($backups as $backup)
        <div class="backup-row">
            <div>
                <div class="backup-name">{{ $backup['name'] }}</div>
                <div class="backup-meta">{{ number_format($backup['size'] / 1024, 1) }} KB · {{ \Illuminate\Support\Carbon::createFromTimestamp($backup['modified_at'])->format('d M Y, h:i A') }}</div>
            </div>
            <div class="backup-actions">
                <a class="btn btn-sm btn-outline-primary" href="{{ route('superadmin.backups.download', $backup['name']) }}"><i class="fa fa-download"></i> Download</a>
                <form method="post" action="{{ route('superadmin.backups.destroy', $backup['name']) }}" onsubmit="return confirm('Permanently delete this backup file? This cannot be undone.');">
                    @csrf @method('DELETE')
                    <button class="btn btn-sm btn-outline-danger"><i class="fa fa-trash"></i> Delete</button>
                </form>
            </div>
        </div>
    @endforeach
@endif
</div></div>

@endsection
