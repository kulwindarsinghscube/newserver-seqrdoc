<?php 
namespace App\models\convodataverification;

use Illuminate\Database\Eloquent\Model;

use App\models\convodataverification\ConvoStudent;

class PasswordResetRequest  extends Model
{
    protected $fillable = [
        'student_id',
        'token',
        'request_ip',
        'requested_at',
        'is_successful'
    ];

    // Disable automatic timestamps if not needed
    public $timestamps = true;

    // Define relationships, if any
    public function student()
    {
        return $this->belongsTo(\App\Models\ConvoStudent::class, 'student_id');
    }
}
