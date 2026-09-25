<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

/**
 * The subject/body arrive already variable-resolved (TemplateVariableResolver
 * ran per-recipient before this is built) — this class just renders them,
 * it doesn't know anything about leads, deals, or templates.
 */
class CampaignMail extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * @param  array<int, array{path: string, name: string, mime: ?string}>  $filesToAttach
     *         Absolute local filesystem paths — see CampaignSender::send(),
     *         which resolves each EmailTemplateAttachment's Storage-disk
     *         path once before dispatching, so this class and the job that
     *         builds it never need database or disk-config access of their
     *         own for attachments. Named $filesToAttach rather than
     *         $attachments — Mailable already declares that property name
     *         itself (populated by ->attach() below), and redeclaring it
     *         here is a fatal type error, not just a shadowing warning.
     */
    public function __construct(
        public string $mailSubject,
        public string $htmlBody,
        public array $filesToAttach = [],
    ) {}

    public function build(): self
    {
        $mail = $this->subject($this->mailSubject)
            ->html($this->htmlBody);

        foreach ($this->filesToAttach as $file) {
            $mail->attach($file['path'], [
                'as' => $file['name'],
                'mime' => $file['mime'] ?? null,
            ]);
        }

        return $mail;
    }
}
