<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Mail;
use Session;

class VerifyController extends Controller
{

    public function bverify() {
        return view('bverify');
    }



    public function bverify_new() {
        return view('bverify_new');
    }



    
}
