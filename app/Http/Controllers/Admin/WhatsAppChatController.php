<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\WhatsappAccount;
use App\Models\WhatsappConversation;
use App\Services\WhatsApp\WhatsAppMessageService;
use App\Support\TenantContext;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * The chat box: one inbox per tenant, spanning every connected WhatsApp
 * account. Conversation/Message are tenant-DB models (BelongsToTenant), so
 * — unlike WhatsappAccount — these are looked up by plain id and
 * hand-scoped rather than route-model-bound, matching every other
 * tenant-scoped controller in the app.
 */
class WhatsAppChatController extends Controller
{
    public function index()
    {
        $tenantId = TenantContext::id();
        $accounts = WhatsappAccount::where('tenant_id', $tenantId)->where('is_active', true)->get();

        return view('whatsapp.chat', compact('accounts'));
    }

    public function conversations(Request $request)
    {
        $query = WhatsappConversation::query()->with('lead:id,name', 'contact:id,name')->orderByDesc('last_message_at');

        if ($request->filled('account_id')) {
            $query->where('whatsapp_account_id', (int) $request->account_id);
        }
        if ($request->filled('search')) {
            $term = $request->search;
            $query->where(fn ($q) => $q->where('wa_phone', 'like', "%{$term}%")->orWhere('wa_name', 'like', "%{$term}%"));
        }

        $conversations = $query->limit(100)->get()->map(fn (WhatsappConversation $c) => [
            'id' => $c->id,
            'wa_phone' => $c->wa_phone,
            'display_name' => $c->wa_name ?: $c->lead?->name ?: $c->contact?->name ?: $c->wa_phone,
            'lead_id' => $c->lead_id,
            'contact_id' => $c->contact_id,
            'last_message_at' => $c->last_message_at,
            'last_message_preview' => $c->last_message_preview,
            'unread_count' => $c->unread_count,
            'within_window' => $c->withinSessionWindow(),
            'account_id' => $c->whatsapp_account_id,
        ]);

        return response()->json(['status' => true, 'data' => $conversations]);
    }

    public function messages($id)
    {
        $conversation = WhatsappConversation::with('messages')->findOrFail($id);
        $conversation->update(['unread_count' => 0]);

        $account = WhatsappAccount::find($conversation->whatsapp_account_id);

        return response()->json([
            'status' => true,
            'data' => [
                'conversation' => [
                    'id' => $conversation->id,
                    'wa_phone' => $conversation->wa_phone,
                    'display_name' => $conversation->wa_name ?: $conversation->lead?->name ?: $conversation->contact?->name ?: $conversation->wa_phone,
                    'within_window' => $conversation->withinSessionWindow(),
                    'channel_type' => $account?->channel_type,
                ],
                'messages' => $conversation->messages->map(fn ($m) => [
                    'id' => $m->id,
                    'direction' => $m->direction,
                    'type' => $m->type,
                    'body' => $m->body,
                    'template_name' => $m->template_name,
                    'status' => $m->status,
                    'error_message' => $m->error_message,
                    'created_at' => $m->created_at,
                ]),
            ],
        ]);
    }

    public function send(Request $request, $id, WhatsAppMessageService $messages)
    {
        $data = $request->validate([
            'type' => 'required|in:text,template',
            'body' => 'required_if:type,text|nullable|string|max:4096',
            'template_name' => 'required_if:type,template|nullable|string|max:150',
            'template_params' => 'nullable|array',
        ]);

        $conversation = WhatsappConversation::findOrFail($id);
        $account = WhatsappAccount::find($conversation->whatsapp_account_id);
        abort_if(! $account || $account->tenant_id !== TenantContext::id(), 404);

        if ($data['type'] === 'text' && $account->channel_type === 'meta_cloud' && ! $conversation->withinSessionWindow()) {
            return response()->json([
                'status' => false,
                'message' => 'More than 24 hours since this contact last messaged you — Meta only allows an approved template message now, not free text.',
            ], 422);
        }

        $message = $messages->sendMessage($account, $conversation, $data, source: 'agent', sentBy: Auth::guard('web')->id());

        return response()->json([
            'status' => $message->status === 'sent',
            'message' => $message->status === 'sent' ? 'Message sent.' : ('Failed to send: '.$message->error_message),
            'data' => ['id' => $message->id, 'status' => $message->status],
        ]);
    }

    /**
     * Start a brand-new conversation from the chat box (rather than one
     * that already exists from an inbound message).
     */
    public function start(Request $request, WhatsAppMessageService $messages)
    {
        $tenantId = TenantContext::id();
        $data = $request->validate([
            'account_id' => 'required|integer',
            'phone' => 'required|string|max:30',
            'name' => 'nullable|string|max:150',
        ]);

        $account = WhatsappAccount::where('tenant_id', $tenantId)->findOrFail($data['account_id']);
        $conversation = $messages->findOrCreateConversation($account, $data['phone'], $data['name'] ?? null);

        return response()->json(['status' => true, 'data' => ['id' => $conversation->id]]);
    }
}
