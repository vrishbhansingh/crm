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

    public function __construct(
        public string $mailSubject,
        public string $htmlBody,
    ) {}

    public function build(): self
    {
        return $this->subject($this->mailSubject)
            ->html($this->htmlBody);
    }
}
