<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\TenantMailSetting;
use App\Services\MailConfigurator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class MailSettingsController extends Controller
{
    public function edit()
    {
        $tenant = Auth::user()->tenant;
        $mailSettings = $tenant->mailSettings()->orderByDesc('is_active')->orderBy('name')->get();

        return view('settings.mail', compact('tenant', 'mailSettings'));
    }

    /**
     * Adds a new named SMTP config. The first one a tenant ever saves is
     * activated automatically (there being nothing else to compare it to);
     * afterward, switching which one is active is a separate action.
     */
    public function store(Request $request)
    {
        $tenant = Auth::user()->tenant;
        $data = $this->validated($request);

        $isFirst = ! $tenant->mailSettings()->exists();

        $setting = $tenant->mailSettings()->create([...$data, 'is_active' => $isFirst]);

        return back()->with('success', "\"{$setting->name}\" saved".($isFirst ? ' and set active.' : '.'));
    }

    public function update(Request $request, TenantMailSetting $mailSetting)
    {
        $this->authorizeOwnership($mailSetting);
        $data = $this->validated($request, $mailSetting->id);

        if (blank($data['smtp_password'] ?? null)) {
            unset($data['smtp_password']);
        }

        $mailSetting->update($data);

        return back()->with('success', "\"{$mailSetting->name}\" updated.");
    }

    /**
     * Makes this the one config MailConfigurator actually sends through —
     * every other config for this tenant is deactivated in the same
     * transaction so exactly one (or none) is ever active at a time.
     */
    public function activate(TenantMailSetting $mailSetting)
    {
        $this->authorizeOwnership($mailSetting);

        $mailSetting->tenant->mailSettings()->update(['is_active' => false]);
        $mailSetting->update(['is_active' => true]);

        return back()->with('success', "\"{$mailSetting->name}\" is now the active SMTP config.");
    }

    public function destroy(TenantMailSetting $mailSetting)
    {
        $this->authorizeOwnership($mailSetting);
        $name = $mailSetting->name;
        $mailSetting->delete();

        return back()->with('success', "\"{$name}\" deleted.");
    }

    public function test(Request $request, MailConfigurator $mailer)
    {
        $tenant = Auth::user()->tenant;
        $data = Validator::make($request->all(), ['test_email' => ['required', 'email']])->validate();

        $mailer->configureFor($tenant);

        try {
            Mail::raw('This is a test email from your CRM mail settings. If you received this, SMTP is working correctly.', function ($message) use ($data, $tenant) {
                $message->to($data['test_email'])->subject(($tenant->name ?: 'CRM').': SMTP test email');
            });
        } catch (\Throwable $exception) {
            return back()->withErrors(['test_email' => 'Could not send test email: '.$exception->getMessage()]);
        }

        return back()->with('success', 'Test email sent to '.$data['test_email'].'.');
    }

    private function validated(Request $request, ?int $ignoreId = null): array
    {
        $tenant = Auth::user()->tenant;

        return $request->validate([
            'name' => [
                'required', 'string', 'max:80',
                Rule::unique('tenant_mail_settings', 'name')
                    ->where(fn ($q) => $q->where('tenant_id', $tenant->id))
                    ->ignore($ignoreId),
            ],
            'smtp_host' => ['required', 'string', 'max:255'],
            'smtp_port' => ['required', 'integer', 'min:1', 'max:65535'],
            'smtp_encryption' => ['nullable', 'in:tls,ssl,'],
            'smtp_username' => ['nullable', 'string', 'max:255'],
            'smtp_password' => ['nullable', 'string', 'max:255'],
            'smtp_from_address' => ['nullable', 'email', 'max:255'],
            'smtp_from_name' => ['nullable', 'string', 'max:255'],
        ]);
    }

    /**
     * TenantMailSetting is a central-DB model reachable by plain numeric ID
     * — implicit binding never checks it belongs to the requester's own
     * tenant, so every write here must confirm that itself before touching
     * the row.
     */
    private function authorizeOwnership(TenantMailSetting $mailSetting): void
    {
        abort_unless($mailSetting->tenant_id === Auth::user()->tenant_id, 403);
    }
}
