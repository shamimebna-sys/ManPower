<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class Employer
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        if($user = \Auth::user()){
//            $employer_id  = \App\Helpers\CommonClass::user()->employer_id;
			if( $user->role_id == 102 ){
                return redirect(route('web.down'));
            }
            if( $user->role_id == 105 ){
                return redirect('/');
            }
        }
        return $next($request);

    }
}
