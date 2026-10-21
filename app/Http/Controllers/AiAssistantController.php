<?php

namespace App\Http\Controllers;

use App\Services\Ai\AssistantService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AiAssistantController extends Controller
{
    private const SESSION_KEY = 'ai_assistant_history';

    public function message(Request $request, AssistantService $assistant)
    {
        abort_unless($assistant->available(), 404);

        $data = $request->validate(['message' => ['required', 'string', 'max:'.config('ai.max_question_length')]]);
        $user = Auth::guard('web')->user();

        try {
            $result = $assistant->ask($user, trim($data['message']), (array) $request->session()->get(self::SESSION_KEY, []));
        } catch (\Throwable $exception) {
            report($exception);

            return response()->json(['status' => false, 'message' => \App\Services\Ai\AiErrors::message($exception)], 503);
        }

        $request->session()->put(self::SESSION_KEY, $result['history']);

        return response()->json(['status' => true, 'reply' => $result['reply'], 'pending' => $result['pending'] ?? []]);
    }

    public function confirm(Request $request, AssistantService $assistant)
    {
        abort_unless($assistant->available(), 404);

        $data = $request->validate(['id' => ['required', 'string', 'max:64']]);
        $result = $assistant->confirm(Auth::guard('web')->user(), $data['id']);

        // Let the model know what actually happened so later answers stay accurate.
        $this->remember($request, ($result['ok'] ? 'Done: ' : 'Not done: ').$result['message']);

        return response()->json(['status' => $result['ok'], 'message' => $result['message']]);
    }

    public function cancel(Request $request, AssistantService $assistant)
    {
        $data = $request->validate(['id' => ['required', 'string', 'max:64']]);
        $assistant->cancel(Auth::guard('web')->user(), $data['id']);
        $this->remember($request, 'The user cancelled that proposed action. Nothing was changed.');

        return response()->json(['status' => true]);
    }

    private function remember(Request $request, string $note): void
    {
        $history = (array) $request->session()->get(self::SESSION_KEY, []);
        $history[] = ['role' => 'assistant', 'content' => $note];
        $request->session()->put(self::SESSION_KEY, array_slice($history, -1 * (int) config('ai.history_messages')));
    }

    public function reset(Request $request)
    {
        $request->session()->forget([self::SESSION_KEY, 'ai_pending_actions']);

        return response()->json(['status' => true]);
    }
}
