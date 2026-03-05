<?php

namespace App\models;

use Illuminate\Database\Eloquent\Model;

class StudentRecords extends Model
{
    protected $table = 'student_record';

    protected $fillable = [
        'id','student_table_id', 'certificate_no','issue_date','candidate_name','grade','created_at','updated_at'
    ];
}
