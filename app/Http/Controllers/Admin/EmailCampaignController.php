<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\EmailCampaign;
use App\Models\EmailTemplate;
use App\Services\CampaignSender;
use App\Tenancy\TenantConnectionManager;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EmailCampaignController extends Controller
{
    public function index()
    {
        $templates = EmailTemplate::orderBy('name')->get(['id', 'name', 'subject']);

        return view('email_campaigns.index', compact('templates'));
    }

    public function data()
    {
        $campaigns = EmailCampaign::with('template:id,name')
            ->orderByDesc('id')
            ->get()
            ->map(fn (EmailCampaign $campaign) => [
                'id' => $campaign->id,
                'name' => $campaign->name,
                'template_name' => $campaign->template->name,
                'audience_type' => $campaign->audience_type,
                'status' => $campaign->status,
                'scheduled_at' => $campaign->scheduled_at,
                'sent_at' => $campaign->sent_at,
                'total_recipients' => $campaign->total_recipients,
                'sent_count' => $campaign->sent_count,
                'failed_count' => $campaign->failed_count,
            ]);

        return response()->json(['status' => true, 'data' => $campaigns]);
    }

    public function show(EmailCampaign $emailCampaign)
    {
        $emailCampaign->load('template', 'recipients');

        return response()->json(['status' => true, 'data' => $emailCampaign]);
    }

    /**
     * Live "N recipients match" count while composing a campaign, before
     * anything is saved.
     */
    public function previewAudience(Request $request, CampaignSender $sender)
    {
        $request->validate(['audience_type' => 'required|in:leads,contacts,companies']);

        $count = $sender->previewCount($request->audience_type, (array) $request->get('filters', []));

        return response()->json(['status' => true, 'count' => $count]);
    }

    public function store(Request $request, TenantConnectionManager $connections)
    {
        // exists: checks the app's DEFAULT connection, but EmailTemplate
        // resolves its own connection dynamically per tenant (see
        // UsesTenantConnection) — without naming that connection here,
        // this would validate against the wrong database and always fail.
        $templateConnection = $connections->connectionName();

        $data = $request->validate([
            'name' => 'required|string|max:150',
            'email_template_id' => "required|exists:{$templateConnection}.email_templates,id",
            'subject' => 'nullable|string|max:255',
            'audience_type' => 'required|in:leads,contacts,companies',
            'filters' => 'nullable|array',
            'scheduled_at' => 'nullable|date|after:now',
        ]);

        $campaign = EmailCampaign::create([
            'created_by' => Auth::guard('web')->id(),
            'email_template_id' => $data['email_template_id'],
            'name' => $data['name'],
            'subject' => $data['subject'] ?? null,
            'audience_type' => $data['audience_type'],
            'audience_filters' => $data['filters'] ?? [],
            'status' => filled($data['scheduled_at'] ?? null) ? 'scheduled' : 'draft',
            'scheduled_at' => $data['scheduled_at'] ?? null,
        ]);

        return response()->json(['status' => true, 'message' => 'Campaign saved', 'data' => $campaign]);
    }

    public function destroy(EmailCampaign $emailCampaign)
    {
        if (in_array($emailCampaign->status, ['sending', 'sent'], true)) {
            return response()->json(['status' => false, 'message' => 'A sent campaign cannot be deleted.'], 422);
        }

        $emailCampaign->delete();

        return response()->json(['status' => true, 'message' => 'Campaign deleted']);
    }

    public function send(EmailCampaign $emailCampaign, CampaignSender $sender)
    {
        if (! in_array($emailCampaign->status, ['draft', 'scheduled', 'failed'], true)) {
            return response()->json(['status' => false, 'message' => 'This campaign has already been sent.'], 422);
        }

        $count = $sender->buildRecipients($emailCampaign);

        if ($count === 0) {
            return response()->json(['status' => false, 'message' => 'No recipients match this campaign\'s audience filters.'], 422);
        }

        $sender->send($emailCampaign);
        $emailCampaign->refresh();

        return response()->json([
            'status' => true,
            'message' => "Sent to {$emailCampaign->sent_count} of {$emailCampaign->total_recipients} recipient(s)".($emailCampaign->failed_count ? ", {$emailCampaign->failed_count} failed" : ''),
            'data' => $emailCampaign,
        ]);
    }
}
