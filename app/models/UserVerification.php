<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserVerification extends Model
{
    protected $table = 'user_verifications';

    protected $fillable = [
        'fullname',
        'username',
        'organization_name',
        'password',
        'email_id',
        'mobile_no',
        'verify_by',
        'device_type',
        'token',
        'OTP',
        'site_id',
    ];

    public $timestamps = true;
}
