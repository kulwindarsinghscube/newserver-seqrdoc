<?php

namespace App\Http\Middleware\convodataverification;
use Illuminate\Support\Facades\Auth;
use Closure;

class ConvoStudnetMiddleware
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
        if (!Auth::guard('convo_student')->check()) {
            return redirect()->route('convo_student.login'); // Redirect to login route
        }

        return $next($request);
    }
}
