<?php

namespace App\Http\Middleware;

use Closure;
use App\models\FunctionalUsers;

class TpsdiAuth
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
        if ($request->header('Authorization')) {
        $key = explode(' ',$request->header('Authorization'));
        $user = FunctionalUsers::where('api_key', $key[1])->first();
        if(!empty($user)){
        $request->request->add(['userid' => $user->id]);
        return $next($request);
        }else{
                return response()->json([
                    'status'=>403,'message' => 'Your are not a authorised user.',
                ]);        
            }
        }
        return response()->json([
            'message' => 'Not a valid API request.',
        ]);
    }

}
