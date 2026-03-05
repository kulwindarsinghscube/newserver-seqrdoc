<?php

namespace App\models;
use Illuminate\Foundation\Auth\User as Authenticatable;

class InstituteStudents extends Authenticatable
{
    protected $table = 'institute_students';

    protected $fillable = ['Student_Name','Father_Name','Mother_name','enrol_roll_number','institute_email','admission_year','graduation_year','adhar_no','abc_id','local_address','permanent_address','blood_group','dob','gender','photo','mobile_no','password'];
}
