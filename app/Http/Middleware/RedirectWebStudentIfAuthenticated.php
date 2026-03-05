<?php

namespace App\Http\Middleware;
use Closure;
use Auth;
use App\Models\SessionManager;
class RedirectWebStudentIfAuthenticated
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
        if(Auth::guard('gswallet')->check())
        {
          return redirect('student-wallet/gsdocuments');
        }
        
     return $next($request);
    }
}
