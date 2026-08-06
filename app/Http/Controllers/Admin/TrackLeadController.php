<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Models\Lead;
use App\Models\Leadfollowup;
use Carbon\Carbon;
use Illuminate\Http\Request;


class TrackLeadController extends Controller
{
    //
    function track_lead()
    {
        return view('admin.track-lead.track_lead');
    }
    function view_track_leads()
    {
        return view('admin.track-lead.view-track-lead');
    }
    public function get_track_leads()
    {
        $data = [];
        $sl = 1;

        $leads = Lead::where('assigned_to', '!=', 0)
            ->orderBy('id', 'desc')
            ->get();

        foreach ($leads as $lead) {

            // Points at the unified Lead Detail page rather than the old
            // follow-up-only view — kept below for direct-URL access only.
            $action = '
            <a href="' . route('leads.show', $lead->id) . '"
               class="action-btn view">
                <i class="fa fa-eye"></i> View
            </a>
        ';
            $data[] = [
                'sl'              => $sl++,
                'lead_id'         => $lead->id,
                'lead_number'     => $lead->lead_number,
                'name'            => $lead->name,
                'phone'           => $lead->phone,
                'lead_status'     => $lead->lead_status,
                'priority'        => $lead->priority,
                'follow_up_date'  => $lead->follow_up_date,
                'follow_up_time'  => $lead->follow_up_time,

                // 👇 View button with lead ID
                'action' =>   $action
            ];
        }

        return response()->json([
            'status' => true,
            'data'   => $data
        ]);
    }


    function get_followups_detials(Request $request)
    {
        $lead_id = $request->lead_id;

        $data = [];
        $sl = 1;
        $lead_follow_ups = Leadfollowup::select(
            'lead_follow_up.id',
            'lead_follow_up.lead_id',
            'lead_follow_up.user_id',
            'lead_follow_up.lead_response',
            'lead_follow_up.follow_up_date',
            'lead_follow_up.follow_up_time',
            'lead_follow_up.call_status',
            'lead_follow_up.call_note',
            'lead_follow_up.status',
            'lead_follow_up.created_at',
            'users.name as user_name'
        )
            ->leftJoin('users', 'users.id', '=', 'lead_follow_up.user_id')
            ->where('lead_follow_up.lead_id', $lead_id)
            ->orderBy('lead_follow_up.id', 'desc')
            ->get();

        foreach ($lead_follow_ups as $row) {

         

            $data[] = [
                'sl_no'          => $sl++,
                'lead_id'        => $row->lead_id,
                'user_name'      => $row->user_name ?? 'N/A',
                'call_status'    => $row->call_status,
                'lead_response'  => $row->lead_response,
                'follow_up_date' => $row->follow_up_date
                    ? Carbon::parse($row->follow_up_date)->format('d M Y')
                    : null,
                'follow_up_time' => $row->follow_up_time,
                'call_note'      => $row->call_note,
            ];
        }

        return response()->json([
            'status' => true,
            'data' => $data
        ]);
    }
}
