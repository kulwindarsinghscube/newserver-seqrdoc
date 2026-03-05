<?php

namespace App\models;
use Illuminate\Foundation\Auth\User as Authenticatable;
// use Illuminate\Database\Eloquent\Model;

class StudentsSitesMapping extends Authenticatable
{
    // protected $guard = 'gswallet';
    protected $connection = 'demo_connect';
    protected $table = 'student_site_mapping';

    protected $fillable = ['gs_id','ins_id','site_id'];
}
