<?php

namespace App\models;

use Illuminate\Database\Eloquent\Model;

class KsgBtatchRecordModel extends Model
{
    protected $table = 'tbl_batch_records';

    protected $fillable = [
        'id','batch_id','usn','name', 'course','course_date','fees','unique_id_no','id_type','approval_id','course_month','status','created_by','created_at','updated_at','publish','credit','course_2',
    ];
}

