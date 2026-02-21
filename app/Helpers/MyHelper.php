<?php

namespace App\Helpers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use App\Models\Admin;
use App\Models\Payouts;
use App\Models\Ledgers;
use App\Models\Members;
use Request;


class MyHelper{

public static function get_total_pf($username=null){
$get=Payouts::select('pf')->where('username', $username)->sum(DB::raw('pf'));

return round($get, 2);
}


public static function get_total_incentive($username=null){
    $get=Ledgers::select('username','credit','debit')->where('username', $username)->sum(DB::raw('credit-debit'));
    
    return round($get, 2);
    }


}