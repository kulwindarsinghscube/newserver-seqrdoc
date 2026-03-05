<?php

namespace App\models;

use Illuminate\Database\Eloquent\Model;

class CertificateDataMapping extends Model
{
    protected $table = 'certificate_data_mapping';
    public $timestamps = false;
    protected $fillable = [
       'Unique_no','Watermark_student_data','Watermark_TPSDI','student_name','Enrollment_NO','Program_Title','On_Date','Student_Image','Place','Mobile_Number','Email_ID','Card_Category','Hub_Name','student_tbl_id','Watermark_Insitute_Name','Institute_name','From_Date','To_Date','roll_no','Dealer_Name','Result','Cert_type'

    ];

    public function student()
    {
        return $this->belongsTo('App\Models\StudentTable', 'student_tbl_id');
    }

}

