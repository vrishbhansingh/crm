<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Deal;
use App\Models\Lead;
use App\Models\Task;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * Aggregates the three date-bearing things this CRM already tracks — Task
 * due dates, Lead follow-ups, and Deal expected-close dates — into one
 * calendar feed. No new storage: this is a read-only view over data that
 * already exists in each of those tables.
 */
class CalendarController extends Controller
{
    public function index()
    {
        return view('calendar.index');
    }

    public function events(Request $request)
    {
        $request->validate([
            'start' => ['nullable', 'date'],
            'end' => ['nullable', 'date'],
        ]);

        $user = Auth::guard('web')->user();
        $elevated = $user->hasElevatedAccess();
        $start = $request->date('start') ?? now()->subMonth();
        $end = $request->date('end') ?? now()->addMonths(2);

        $events = collect();

        Task::whereNotNull('due_at')
            ->whereBetween('due_at', [$start, $end])
            ->whereNotIn('status', ['completed', 'cancelled'])
            ->when(! $elevated, fn ($q) => $q->where(fn ($qq) => $qq->where('assigned_to', $user->id)->orWhere('created_by', $user->id)))
            ->get(['id', 'title', 'due_at', 'priority', 'related_type', 'related_id'])
            ->each(function (Task $task) use ($events) {
                $events->push([
                    'id' => 'task-'.$task->id,
                    'title' => $task->title,
                    'start' => $task->due_at->toIso8601String(),
                    'color' => match ($task->priority) {
                        'urgent' => '#dc2626',
                        'high' => '#ea580c',
                        'medium' => '#2563eb',
                        default => '#64748b',
                    },
                    'url' => $task->related_type === 'deal'
                        ? route('deals.show', $task->related_id)
                        : ($task->related_type === 'lead' ? route('leads.show', $task->related_id) : route('tasks.index')),
                    'extendedProps' => ['type' => 'Task reminder'],
                ]);
            });

        Lead::whereNotNull('follow_up_date')
            ->whereBetween('follow_up_date', [$start, $end])
            ->where('lead_status', '!=', 'closed')
            ->when(! $elevated, fn ($q) => $q->where('assigned_to', $user->id))
            ->get(['id', 'name', 'follow_up_date', 'follow_up_time'])
            ->each(function (Lead $lead) use ($events) {
                // follow_up_date/follow_up_time aren't cast to Carbon on the
                // Lead model (read back as plain strings) — parsed locally
                // here rather than widening that cast app-wide.
                $startAt = Carbon::parse($lead->follow_up_date.($lead->follow_up_time ? ' '.$lead->follow_up_time : ''))
                    ->toIso8601String();

                $events->push([
                    'id' => 'lead-'.$lead->id,
                    'title' => 'Follow up: '.$lead->name,
                    'start' => $startAt,
                    'color' => '#7c3aed',
                    'url' => route('leads.show', $lead->id),
                    'extendedProps' => ['type' => 'Lead follow-up'],
                ]);
            });

        Deal::whereNotNull('expected_close_date')
            ->whereBetween('expected_close_date', [$start, $end])
            ->where('status', 'open')
            ->when(! $elevated, fn ($q) => $q->where('owner_id', $user->id))
            ->get(['id', 'name', 'amount', 'currency', 'expected_close_date'])
            ->each(function (Deal $deal) use ($events) {
                // expected_close_date isn't cast to Carbon on the Deal
                // model (read back as a plain string) — parsed locally
                // here rather than widening that cast app-wide.
                $events->push([
                    'id' => 'deal-'.$deal->id,
                    'title' => 'Close: '.$deal->name,
                    'start' => Carbon::parse($deal->expected_close_date)->toIso8601String(),
                    'allDay' => true,
                    'color' => '#16a34a',
                    'url' => route('deals.show', $deal->id),
                    'extendedProps' => ['type' => 'Expected close'],
                ]);
            });

        return response()->json($events->values());
    }
}
