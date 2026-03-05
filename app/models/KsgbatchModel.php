<?php

namespace App\models;

use Illuminate\Database\Eloquent\Model;

class KsgbatchModel extends Model
{
    protected $table = 'tbl_batch';

    protected $fillable = [
        'id','name', 'status','approver_id','created_by','created_at','files','branch_id','updated_at','publish',
    ];
}
