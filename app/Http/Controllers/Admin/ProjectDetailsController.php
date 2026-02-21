<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Models\ProjectInfo;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ProjectDetailsController extends Controller
{
    //
    function project_details()
    {
        return view('admin.Project-Details.project_details');
    }

    public function get_project_details()
    {
        $projects = ProjectInfo::orderBy('id', 'desc')->get();

        $data = [];
        $sl_no = 1;

        foreach ($projects as $project) {

            // Action buttons (CRM standard)
           $action = '
                    

                    <button type="button"
                        class="btn btn-sm btn-primary mb-1 editProjectBtn"
                        data-id="'.$project->id.'"
                        data-project_name="'.$project->project_name.'"
                        data-tech_stack="'.$project->tech_stack.'"
                        data-expected_start_date="'.$project->expected_start_date.'"
                        data-expected_delivery_date="'.$project->expected_delivery_date.'"
                        data-actual_delivery_date="'.$project->actual_delivery_date.'"
                        data-priority="'.$project->priority.'"
                        data-description="'.htmlspecialchars($project->description).'"
                        data-status="'.$project->status.'">
                       <i class="fa fa-pencil"></i>
                         <span>Edit</span>
                    </button>
                ';

            $data[] = [
                'sl_no' => $sl_no++,

                'project_id' => $project->id,
                'project_name' => $project->project_name ?? 'Project #' . $project->id,

                'tech_stack' => $project->tech_stack ?? '-',

                'expected_start_date' => !empty($project->expected_start_date)
                    ? date('d M Y', strtotime($project->expected_start_date))
                    : '-',

                'expected_delivery_date' => !empty($project->expected_delivery_date)
                    ? date('d M Y', strtotime($project->expected_delivery_date))
                    : '-',

                'actual_delivery_date' => !empty($project->actual_delivery_date)
                    ? date('d M Y', strtotime($project->actual_delivery_date))
                    : '-',

                'priority' => $project->priority ?? 'low',

                'description' => $project->description
                    ? Str::limit($project->description, 80)
                    : '-',

                'action' => $action
            ];
        }

        return response()->json([
            'status' => true,
            'data'   => $data
        ]);
    }

    function update_project_details(Request $request)
    {
        $project = ProjectInfo::findOrFail($request->id);
        $project->project_name = $request->project_name;
        $project->tech_stack = $request->tech_stack;
        $project->expected_start_date = $request->expected_start_date;
        $project->expected_delivery_date = $request->expected_delivery_date;
        $project->actual_delivery_date = $request->actual_delivery_date;
        $project->priority = $request->priority;
        $project->description = $request->description;
        $result = $project->update();

        if($result)
            {
                return response()->json([
                    'status' => true,
                    'message' => 'Project Details updated successfully'
                ]);
            }
        else{
              return response()->json([
                    'status' => false,
                    'message' => 'Something went wrong'
                ]);
        }
    }

    
}
