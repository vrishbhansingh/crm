<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\EmailLog;
use App\Models\Tenant;
use Illuminate\Http\Request;

class PlatformEmailLogController extends Controller
{
    public function index(Request $request)
    {
        $query = EmailLog::query()->with('tenant:id,name')->latest('id');

        if ($request->filled('status')) {
            if ($request->input('status') === 'stuck') {
                $query->stuck();
            } else {
                $query->where('status', $request->input('status'));
            }
        }

        if ($request->filled('type')) {
            $query->where('type', $request->input('type'));
        }

        if ($request->filled('tenant_id')) {
            $request->input('tenant_id') === 'platform'
                ? $query->whereNull('tenant_id')
                : $query->where('tenant_id', $request->integer('tenant_id'));
        }

        if ($request->filled('search')) {
            $term = '%'.$request->string('search').'%';
            $query->where(function ($q) use ($term) {
                $q->where('to_email', 'like', $term)->orWhere('subject', 'like', $term);
            });
        }

        if ($request->filled('from')) {
            $query->whereDate('created_at', '>=', $request->date('from'));
        }
        if ($request->filled('to')) {
            $query->whereDate('created_at', '<=', $request->date('to'));
        }

        $logs = $query->paginate(30)->withQueryString();

        $stats = [
            'sent' => EmailLog::where('status', 'sent')->count(),
            'failed' => EmailLog::where('status', 'failed')->count(),
            'in_flight' => EmailLog::whereIn('status', ['queued', 'sending'])->count(),
            'stuck' => EmailLog::stuck()->count(),
        ];

        $tenants = Tenant::orderBy('name')->get(['id', 'name']);

        return view('platform.email-log', [
            'logs' => $logs,
            'stats' => $stats,
            'tenants' => $tenants,
            'filters' => $request->only(['status', 'type', 'tenant_id', 'search', 'from', 'to']),
        ]);
    }

    /**
     * Re-queues a failed — or stuck — campaign email as a brand new
     * attempt — only meaningful for campaign rows, which carry enough
     * context (campaign_id + recipient_id) to rebuild the job; anything
     * else (a password reset, an SMTP test) was a one-off, point-in-time
     * send with nothing left to retry. Dispatched as a standalone job, not
     * back into the original Bus::batch() — that batch may itself be
     * cancelled or long gone, and this new attempt shouldn't inherit
     * whatever state it's in.
     */
    public function retry(EmailLog $log)
    {
        $retryable = $log->status === 'failed' || $log->isStuck();

        if ($log->type !== 'campaign' || ! $retryable) {
            return back()->withErrors(['retry' => 'Only a failed or stuck campaign email can be retried.']);
        }

        $campaignId = $log->context['campaign_id'] ?? null;
        $recipientId = $log->context['recipient_id'] ?? null;
        $body = $log->context['body'] ?? null;

        if (! $campaignId || ! $recipientId || $body === null) {
            return back()->withErrors(['retry' => 'Missing context for this email — it cannot be retried.']);
        }

        // A stuck row was never actually failed, so its own status has to
        // be closed out explicitly — otherwise it just keeps sitting there
        // "stuck" forever alongside the fresh retry, double-counted in the
        // in-flight stat.
        if ($log->isStuck()) {
            $log->update(['status' => 'failed', 'error' => 'Timed out waiting for a queue worker — retried manually.']);
        }

        $newLog = EmailLog::create([
            'tenant_id' => $log->tenant_id,
            'type' => 'campaign',
            'to_email' => $log->to_email,
            'subject' => $log->subject,
            'status' => 'queued',
            'context' => $log->context,
            'queued_at' => now(),
        ]);

        \App\Jobs\SendCampaignEmailJob::dispatch(
            $log->tenant_id, $campaignId, $recipientId, $log->to_email,
            $log->subject ?? '', $body, $newLog->id,
        );

        return back()->with('success', 'Re-queued — it will send on the next queue run.');
    }
}
