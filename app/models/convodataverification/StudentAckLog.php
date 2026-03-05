<?php 
namespace App\models\convodataverification;

use Illuminate\Database\Eloquent\Model;

use App\models\convodataverification\ConvoStudent;

class StudentAckLog extends Model
{
    // protected $table  = 'student_ack_log';

    protected $fillable = [
        'fn_en_status',
        'fn_en_remark',
        'fn_en_image',
        'fn_hi_status',
        'fn_hi_remark',
        'fn_hi_image',
        'cs_en_status',
        'cs_en_remark',
        'cs_en_image',
        'cs_hi_image',
        'cs_hi_status',
        'cs_hi_remark',
        'cgpa_status',
        'cgpa_remark',
        'cgpa_image',
        'student_photo_status',
        'student_photo',
        'student_id',
        'is_active',
        'mn_en_status',
        'mn_en_remark',
        'mn_en_image',
        'mn_hi_status', 
        'mn_hi_remark', 
        'mn_hi_image', 
        'ftn_en_status', 
        'ftn_en_remark', 
        'ftn_en_image', 
        'ftn_hi_status', 
        'ftn_hi_remark', 
        'ftn_hi_image',
        'se_image',
        'se_status',
        'se_remark'

    ];

    // public function convoStudent()
    // {
    //     return $this->belongsTo(ConvoStudent::class, 'student_id');
    // }
}
