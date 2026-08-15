<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PlatformMailSetting;
use App\Services\MailConfigurator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;

class PlatformMailSettingsController extends Controller
{
    public function edit()
    {
        $settings = PlatformMailSetting::current();

        return view('platform.mail', compact('settings'));
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

        return back()->with('success', 'Platform mail settings saved.');
    }

    public function test(Request $request, MailConfigurator $mailer)
    {
        $data = Validator::make($request->all(), ['test_email' => ['required', 'email']])->validate();

        $mailer->configureFor(null);

        try {
            Mail::raw('This is a test email from the CRM platform default mail settings. If you received this, SMTP is working correctly.', function ($message) use ($data) {
                $message->to($data['test_email'])->subject('CRM platform: SMTP test email');
            });
        } catch (\Throwable $exception) {
            return back()->withErrors(['test_email' => 'Could not send test email: '.$exception->getMessage()]);
        }

        return back()->with('success', 'Test email sent to '.$data['test_email'].'.');
    }
}
