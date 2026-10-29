<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\Contact;
use App\Models\EmailCampaign;
use App\Models\EmailCampaignEvent;
use App\Models\EmailCampaignRecipient;
use App\Models\Lead;
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
                'opened_count' => $campaign->opened_count,
                'clicked_count' => $campaign->clicked_count,
            ]);

        return response()->json(['status' => true, 'data' => $campaigns]);
    }

    // Plain $id + a manual lookup throughout this controller, not
    // route-model-binding: implicit binding resolves during
    // SubstituteBindings, which runs before ActivateTenantDatabase — too
    // early for a model whose connection is only known once the tenant DB
    // is active for this request.
    public function show($id)
    {
        $emailCampaign = EmailCampaign::with('template', 'recipients')->findOrFail($id);

        return response()->json(['status' => true, 'data' => $emailCampaign]);
    }

    /** The campaign report page (who got it, opened it, clicked what, and when). */
    public function report($id)
    {
        $campaign = EmailCampaign::with('template:id,name')->findOrFail($id);

        return view('email_campaigns.report', ['campaign' => $campaign]);
    }

    /**
     * JSON for the report: headline numbers, most-clicked links, a paged and
     * filterable recipient table, and the latest opens/clicks as a timeline.
     * filter: all | opened | clicked | not_opened | failed | pending
     */
    public function reportData(Request $request, $id)
    {
        $campaign = EmailCampaign::findOrFail($id);
        $filter = $request->query('filter', 'all');
        $search = trim((string) $request->query('q', ''));

        $base = EmailCampaignRecipient::where('email_campaign_id', $campaign->id);
        $count = fn (?callable $scope = null) => (clone $base)->when($scope, $scope)->count();

        $sent = $count(fn ($q) => $q->where('status', 'sent'));
        $opened = $count(fn ($q) => $q->whereNotNull('opened_at'));
        $clicked = $count(fn ($q) => $q->whereNotNull('clicked_at'));
        $rate = fn (int $n) => $sent > 0 ? round($n / $sent * 100, 1) : 0;

        $query = (clone $base)->when($search !== '', fn ($q) => $q->where('email', 'like', '%'.addcslashes($search, '%_\\').'%'));
        match ($filter) {
            'opened' => $query->whereNotNull('opened_at'),
            'clicked' => $query->whereNotNull('clicked_at'),
            'not_opened' => $query->where('status', 'sent')->whereNull('opened_at'),
            'failed' => $query->where('status', 'failed'),
            'pending' => $query->where('status', 'pending'),
            default => null,
        };

        $page = $query->orderByRaw('COALESCE(clicked_at, opened_at, sent_at) DESC')->orderBy('id')->paginate(25);
        $names = $this->recipientNames($page->getCollection());

        $topLinks = EmailCampaignEvent::where('email_campaign_id', $campaign->id)->where('type', 'click')
            ->selectRaw('url, COUNT(*) as clicks, COUNT(DISTINCT email_campaign_recipient_id) as people')
            ->groupBy('url')->orderByDesc('clicks')->limit(5)->get();

        $timeline = EmailCampaignEvent::where('email_campaign_id', $campaign->id)->with('recipient:id,email,recipient_type,recipient_id')
            ->orderByDesc('id')->limit(25)->get();
        $timelineNames = $this->recipientNames($timeline->pluck('recipient')->filter()->values());

        return response()->json(['status' => true, 'data' => [
            'campaign' => ['id' => $campaign->id, 'name' => $campaign->name, 'status' => $campaign->status, 'sent_at' => optional($campaign->sent_at)->toDayDateTimeString()],
            'summary' => [
                'total' => $count(), 'sent' => $sent, 'failed' => $count(fn ($q) => $q->where('status', 'failed')), 'pending' => $count(fn ($q) => $q->where('status', 'pending')),
                'opened' => $opened, 'open_rate' => $rate($opened), 'clicked' => $clicked, 'click_rate' => $rate($clicked),
            ],
            'top_links' => $topLinks,
            'recipients' => $page->getCollection()->map(fn ($r) => [
                'id' => $r->id, 'name' => $names[$r->recipient_type.':'.$r->recipient_id] ?? null, 'email' => $r->email, 'type' => $r->recipient_type,
                'status' => $r->status, 'error' => $r->error, 'sent_at' => optional($r->sent_at)->toDayDateTimeString(),
                'opened_at' => optional($r->opened_at)->toDayDateTimeString(), 'open_count' => $r->open_count,
                'clicked_at' => optional($r->clicked_at)->toDayDateTimeString(), 'click_count' => $r->click_count,
            ])->values(),
            'pagination' => ['page' => $page->currentPage(), 'last_page' => $page->lastPage(), 'total' => $page->total()],
            'timeline' => $timeline->map(fn ($e) => [
                'type' => $e->type, 'url' => $e->url, 'when' => optional($e->created_at)->toDayDateTimeString(), 'ago' => optional($e->created_at)->diffForHumans(),
                'email' => $e->recipient?->email,
                'name' => $e->recipient ? ($timelineNames[$e->recipient->recipient_type.':'.$e->recipient->recipient_id] ?? null) : null,
            ])->values(),
        ]]);
    }

    /** "lead:5" => "Asha Rao" for a page of recipients, in one query per type. */
    private function recipientNames($recipients): array
    {
        $names = [];
        foreach (['lead' => Lead::class, 'contact' => Contact::class, 'company' => Company::class] as $type => $model) {
            $ids = $recipients->where('recipient_type', $type)->pluck('recipient_id')->unique()->values();
            if ($ids->isEmpty()) {
                continue;
            }
            foreach ($model::whereIn('id', $ids)->pluck('name', 'id') as $id => $name) {
                $names[$type.':'.$id] = $name;
            }
        }

        return $names;
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

    public function destroy($id)
    {
        $emailCampaign = EmailCampaign::findOrFail($id);

        if (in_array($emailCampaign->status, ['sending', 'sent'], true)) {
            return response()->json(['status' => false, 'message' => 'A sent campaign cannot be deleted.'], 422);
        }

        $emailCampaign->delete();

        return response()->json(['status' => true, 'message' => 'Campaign deleted']);
    }

    public function send($id, CampaignSender $sender)
    {
        $emailCampaign = EmailCampaign::findOrFail($id);

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
