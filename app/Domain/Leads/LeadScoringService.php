<?php

namespace App\Domain\Leads;

use App\Models\Lead;

/**
 * Simple rule-based lead score (0-100), Phase 4. Weights are fixed here
 * rather than admin-configurable (unlike Master Data) — scope kept small
 * for this pass; revisit if scoring rules need to vary per tenant later.
 */
class LeadScoringService
{
    private const TYPE_WEIGHTS = [
        'hot' => 40,
        'existing_customer' => 35,
        'warm' => 25,
        'inquiry' => 15,
        'cold' => 10,
    ];

    private const SOURCE_WEIGHTS = [
        'referral' => 30,
        'partner' => 25,
        'website' => 20,
        'google_ads' => 15,
        'facebook_ads' => 15,
        'linkedIn' => 15,
        'email' => 10,
        'cold_call' => 8,
        'other' => 5,
    ];

    public function score(Lead $lead): int
    {
        $score = self::TYPE_WEIGHTS[$lead->lead_type] ?? 10;
        $score += self::SOURCE_WEIGHTS[$lead->lead_source] ?? 5;

        if ($lead->budget !== null) {
            $score += match (true) {
                $lead->budget >= 100000 => 30,
                $lead->budget >= 50000 => 20,
                $lead->budget > 0 => 10,
                default => 0,
            };
        }

        return min(100, max(0, $score));
    }

    public static function band(?int $score): string
    {
        return match (true) {
            $score === null => 'unscored',
            $score >= 70 => 'hot',
            $score >= 40 => 'warm',
            default => 'cold',
        };
    }
}
