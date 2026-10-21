<?php

namespace App\Http\Controllers;

use App\Models\Lead;
use App\Models\WhatsappConversation;
use App\Services\Ai\AiQuota;
use App\Services\Ai\TextAssistant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * One-click AI helpers (lead summary, draft reply, WhatsApp reply
 * suggestion). Every route already carries the matching permission
 * middleware; on top of that a lead is only reachable here if the user
 * could open it themselves — admins/managers any lead in their company,
 * everyone else only leads assigned to them.
 */
class AiHelperController extends Controller
{
    public function summary($id, TextAssistant $ai)
    {
        return $this->respond($ai, fn ($user) => $ai->summarize($user, $this->lead($id)));
    }

    public function draft(Request $request, $id, TextAssistant $ai)
    {
        $data = $request->validate([
            'channel' => ['required', 'in:email,whatsapp'],
            'goal' => ['nullable', 'string', 'max:300'],
        ]);

        return $this->respond($ai, fn ($user) => $ai->draft($user, $this->lead($id), $data['channel'], trim((string) ($data['goal'] ?? ''))));
    }

    public function suggestWhatsapp($id, TextAssistant $ai)
    {
        // Same visibility the inbox itself gives (anyone with whatsapp.send can open a conversation).
        return $this->respond($ai, fn ($user) => $ai->suggestWhatsappReply($user, WhatsappConversation::findOrFail($id)));
    }

    private function lead($id): Lead
    {
        $user = Auth::guard('web')->user();
        $query = $user->hasElevatedAccess() ? Lead::query() : Lead::where('assigned_to', $user->id);

        return $query->findOrFail($id);
    }

    private function respond(TextAssistant $ai, callable $work)
    {
        abort_unless($ai->available(), 404);
        $user = Auth::guard('web')->user();

        if (! AiQuota::take($user)) {
            return response()->json(['status' => false, 'message' => 'You have reached today\'s AI limit. It resets tomorrow.'], 429);
        }

        try {
            return response()->json(['status' => true, 'text' => $work($user)]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $exception) {
            throw $exception; // becomes a normal 404
        } catch (\Throwable $exception) {
            report($exception);

            return response()->json(['status' => false, 'message' => \App\Services\Ai\AiErrors::message($exception)], 503);
        }
    }
}
