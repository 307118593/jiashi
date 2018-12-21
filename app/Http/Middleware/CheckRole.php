<?php

namespace App\Http\Middleware;

use Closure;

class CheckRole
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        if ($request->cid) {
            $cid = getCid($request->cid);
            $request->merge(['cid'=>$cid]);
        }
        return $next($request);
    }
}
