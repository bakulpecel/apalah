<?php

namespace App\Http\Middleware;

use Auth;
use Closure;

class Article
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
        if (Auth::user()->role_id === 4) {
            return response()
                ->json('Unauthorize', 401);
        }

        return $next($request);
    }
}
