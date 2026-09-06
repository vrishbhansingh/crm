@extends('layouts.platform')
@section('title','Mail settings')
@section('heading','Platform default mail settings')
@section('content')

<style>
    .mail-intro { color: #64748b; font-size: 13.5px; max-width: 900px; margin-bottom: 20px; }

    .smtp-row {
        display: flex; justify-content: space-between; align-items: center; gap: 16px; flex-wrap: wrap;
    }
    .smtp-main { display: flex; align-items: center; gap: 14px; }
    .smtp-icon {
        width: 40px; height: 40px; border-radius: 10px; flex-shrink: 0;
        background: #eef2ff; color: #4338ca; display: flex; align-items: center; justify-content: center; font-size: 16px;
    }
    .smtp-title { font-weight: 700; font-size: 14.5px; color: #1f2937; display: flex; align-items: center; gap: 8px; }
    .smtp-detail { font-size: 12.5px; color: #64748b; margin-top: 2px; }
    .badge-configured { background: #dcfce7; color: #166534; font-size: 10.5px; font-weight: 800; text-transform: uppercase; letter-spacing: .03em; padding: 3px 9px; border-radius: 999px; }
    .smtp-actions { display: flex; gap: 8px; flex-wrap: wrap; }

    .empty-state { text-align: center; padding: 34px 20px; color: #64748b; }
    .empty-state i { font-size: 30px; color: #cbd5e1; margin-bottom: 12px; display: block; }

    /* The card itself spans the full content width (no more big empty gap
       on wide screens), but the step text stays capped for readability —
       a paragraph stretched across 1600px is hard to read either way. */
    .guide-steps { list-style: none; margin: 0; padding: 0; max-width: 820px; counter-reset: guide-step; }
    .guide-steps > li {
        counter-increment: guide-step;
        position: relative; padding: 0 0 18px 40px; margin-bottom: 18px;
        border-bottom: 1px dashed var(--border);
    }
    .guide-steps > li:last-child { padding-bottom: 0; margin-bottom: 0; border-bottom: none; }
    .guide-steps > li::before {
        content: counter(guide-step);
        position: absolute; left: 0; top: 0;
        width: 26px; height: 26px; border-radius: 50%;
        background: #eef2ff; color: #4338ca; font-weight: 700; font-size: 13px;
        display: flex; align-items: center; justify-content: center;
    }
    .guide-steps h6 { font-size: 14px; font-weight: 700; margin-bottom: 4px; color: #1f2937; }
    .guide-steps p { font-size: 13px; color: #64748b; margin: 0; line-height: 1.55; }
    .guide-steps code { background: #f1f5f9; padding: 1px 5px; border-radius: 4px; }

    .guide-troubleshoot {
        margin-top: 6px; background: #f8fafc; border: 1px solid var(--border); border-radius: 8px;
        padding: 10px 14px; font-size: 12.5px; color: #475569;
    }
    .guide-troubleshoot ul { margin: 4px 0 0; padding-left: 18px; }
</style>

<p class="mail-intro">
    This is the fallback SMTP used to send <strong>every Super Admin email</strong> (like your own password resets)
    and any company that hasn't set up its own mail server yet.
</p>

<div class="card setup-card mb-4"><div class="card-body">

@if($settings->isConfigured())
    <div class="smtp-row">
        <div class="smtp-main">
            <div class="smtp-icon"><i class="fa fa-envelope"></i></div>
            <div>
                <div class="smtp-title">{{ $settings->smtp_host }}:{{ $settings->smtp_port }} <span class="badge-configured">Configured</span></div>
                <div class="smtp-detail">
                    @if($settings->smtp_encryption){{ strtoupper($settings->smtp_encryption) }} · @endif
                    @if($settings->smtp_from_name || $settings->smtp_from_address)
                        From: {{ $settings->smtp_from_name ?: '—' }} &lt;{{ $settings->smtp_from_address ?: '—' }}&gt;
                    @else
                        No "from" address set yet
                    @endif
                </div>
            </div>
        </div>
        <div class="smtp-actions">
            <form method="post" action="{{ route('superadmin.settings.mail.test') }}">
                @csrf
                <button class="btn btn-sm btn-outline-success"><i class="fa fa-paper-plane"></i> Test</button>
            </form>
            <a href="{{ route('superadmin.settings.mail.form') }}" class="btn btn-sm btn-outline-secondary"><i class="fa fa-pencil"></i> Edit</a>
            <form method="post" action="{{ route('superadmin.settings.mail.destroy') }}" onsubmit="return confirm('Remove the platform SMTP configuration? Companies without their own SMTP will stop being able to send email until this is set up again.');">
                @csrf @method('DELETE')
                <button class="btn btn-sm btn-outline-danger"><i class="fa fa-trash"></i> Delete</button>
            </form>
        </div>
    </div>
@else
    <div class="empty-state">
        <i class="fa fa-envelope-o"></i>
        No SMTP configured yet — Super Admin emails and any company without its own SMTP currently can't send mail.
        <div class="mt-3">
            <a href="{{ route('superadmin.settings.mail.form') }}" class="btn btn-primary btn-sm"><i class="fa fa-plus"></i> Add SMTP</a>
        </div>
    </div>
@endif

</div></div>

<div class="card guide-card"><div class="card-body">
    <h5 class="mb-3"><i class="fa fa-list-ol"></i> How to set this up, step by step</h5>
    <ol class="guide-steps">
        <li>
            <h6>Click Add SMTP (or Edit if one's already set up)</h6>
            <p>That opens the setup form on its own page, with a <strong>Quick setup</strong> dropdown that fills in the Host, Port, and Encryption for common providers. Don't see yours? Choose <strong>Other / custom server</strong> and check your provider's own help pages for the exact SMTP host and port — search "[your provider] SMTP settings".</p>
        </li>
        <li>
            <h6>Get the right password — not always your login password</h6>
            <p>Gmail, Google Workspace, and Yahoo all block your normal password here once 2-Step Verification is on — you need a separate <strong>App Password</strong> instead (the form shows exactly where to generate one once you pick that provider). Services like SendGrid or Mailgun use an <strong>API key</strong> as the password, with the username set to something fixed like <code>apikey</code> — their dashboard will tell you the exact value.</p>
        </li>
        <li>
            <h6>Fill in Username, From address, and From name, then save</h6>
            <p>Username is usually your full email address. From address/name is what every recipient sees as the sender — use a real, working address on the same domain if your provider requires it (some reject a "From" address that doesn't match the account you're sending as).</p>
        </li>
        <li>
            <h6>Click Test on the row above — and actually check your inbox</h6>
            <p>It sends to your own Super Admin email. A message at the top of the page tells you immediately if it failed, with the real reason. If it says sent, check your inbox <strong>and your spam/junk folder</strong> — a first email from a new sender sometimes lands there.</p>
            <div class="guide-troubleshoot">
                If the test email fails, the error shown is usually one of these:
                <ul>
                    <li><strong>Authentication failed</strong> — wrong password/app password, or username doesn't match the account.</li>
                    <li><strong>Connection could not be established / timed out</strong> — the Host or Port is wrong, or something on this server's network is blocking outbound mail — double-check for typos first.</li>
                    <li><strong>Certificate / TLS errors</strong> — try switching Encryption between TLS and SSL, matching what your provider's docs specify for the port you're using.</li>
                </ul>
            </div>
        </li>
    </ol>
</div></div>

@endsection
