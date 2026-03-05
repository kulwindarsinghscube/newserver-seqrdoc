<?php

namespace App\models\convodataverification;
 
use Illuminate\Database\Eloquent\Model;

class StudentTransactionTemp extends Model
{ 

    protected $table = 'student_transaction_temp';
    public $timestamps = false;

    protected $fillable = [
        'student_id', 
        'order_id',
        'status', 
        'txn_date', 
        "txn_amount",
        "changed_collection_mode",
    ];

    protected $casts = [
        'txn_date' => 'datetime',
    ];

    public function student()
    {
        return $this->belongsTo(\App\Models\convodataverification\ConvoStudent::class, 'student_id')->select("id","prn","student_mobile_no","full_name","course_name",'collection_mode','status');
    }
}
