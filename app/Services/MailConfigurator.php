<?php

namespace App\Services;

use App\Models\PlatformMailSetting;
use App\Models\Tenant;

/**
 * Points the `mail.mailers.smtp` config at the right SMTP credentials before
 * a mailable/notification is sent: the tenant's own SMTP if it configured
 * and enabled one, otherwise the platform default (used for every Super
 * Admin email and for tenants that never set up their own SMTP).
 */
class MailConfigurator
{
    public function configureFor(?Tenant $tenant): void
    {
        if ($tenant && $tenant->hasUsableSmtp()) {
            $this->apply(
                host: $tenant->smtp_host,
                port: $tenant->smtp_port,
                encryption: $tenant->smtp_encryption,
                username: $tenant->smtp_username,
                password: $tenant->smtp_password,
                fromAddress: $tenant->smtp_from_address ?: config('mail.from.address'),
                fromName: $tenant->smtp_from_name ?: ($tenant->name ?: config('mail.from.name')),
            );

            return;
        }

        $platform = PlatformMailSetting::current();

        if ($platform->isConfigured()) {
            $this->apply(
                host: $platform->smtp_host,
                port: $platform->smtp_port,
                encryption: $platform->smtp_encryption,
                username: $platform->smtp_username,
                password: $platform->smtp_password,
                fromAddress: $platform->smtp_from_address ?: config('mail.from.address'),
                fromName: $platform->smtp_from_name ?: config('mail.from.name'),
            );
        }

        // Neither configured — leave the .env-based mail.mailers.smtp config
        // untouched so local/staging environments without any DB-configured
        // SMTP still send through whatever the server admin set in .env.
    }

    private function apply(?string $host, ?int $port, ?string $encryption, ?string $username, ?string $password, ?string $fromAddress, ?string $fromName): void
    {
        config([
            'mail.mailers.smtp.host' => $host,
            'mail.mailers.smtp.port' => $port,
            'mail.mailers.smtp.encryption' => $encryption ?: null,
            'mail.mailers.smtp.username' => $username,
            'mail.mailers.smtp.password' => $password,
            'mail.default' => 'smtp',
            'mail.from.address' => $fromAddress,
            'mail.from.name' => $fromName,
        ]);
    }
}
