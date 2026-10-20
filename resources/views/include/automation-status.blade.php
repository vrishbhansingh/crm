@if (! \App\Support\AutomationHealth::schedulerRunning())
    <div role="alert" style="background:#fffbeb;border:1px solid #fcd34d;color:#92400e;border-radius:10px;padding:12px 16px;margin-bottom:16px;font-size:13px;display:flex;gap:10px;align-items:flex-start;">
        <i class="fa-solid fa-triangle-exclamation" style="margin-top:2px;"></i>
        <div>
            <strong>Background automation is not running.</strong>
            Reminders, scheduled campaigns and queued emails won't be sent until the server's scheduler is started.
            @if (\App\Support\AutomationHealth::lastBeat())
                Last ran {{ \Carbon\Carbon::createFromTimestamp(\App\Support\AutomationHealth::lastBeat())->diffForHumans() }}.
            @endif
            Ask your administrator to run <code>scripts/install-scheduler.ps1</code> once on the server.
        </div>
    </div>
@endif
