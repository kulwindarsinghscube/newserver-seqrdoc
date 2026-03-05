<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Requests\WebUserRegisterRequest;
use App\models\Site;
use Hash;
use App\Jobs\SendMailJob;
use App\models\User;
use App\models\SessionManager;
use Illuminate\Support\Facades\Auth;
use App\Utility\ApiSecurityLayer;
use App\models\ApiTracker;
class GetProfileController extends Controller
{
  public function GetProfileData(){
     if (ApiSecurityLayer::checkAuthorization()) 
    {  

        dd(yes);

    }
    else
    {
        $message = array('success' => false,'status'=>403,'message' => 'Access forbidden.');
    } 
  }

}
