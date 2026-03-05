<?php

namespace App\models\convodataverification;

use Illuminate\Database\Eloquent\Model;

use Illuminate\Foundation\Auth\User as Authenticatable;

use App\Models\convodataverification\StudentAckLog; // Ensure the namespace and class name are correct


// use App\models\convodataverification\;
 
class ConvoStudent extends Authenticatable
{
    // 

    protected $fillable = [
        'prn',
        'date_of_birth',
        'wpu_email_id',
        'full_name',
        'full_name_hindi',
        'full_name_krutidev',
        'course_name',
        'course_name_hindi',
        'course_name_krutidev',
        'cgpa',
        'cgpa_hindi',
        'cgpa_krutidev',
        'password',
        'student_photo',
        'status',
        'mother_name',
        'mother_name_hindi',
        'mother_name_krutidev',
        'father_name',
        'father_name_hindi',
        'father_name_krutidev',
        'gender',
        'secondary_email_id',
        'first_name',
        'middle_name',
        'last_name',
        'student_mobile_no',
        'permanent_address',
        'local_address',
        'certificateid',
        'cohort_id',
        'cohort_name',
        'faculty_name',
        'specialization',
        'rank',
        'medal_type',
        'completion_date',
        'issue_date', 
        'is_pwd_reset',
        'faculty_name_krutidev',
        'specialization_krutidev',
         'correction_message',
        'no_of_people_accompanied',
    ];
    
    protected $hidden = ['password'];

    public function studentAckLogs()
    {
        return $this->hasOne(StudentAckLog::class, 'student_id')->where('is_active',1);
    }
}
