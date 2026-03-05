<?php

namespace App\models\convodataverification;
 
use Illuminate\Database\Eloquent\Model;

class StudentTransaction extends Model
{ 

    protected $table = 'student_transactions';

    protected $fillable = [
        'student_id',
        'currency',
        'gateway_name',
        'response_message',
        'bank_name',
        'payment_mode',
        'mid',
        'response_code',
        'txn_id',
        'txn_amount',
        'order_id',
        'status',
        'bank_txn_id',
        'txn_date',
        'checksum_hash',
        'changed_collection_mode'
    ];

    protected $casts = [
        'txn_date' => 'datetime',
    ];

    public function student()
    {
        return $this->belongsTo(\App\Models\convodataverification\ConvoStudent::class, 'student_id')->select("id","prn","student_mobile_no","full_name","course_name",'collection_mode','status','is_printed');
    }
}
