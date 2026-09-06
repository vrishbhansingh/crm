@extends('layouts.platform')
@section('title','Mail settings')
@section('heading', $settings->isConfigured() ? 'Edit platform SMTP' : 'Add platform SMTP')
@section('content')

<style>
    .setup-card { max-width: 900px; }

    .preset-row {
        display: flex; align-items: flex-end; gap: 14px; flex-wrap: wrap;
        background: #f8fafc; border: 1px solid var(--border); border-radius: 10px;
        padding: 14px 16px; margin-bottom: 22px;
    }
    .preset-row .form-group { margin-bottom: 0; flex: 1 1 260px; }
    .preset-row label { margin-bottom: 4px; }
    .preset-hint { font-size: 12px; color: #64748b; margin-top: 6px; flex-basis: 100%; }

    .field-help {
        display: block; font-size: 11.5px; color: #94a3b8; margin-top: 5px; line-height: 1.4;
    }

    .provider-note {
        display: none; margin-top: 12px; padding: 10px 14px; border-radius: 8px;
        background: #fffbeb; border: 1px solid #fde68a; color: #92400e; font-size: 12.5px; line-height: 1.5;
    }
    .provider-note.show { display: block; }
    .provider-note code { background: rgba(0,0,0,.06); padding: 1px 5px; border-radius: 4px; }
</style>

<div class="mb-3">
    <a href="{{ route('superadmin.settings.mail.edit') }}" class="btn btn-sm btn-outline-secondary"><i class="fa fa-arrow-left"></i> Back to Mail Settings</a>
</div>

<div class="card setup-card"><div class="card-body">

<form method="post" action="{{ route('superadmin.settings.mail.update') }}" id="mailSettingsForm">
@csrf @method('PUT')

    <div class="preset-row">
        <div class="form-group">
            <label class="text-muted small mb-1">Quick setup — pick your email provider</label>
            <select class="form-control" id="providerPreset">
                <option value="">Select a provider (optional)…</option>
                <option value="gmail">Gmail / Google Workspace</option>
                <option value="outlook">Outlook / Office 365</option>
                <option value="yahoo">Yahoo Mail</option>
                <option value="zoho">Zoho Mail</option>
                <option value="sendgrid">SendGrid</option>
                <option value="custom">Other / custom server</option>
            </select>
        </div>
        <div class="preset-hint">
            This just fills in the host/port/encryption below for you — you can still edit anything by hand afterward.
        </div>
    </div>

    <div id="gmailNote" class="provider-note">
        <strong>Gmail won't accept your normal password here.</strong> With 2-Step Verification turned on for the account,
        go to <code>myaccount.google.com</code> → Security → 2-Step Verification → <strong>App passwords</strong>,
        create one for "Mail", and paste that 16-character code into the Password field below instead.
    </div>
    <div id="sendgridNote" class="provider-note">
        For SendGrid, the <strong>Username</strong> is literally the word <code>apikey</code> (not your SendGrid login email) —
        the <strong>Password</strong> is your actual SendGrid API key.
    </div>
    <div id="yahooNote" class="provider-note">
        Yahoo also requires an app password, not your regular one — generate it from your Yahoo Account Security page
        the same way Gmail does.
    </div>

    <div class="row">
        <div class="col-md-8 form-group">
            <label class="text-muted small mb-1">SMTP Host</label>
            <input class="form-control" name="smtp_host" id="smtp_host" placeholder="smtp.example.com" value="{{ old('smtp_host', $settings->smtp_host) }}">
            <small class="field-help">Your email provider's outgoing mail server address. Picking a provider above fills this in automatically.</small>
        </div>
        <div class="col-md-4 form-group">
            <label class="text-muted small mb-1">Port</label>
            <input class="form-control" type="number" name="smtp_port" id="smtp_port" placeholder="587" value="{{ old('smtp_port', $settings->smtp_port) }}">
            <small class="field-help"><strong>587</strong> is correct for almost everyone (works with TLS below).</small>
        </div>

        <div class="col-md-4 form-group">
            <label class="text-muted small mb-1">Encryption</label>
            <select class="form-control" name="smtp_encryption" id="smtp_encryption">
                <option value="" @selected(!$settings->smtp_encryption)>None</option>
                <option value="tls" @selected($settings->smtp_encryption==='tls')>TLS (recommended)</option>
                <option value="ssl" @selected($settings->smtp_encryption==='ssl')>SSL</option>
            </select>
            <small class="field-help">Leave on <strong>TLS</strong> unless your provider's instructions say otherwise.</small>
        </div>
        <div class="col-md-4 form-group">
            <label class="text-muted small mb-1">Username</label>
            <input class="form-control" name="smtp_username" value="{{ old('smtp_username', $settings->smtp_username) }}">
            <small class="field-help">Usually your full email address (e.g. <code>you@company.com</code>).</small>
        </div>
        <div class="col-md-4 form-group">
            <label class="text-muted small mb-1">Password</label>
            <input class="form-control" type="password" name="smtp_password" placeholder="{{ $settings->smtp_password ? 'Leave blank to keep current password' : '' }}">
            <small class="field-help">Not always your normal login password — see the note above for Gmail/Yahoo.</small>
        </div>

        <div class="col-md-6 form-group">
            <label class="text-muted small mb-1">From address</label>
            <input class="form-control" type="email" name="smtp_from_address" placeholder="noreply@yourplatform.com" value="{{ old('smtp_from_address', $settings->smtp_from_address) }}">
            <small class="field-help">The email address recipients will see this came from.</small>
        </div>
        <div class="col-md-6 form-group">
            <label class="text-muted small mb-1">From name</label>
            <input class="form-control" name="smtp_from_name" placeholder="CRM Platform" value="{{ old('smtp_from_name', $settings->smtp_from_name) }}">
            <small class="field-help">The display name recipients will see next to the address above.</small>
        </div>
    </div>

    <div class="mt-3">
        <button type="submit" class="btn btn-primary">Save platform mail settings</button>
        <a href="{{ route('superadmin.settings.mail.edit') }}" class="btn btn-secondary">Cancel</a>
    </div>
</form>

</div></div>

<script>
    const PRESETS = {
        gmail: { host: 'smtp.gmail.com', port: 587, encryption: 'tls', note: 'gmailNote' },
        outlook: { host: 'smtp.office365.com', port: 587, encryption: 'tls', note: null },
        yahoo: { host: 'smtp.mail.yahoo.com', port: 587, encryption: 'tls', note: 'yahooNote' },
        zoho: { host: 'smtp.zoho.com', port: 587, encryption: 'tls', note: null },
        sendgrid: { host: 'smtp.sendgrid.net', port: 587, encryption: 'tls', note: 'sendgridNote' },
        custom: { host: '', port: '', encryption: '', note: null },
    };

    document.getElementById('providerPreset').addEventListener('change', function() {
        document.querySelectorAll('.provider-note').forEach(el => el.classList.remove('show'));

        const preset = PRESETS[this.value];
        if (!preset) return;

        document.getElementById('smtp_host').value = preset.host;
        document.getElementById('smtp_port').value = preset.port;
        document.getElementById('smtp_encryption').value = preset.encryption;

        if (preset.note) {
            document.getElementById(preset.note).classList.add('show');
        }
    });
</script>

@endsection
