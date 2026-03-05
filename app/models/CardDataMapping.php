<?php

namespace App\models;

use Illuminate\Database\Eloquent\Model;

class CardDataMapping extends Model
{
    protected $table = 'card_data_mapping';
    public $timestamps = false;
    protected $fillable = [
        'unique_number','Candidate_name','enrollment_no','blood_group','course','framework_at','can_work_as','to_work_under','valid_upto','batch_no','colour_code','division_name','Mobile_Number','Email_ID','Card_Category','Hub_Name','student_tbl_id','on_date','colour_code','division_name'

    ];
    public function student()
    {
        return $this->belongsTo('App\Models\StudentTable', 'student_tbl_id');
    }

}
