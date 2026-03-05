<?php

namespace App\models;

use Illuminate\Database\Eloquent\Model;

class FunctionalUsers extends Model
{
    protected $table = 'functional_users';
    public $timestamps = false;
    protected $fillable = [
        'Mobile_Number','Email_ID','is_inter','student_tbl_id','OTP','PIN','Student_Name'
    ];
}
