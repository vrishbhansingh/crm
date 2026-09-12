<?php

namespace App\Http\Middleware;

use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken as Middleware;

class VerifyCsrfToken extends Middleware
{
    /**
     * The URIs that should be excluded from CSRF verification.
     *
     * @var array<int, string>
     */
    protected $except = [
        // External platforms (IndiaMART, Meta, etc.) POST here directly and
        // never carry a Laravel CSRF token — the URL's own opaque, unguessable
        // token is this endpoint's actual credential instead.
        'webhooks/leads/*',
    ];
}
