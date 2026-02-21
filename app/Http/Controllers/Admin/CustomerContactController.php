<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Models\CustomerContact;
use Illuminate\Http\Request;

class CustomerContactController extends Controller
{
    //
    function customer_contact_details()
    {
        return view('admin.customer_contact.customer_contact');
    }

    public function get_customer_contacts()
    {
        $contacts = CustomerContact::orderBy('id', 'desc')->get();

        $data = [];

        foreach ($contacts as $index => $contact) {
            $data[] = [
                'id'          => $contact->id,
                'name'        => $contact->name,
                'phone'       => $contact->phone,
                'email'       => $contact->email,
                'designation' => $contact->designation,
                'budget'      => number_format($contact->budget, 2),
                'city'        => $contact->city,
                'lead_id'     => $contact->lead_id,
            ];
        }

        return response()->json([
            'status' => true,
            'data'   => $data
        ]);
    }
}
