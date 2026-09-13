<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\WhatsappAccount;
use App\Models\WhatsappCampaign;
use App\Services\WhatsApp\WhatsAppCampaignSender;
use App\Support\TenantContext;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class WhatsAppCampaignController extends Controller
{
    public function index()
    {
        $tenantId = TenantContext::id();
        $accounts = WhatsappAccount::where('tenant_id', $tenantId)->where('is_active', true)->get(['id', 'name', 'channel_type']);

        return view('whatsapp.campaigns', compact('accounts'));
    }

    public function data()
    {
        $campaigns = WhatsappCampaign::orderByDesc('id')->get()->map(fn (WhatsappCampaign $c) => [
            'id' => $c->id,
            'name' => $c->name,
            'message_type' => $c->message_type,
            'audience_type' => $c->audience_type,
            'status' => $c->status,
            'scheduled_at' => $c->scheduled_at,
            'sent_at' => $c->sent_at,
            'total_recipients' => $c->total_recipients,
            'sent_count' => $c->sent_count,
            'failed_count' => $c->failed_count,
        ]);

        return response()->json(['status' => true, 'data' => $campaigns]);
    }

    public function previewAudience(Request $request, WhatsAppCampaignSender $sender)
    {
        $request->validate(['audience_type' => 'required|in:leads,contacts,companies']);

        $count = $sender->previewCount($request->audience_type, (array) $request->get('filters', []));

        return response()->json(['status' => true, 'count' => $count]);
    }

    public function store(Request $request)
    {
        $tenantId = TenantContext::id();
        $data = $request->validate([
            'name' => 'required|string|max:150',
            'whatsapp_account_id' => 'required|integer',
            'message_type' => 'required|in:text,template',
            'template_name' => 'required_if:message_type,template|nullable|string|max:150',
            'body' => 'required_if:message_type,text|nullable|string|max:4096',
            'audience_type' => 'required|in:leads,contacts,companies',
            'filters' => 'nullable|array',
            'scheduled_at' => 'nullable|date|after:now',
        ]);

        $account = WhatsappAccount::where('tenant_id', $tenantId)->findOrFail($data['whatsapp_account_id']);

        $campaign = WhatsappCampaign::create([
            'created_by' => Auth::guard('web')->id(),
            'whatsapp_account_id' => $account->id,
            'name' => $data['name'],
            'message_type' => $data['message_type'],
            'template_name' => $data['template_name'] ?? null,
            'body' => $data['body'] ?? null,
            'audience_type' => $data['audience_type'],
            'audience_filters' => $data['filters'] ?? [],
            'status' => filled($data['scheduled_at'] ?? null) ? 'scheduled' : 'draft',
            'scheduled_at' => $data['scheduled_at'] ?? null,
        ]);

        return response()->json(['status' => true, 'message' => 'Campaign saved', 'data' => $campaign]);
    }

    public function destroy($id)
    {
        $campaign = WhatsappCampaign::findOrFail($id);

        if (in_array($campaign->status, ['sending', 'sent'], true)) {
            return response()->json(['status' => false, 'message' => 'A sent campaign cannot be deleted.'], 422);
        }

        $campaign->delete();

        return response()->json(['status' => true, 'message' => 'Campaign deleted']);
    }

    public function send($id, WhatsAppCampaignSender $sender)
    {
        $campaign = WhatsappCampaign::findOrFail($id);

        if (! in_array($campaign->status, ['draft', 'scheduled', 'failed'], true)) {
            return response()->json(['status' => false, 'message' => 'This campaign has already been sent.'], 422);
        }

        $account = WhatsappAccount::find($campaign->whatsapp_account_id);
        abort_if(! $account || $account->tenant_id !== TenantContext::id(), 404);

        $count = $sender->buildRecipients($campaign);

        if ($count === 0) {
            return response()->json(['status' => false, 'message' => "No recipients in this audience have a WhatsApp number on file."], 422);
        }

        $sender->send($campaign, $account);
        $campaign->refresh();

        return response()->json([
            'status' => true,
            'message' => "Sent to {$campaign->sent_count} of {$campaign->total_recipients} recipient(s)".($campaign->failed_count ? ", {$campaign->failed_count} failed" : ''),
            'data' => $campaign,
        ]);
    }
}
