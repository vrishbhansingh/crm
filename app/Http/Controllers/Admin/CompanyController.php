<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Models\CompanyDetails;
use Illuminate\Http\Request;
use LDAP\Result;
use Illuminate\Support\Facades\File;

class CompanyController extends Controller
{
    //
    public function company_details()
    {
        $company = CompanyDetails::first();
        return view('admin.company_details.company_details', compact('company'));
    }

    public function edit_company_details_page()
    {
        $company = CompanyDetails::first();
        return view('admin.company_details.edit_company_details', compact('company'));
    }
    public function edit_com_details(Request $request)
    {
        $company = CompanyDetails::where('status', 'Active')->firstOrFail();

        // 🔹 Update normal fields
        $company->company_name  = $request->company_name;
        $company->email         = $request->email;
        $company->phone         = $request->phone;
        $company->address       = $request->address;
        $company->city          = $request->city;
        $company->state         = $request->state;
        $company->gst_number    = $request->gst_number;
        $company->pincode       = $request->pincode;
        $company->pan_number    = $request->pan_number;
        $company->bank_name     = $request->bank_name;
        $company->account_name  = $request->account_name;
        $company->ifsc_code     = $request->ifsc_code;
        $company->status        = $request->status;

        // 🔹 Handle logo upload (OPTIONAL)
        if ($request->hasFile('company_logo')) {

            $file = $request->file('company_logo');

            if (!$file->isValid()) {
                return response()->json([
                    'status' => false,
                    'message' => 'Invalid image file'
                ]);
            }

            $uploadPath = public_path('uploads/company');

            if (!File::exists($uploadPath)) {
                File::makeDirectory($uploadPath, 0755, true);
            }

            // Delete old logo
            if (!empty($company->company_logo) && File::exists(public_path($company->company_logo))) {
                File::delete(public_path($company->company_logo));
            }

            $fileName = 'company_logo_' . time() . '.' . $file->getClientOriginalExtension();
            $file->move($uploadPath, $fileName);

            $company->company_logo = 'uploads/company/' . $fileName;
        }

        // 🔹 FINAL SAVE (ALWAYS)
        if ($company->save()) {
            return response()->json([
                'status' => true,
                'message' => 'Company details updated successfully'
            ]);
        }

        return response()->json([
            'status' => false,
            'message' => 'Something went wrong'
        ]);
    }
}
