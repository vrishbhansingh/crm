<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;


class Vendor
{
    
    public function handle(Request $request, Closure $next)
    {
        
            if (Auth::guard('vendor')->check()) {
                if (Auth::guard('vendor')->user()) {
                return $next($request);
                }
            }
        
        return redirect('/vendor');
    }
}
