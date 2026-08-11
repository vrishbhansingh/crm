<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Deal;
use App\Models\DealStageHistory;
use App\Models\Lead;
use App\Models\LeadActivity;
use App\Models\LeadAttachment;
use App\Models\Leadfollowup;
use App\Models\Pipeline;
use App\Models\Tag;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

/**
 * Backs the new unified Lead Detail page (Phase 4) — everything the four
 * previously-fragmented lead pages (list expand-row, admin track-lead view,
 * user view_lead) showed separately, plus notes/tags/attachments/duplicate
 * check, which didn't exist anywhere before this.
 */
class LeadDetailController extends Controller
{
    public function show($id)
    {
        $this->findVisibleLead($id);

        return view('admin.lead.view_lead', ['leadId' => $id]);
    }

    public function detail($id)
    {
        $lead = $this->findVisibleLead($id);
        $lead->load(['customerContact', 'company', 'assignedUser', 'order', 'deal', 'tags']);

        return response()->json(['status' => true, 'data' => $lead]);
    }

    public function timeline($id)
    {
        $this->findVisibleLead($id);

        $activities = LeadActivity::with('user:id,name')
            ->where('lead_id', $id)
            ->get()
            ->map(fn ($a) => [
                'kind' => 'activity',
                'type' => $a->type,
                'description' => $a->description,
                'user_name' => $a->user->name ?? 'System',
                'created_at' => $a->created_at,
            ]);

        $followUps = Leadfollowup::where('lead_id', $id)
            ->get()
            ->map(fn ($f) => [
                'kind' => 'follow_up',
                'type' => 'follow_up',
                'description' => trim(($f->call_status ? ucfirst(str_replace('_', ' ', $f->call_status)).' — ' : '').($f->call_note ?? '')),
                'user_name' => null,
                'created_at' => $f->created_at,
            ]);

        $attachments = LeadAttachment::with('user:id,name')
            ->where('lead_id', $id)
            ->get()
            ->map(fn ($f) => [
                'kind' => 'attachment',
                'type' => 'attachment',
                'description' => $f->original_name,
                'user_name' => $f->user->name ?? 'System',
                'created_at' => $f->created_at,
                'attachment_id' => $f->id,
            ]);

        $timeline = $activities->concat($followUps)->concat($attachments)
            ->sortByDesc('created_at')
            ->values();

        return response()->json(['status' => true, 'data' => $timeline]);
    }

    public function addNote(Request $request, $id)
    {
        $request->validate(['body' => 'required|string|max:2000']);

        $lead = $this->findEditableLead($id);

        LeadActivity::create([
            'tenant_id' => $lead->tenant_id,
            'lead_id' => $id,
            'user_id' => Auth::guard('web')->id(),
            'type' => 'note',
            'description' => $request->body,
        ]);

        return response()->json(['status' => true, 'message' => 'Note added']);
    }

    public function addTag(Request $request, $id)
    {
        $request->validate(['name' => 'required|string|max:50']);

        $lead = $this->findEditableLead($id);

        // Use the lead's own tenant, not the acting user's — a platform
        // Super Admin (tenant_id null) has no tenant of their own, but the
        // tag being added always belongs to whichever tenant owns this lead.
        $tag = Tag::firstOrCreate(
            ['tenant_id' => $lead->tenant_id, 'name' => trim($request->name)],
            ['color' => $request->color]
        );

        if (! $lead->tags->contains($tag->id)) {
            $lead->tags()->attach($tag->id);

            LeadActivity::create([
                'tenant_id' => $lead->tenant_id,
                'lead_id' => $id,
                'user_id' => Auth::guard('web')->id(),
                'type' => 'tag_added',
                'description' => "Tag \"{$tag->name}\" added",
            ]);
        }

        return response()->json(['status' => true, 'data' => $tag]);
    }

    public function removeTag($id, $tagId)
    {
        $lead = $this->findEditableLead($id);
        $tag = Tag::where('tenant_id', $lead->tenant_id)->findOrFail($tagId);

        $lead->tags()->detach($tagId);

        LeadActivity::create([
            'tenant_id' => $lead->tenant_id,
            'lead_id' => $id,
            'user_id' => Auth::guard('web')->id(),
            'type' => 'tag_removed',
            'description' => "Tag \"{$tag->name}\" removed",
        ]);

        return response()->json(['status' => true]);
    }

    public function uploadAttachment(Request $request, $id)
    {
        $request->validate([
            'file' => 'required|file|max:10240|mimes:pdf,jpg,jpeg,png,webp,doc,docx,xls,xlsx,csv,txt',
        ]);

        $lead = $this->findEditableLead($id);
        $file = $request->file('file');

        $storedPath = $file->store('lead-attachments/'.$lead->tenant_id.'/'.$id, 'local');

        $attachment = LeadAttachment::create([
            'tenant_id' => $lead->tenant_id,
            'lead_id' => $id,
            'user_id' => Auth::guard('web')->id(),
            'original_name' => $file->getClientOriginalName(),
            'stored_path' => $storedPath,
            'size' => $file->getSize(),
            'mime_type' => $file->getMimeType(),
        ]);

        return response()->json(['status' => true, 'data' => $attachment]);
    }

    public function downloadAttachment($attachmentId)
    {
        $attachment = LeadAttachment::findOrFail($attachmentId);
        $this->findVisibleLead($attachment->lead_id);

        if (Storage::disk('local')->exists($attachment->stored_path)) {
            return Storage::disk('local')->download($attachment->stored_path, $attachment->original_name);
        }

        // Compatibility for attachments created before private storage was
        // introduced. New uploads are never written to the public disk.
        abort_unless(Storage::disk('public')->exists($attachment->stored_path), 404);

        return Storage::disk('public')->download($attachment->stored_path, $attachment->original_name);
    }

