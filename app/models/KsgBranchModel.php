<?php

namespace App\models;

use Illuminate\Database\Eloquent\Model;

class KsgBranchModel extends Model
{
    protected $table = 'tbl_branch';

    protected $fillable = [
        'id','name', 'created_by','created_at','updated_at','publish',
    ];
}
