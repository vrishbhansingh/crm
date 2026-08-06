<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Lead;
use App\Models\LeadActivity;
use App\Models\LeadAttachment;
use App\Models\Leadfollowup;
use App\Models\Tag;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
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
        return view('admin.lead.view_lead', ['leadId' => $id]);
    }

    public function detail($id)
    {
        $lead = Lead::with(['customerContact', 'assignedUser', 'order', 'tags'])->findOrFail($id);

        return response()->json(['status' => true, 'data' => $lead]);
    }

    public function timeline($id)
    {
        Lead::findOrFail($id);

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

        $lead = Lead::findOrFail($id);

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

        $lead = Lead::findOrFail($id);

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
        $lead = Lead::findOrFail($id);
        $tag = Tag::findOrFail($tagId);

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
            'file' => 'required|file|max:10240',
        ]);

        $lead = Lead::findOrFail($id);
        $file = $request->file('file');

        $storedPath = $file->store('lead-attachments/'.$id, 'public');

        $attachment = LeadAttachment::create([
            'tenant_id' => $lead->tenant_id,
            'lead_id' => $id,
            'user_id' => Auth::guard('web')->id(),
            'original_name' => $file->getClientOriginalName(),
            'stored_path' => $storedPath,
            'size' => $file->getSize(),
            'mime_type' => $file->getClientMimeType(),
        ]);

        return response()->json(['status' => true, 'data' => $attachment]);
    }

    public function downloadAttachment($attachmentId)
    {
        $attachment = LeadAttachment::findOrFail($attachmentId);

        return Storage::disk('public')->download($attachment->stored_path, $attachment->original_name);
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
}
