<?php

namespace App\Services\Ai;

use App\Models\User;
use App\Services\Ai\Tools\AddLeadNoteTool;
use App\Services\Ai\Tools\CreateTaskTool;
use App\Services\Ai\Tools\CrmTool;
use App\Services\Ai\Tools\DealHealthTool;
use App\Services\Ai\Tools\DealPipelineTool;
use App\Services\Ai\Tools\GetLeadTool;
use App\Services\Ai\Tools\LeadStatsTool;
use App\Services\Ai\Tools\ListTasksTool;
use App\Services\Ai\Tools\SearchLeadsTool;
use App\Services\Ai\Tools\TeamPerformanceTool;
use App\Services\Ai\Tools\UpdateLeadStatusTool;

/**
 * Role-based capability list. The model is only ever shown (and can only
 * ever run) the tools its user is allowed to use, so an Agent's assistant
 * has no team_performance tool to call, however the question is phrased.
 */
class ToolRegistry
{
    private const TOOLS = [
        SearchLeadsTool::class,
        GetLeadTool::class,
        LeadStatsTool::class,
        DealPipelineTool::class,
        DealHealthTool::class,
        ListTasksTool::class,
        TeamPerformanceTool::class,
        CreateTaskTool::class,
        UpdateLeadStatusTool::class,
        AddLeadNoteTool::class,
    ];

    /** @return array<string, CrmTool> keyed by tool name */
    public function for(User $user): array
    {
        $allowed = [];
        foreach (self::TOOLS as $class) {
            $tool = new $class();
            if ($tool->allowedFor($user)) {
                $allowed[$tool->name()] = $tool;
            }
        }

        return $allowed;
    }
}
