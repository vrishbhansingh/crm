<?php

namespace App\Http\Controllers;

use App\Models\MasterValue;
use Illuminate\Http\Request;

/**
 * Read-only dropdown data source (Phase 2). Any authenticated user can read
 * these — filling out a lead/order form needs the option list regardless of
 * whether that role can manage master data itself; only the admin
 * Master Management screen (Admin\MasterDataController) is permission-gated.
 */
class MasterValueLookupController extends Controller
{
    public function options(string $typeCode)
    {
        return response()->json([
            'data' => MasterValue::options($typeCode),
        ]);
    }
}