    /**
     * Log a follow-up call outcome — merged in from the old user-only
     * `User\LeadListController::user_lead_call_update` (unified interface).
     * Also mirrored into the LeadActivity timeline so it shows up alongside
     * notes/status changes instead of only in the separate follow-up table.
     */
    public function storeFollowUp(Request $request, $id)
    {
        $request->validate([
            'call_status' => 'nullable|string',
            'lead_response' => 'nullable|string',
            'followup_date' => 'nullable|date',
            'followup_time' => 'nullable',
            'call_notes' => 'nullable|string',
        ]);

        $lead = $this->findEditableLead($id);
        $userId = Auth::guard('web')->id();

        $followUp = Leadfollowup::create([
            'tenant_id' => $lead->tenant_id,
            'user_id' => $userId,
            'lead_id' => $id,
            'lead_response' => $request->lead_response,
            'follow_up_date' => $request->followup_date,
            'follow_up_time' => $request->followup_time,
            'call_status' => $request->call_status,
            'call_note' => $request->call_notes,
        ]);

        return response()->json(['status' => true, 'message' => 'Lead follow up saved', 'data' => $followUp]);
    }

    /**
     * Convert a lead into a Deal (Phase 5) — replaces the old direct
     * Lead -> Order conversion (`convertToOrder`). An Order is now only
     * created once the deal reaches a Won stage (see
     * Admin\DealController::moveStage/createOrderFromDeal). This is also
     * where the real is_converted/converted_at/conversion_value bug is
     * fixed: the old code created an Order but never set these fields on
     * the Lead, even though the view inferred "converted" purely from
     * order existing.
     */
    public function convertToDeal(Request $request, $id)
    {
        $request->validate([
            'pipeline_id' => 'nullable|integer',
            'amount' => 'nullable|numeric|min:0',
            'currency' => 'nullable|string|max:10',
        ]);

        $user = Auth::guard('web')->user();

        $deal = DB::transaction(function () use ($request, $id, $user) {
            $leadQuery = Lead::query();
            if (! $user->hasElevatedAccess()) {
                $leadQuery->where('assigned_to', $user->id);
            }

            $lead = $leadQuery->lockForUpdate()->findOrFail($id);
            $existingDeal = $lead->deal()->first();

            if ($existingDeal) {
                abort(409, 'This lead has already been converted to a deal.');
            }

            $pipeline = $request->filled('pipeline_id')
                ? Pipeline::withoutGlobalScope('tenant')
                    ->where('tenant_id', $lead->tenant_id)
                    ->findOrFail($request->pipeline_id)
                : Pipeline::ensureDefaultForTenant($lead->tenant_id);

            $firstOpenStage = $pipeline->stages()
                ->where('is_won', false)
                ->where('is_lost', false)
                ->orderBy('sort_order')
                ->firstOrFail();

            $amount = $lead->conversion_value ?? $request->amount ?? 0;

            $deal = Deal::create([
                'tenant_id' => $lead->tenant_id,
                'pipeline_id' => $pipeline->id,
                'stage_id' => $firstOpenStage->id,
                'lead_id' => $lead->id,
                'company_id' => $lead->company_id,
                'contact_id' => $lead->contact_id,
                'owner_id' => $lead->assigned_to ?? $user->id,
                'created_by' => $user->id,
                'name' => trim(($lead->company_name ?: $lead->name).' - Deal'),
                'amount' => $amount,
                'currency' => $request->currency,
            ]);

            DealStageHistory::create([
                'deal_id' => $deal->id,
                'from_stage_id' => null,
                'to_stage_id' => $firstOpenStage->id,
                'changed_by' => $user->id,
            ]);

            $lead->is_converted = 'Yes';
            $lead->converted_at = now();
            $lead->conversion_value = $amount;
            $lead->save();

            LeadActivity::create([
                'tenant_id' => $lead->tenant_id,
                'lead_id' => $id,
                'user_id' => $user->id,
                'type' => 'converted',
                'description' => "Converted to deal \"{$deal->name}\"",
            ]);

            return $deal;
        });

        return response()->json(['status' => true, 'message' => 'Lead converted to deal successfully', 'deal_id' => $deal->id]);
    }

    public function checkDuplicate(Request $request)
    {
        $request->validate([
            'phone' => 'nullable|string',
            'email' => 'nullable|email',
            'company_name' => 'nullable|string',
        ]);

        if (! $request->phone && ! $request->email && ! $request->company_name) {
            return response()->json(['status' => true, 'data' => []]);
        }

        $matches = Lead::query()
            ->where(function ($query) use ($request) {
                if ($request->phone) {
                    $query->orWhere('phone', $request->phone);
                }
                if ($request->email) {
                    $query->orWhere('email', $request->email);
                }
                if ($request->company_name) {
                    $query->orWhere('company_name', $request->company_name);
                }
            })
            ->when($request->exclude_id, fn ($q) => $q->where('id', '!=', $request->exclude_id))
            ->limit(5)
            ->get(['id', 'name', 'phone', 'email', 'company_name', 'lead_status']);

        return response()->json(['status' => true, 'data' => $matches]);
    }

    private function findVisibleLead(int $id): Lead
    {
        $user = Auth::guard('web')->user();
        $query = Lead::query();

        if (! $user->hasElevatedAccess() && ! $user->hasAnyRole(['Finance', 'Support'])) {
            $query->where('assigned_to', $user->id);
        }

        return $query->findOrFail($id);
    }

    private function findEditableLead(int $id): Lead
    {
        $user = Auth::guard('web')->user();
        $query = Lead::query();

        if (! $user->hasElevatedAccess()) {
            $query->where('assigned_to', $user->id);
        }

        return $query->findOrFail($id);
    }
}
