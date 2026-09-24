<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PlatformMailSetting;
use App\Services\EmailLogger;
use App\Services\MailConfigurator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;

class PlatformMailSettingsController extends Controller
{
    /**
     * Overview: shows the one platform SMTP config as a row (Test/Edit/
     * Delete) if it's set up, or an empty state pointing at the add form —
     * kept separate from the form itself so "is something configured or
     * not" is obvious at a glance instead of an inline form that looks the
     * same whether it holds real values or not.
     */
    public function index()
    {
        $settings = PlatformMailSetting::current();

        return view('platform.mail', compact('settings'));
    }

    public function form()
    {
        $settings = PlatformMailSetting::current();

        return view('platform.mail-form', compact('settings'));
    }

    public function update(Request $request)
    {
        $settings = PlatformMailSetting::current();

        $data = $request->validate([
            'smtp_host' => ['nullable', 'string', 'max:255'],
            'smtp_port' => ['nullable', 'integer', 'min:1', 'max:65535'],
            'smtp_encryption' => ['nullable', 'in:tls,ssl,'],
            'smtp_username' => ['nullable', 'string', 'max:255'],
            'smtp_password' => ['nullable', 'string', 'max:255'],
            'smtp_from_address' => ['nullable', 'email', 'max:255'],
            'smtp_from_name' => ['nullable', 'string', 'max:255'],
        ]);

        $update = [
            'smtp_host' => $data['smtp_host'] ?? null,
            'smtp_port' => $data['smtp_port'] ?? null,
            'smtp_encryption' => $data['smtp_encryption'] ?: null,
            'smtp_username' => $data['smtp_username'] ?? null,
            'smtp_from_address' => $data['smtp_from_address'] ?? null,
            'smtp_from_name' => $data['smtp_from_name'] ?? null,
        ];

        if (filled($data['smtp_password'] ?? null)) {
            $update['smtp_password'] = $data['smtp_password'];
        }

        $settings->update($update);

        return redirect()->route('superadmin.settings.mail.edit')->with('success', 'Platform mail settings saved.');
    }

    public function destroy()
    {
        PlatformMailSetting::current()->update([
            'smtp_host' => null,
            'smtp_port' => null,
            'smtp_encryption' => null,
            'smtp_username' => null,
            'smtp_password' => null,
            'smtp_from_address' => null,
            'smtp_from_name' => null,
        ]);

        return redirect()->route('superadmin.settings.mail.edit')->with('success', 'Platform mail settings removed.');
    }

    /**
     * Sends to whatever address is typed in the Test field, defaulting to
     * the Super Admin's own email so the field arrives pre-filled and a
     * plain click still works like before.
     */
    public function test(Request $request, MailConfigurator $mailer, EmailLogger $emailLogger)
    {
        $data = $request->validate([
            'test_email' => ['nullable', 'email', 'max:255'],
        ]);

        $to = ($data['test_email'] ?? null) ?: Auth::guard('web')->user()->email;
        $subject = 'CRM platform: SMTP test email';
        $mailer->configureFor(null);

        try {
            $emailLogger->sync('smtp_test', null, $to, $subject, function () use ($to, $subject) {
                Mail::raw('This is a test email from the CRM platform default mail settings. If you received this, SMTP is working correctly.', function ($message) use ($to, $subject) {
                    $message->to($to)->subject($subject);
                });
            });
        } catch (\Throwable $exception) {
            return back()->withErrors(['test' => 'Could not send test email: '.$exception->getMessage()]);
        }

        return back()->with('success', 'Test email sent to '.$to.'.');
    }
}
