<?php

namespace App\Http\Middleware;
use Closure;
use Auth;
use App\Models\SessionManager;

class RedirectWebStudentNotLogin
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
     

        if(!Auth::guard('gswallet')->check() )
        {
          return redirect()->route('gswebapp.index');
        }

        $session_val = $request->session()->get('session_id');
        // dd($session_val);
        $session_manager = SessionManager::where('id',$session_val)->value('is_logged');
        // dd($session_manager);

        if($session_manager != 1){
            $request->session()->forget('session_id');

            Auth::guard('gswallet')->logout();
            return redirect()->route('gswebapp.index');            
        }

        $response = $next($request);

        return $response->header('Cache-Control','nocache, no-store, max-age=0, must-revalidate')
            ->header('Pragma','no-cache')
            ->header('Expires','Sun, 02 Jan 1990 00:00:00 GMT');
    }
}
