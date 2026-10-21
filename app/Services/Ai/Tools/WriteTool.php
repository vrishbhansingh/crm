<?php

namespace App\Services\Ai\Tools;

use App\Models\User;
use App\Services\Ai\PendingActionStore;

/**
 * A tool that changes data. Called by the model it only PROPOSES: the input
 * is validated and scoped to the user right away (so the user is never
 * offered something they couldn't do), parked in PendingActionStore, and
 * executed only by AssistantService::confirm() after the user clicks
 * Confirm. execute() runs proposal() again first, so nothing that changed
 * between propose and confirm can slip through.
 */
abstract class WriteTool extends CrmTool
{
    /**
     * Validate + normalise the input for this user.
     *
     * @return array{summary: string}|array{error: string}
     */
    abstract public function proposal(User $user, array $input): array;

    /**
     * Perform the change. Throw \RuntimeException with a user-friendly
     * message if it can no longer be done.
     *
     * @return string Confirmation message shown to the user.
     */
    abstract public function execute(User $user, array $input): string;

    final public function run(User $user, array $input): array
    {
        $proposal = $this->proposal($user, $input);
        if (isset($proposal['error'])) {
            return ['error' => $proposal['error']];
        }

        app(PendingActionStore::class)->put($user, $this->name(), $input, $proposal['summary']);

        return [
            'status' => 'awaiting_user_confirmation',
            'summary' => $proposal['summary'],
            'instruction' => 'NOT DONE YET. Tell the user you have prepared this and that they must press the Confirm button shown under your message. Do not say it has been done.',
        ];
    }

    /** Re-validates, then returns the normalised proposal or throws. */
    protected function revalidate(User $user, array $input): array
    {
        $proposal = $this->proposal($user, $input);
        if (isset($proposal['error'])) {
            throw new \RuntimeException($proposal['error']);
        }

        return $proposal;
    }
}
