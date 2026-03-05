<?php

namespace App\models;
use Illuminate\Foundation\Auth\User as Authenticatable;
// use Illuminate\Database\Eloquent\Model;

class GlobalStudents extends Authenticatable
{
    // protected $guard = 'gswallet';
    protected $connection = 'demo_connect';
    protected $table = 'global_students';

    protected $fillable = ['Student_Name','Father_Name','Mother_name','personal_email','enrol_roll_number','admission_year','graduation_year','adhar_no','abc_id','local_address','permanent_address','blood_group','dob','gender','photo','mobile_no','password'];
}
