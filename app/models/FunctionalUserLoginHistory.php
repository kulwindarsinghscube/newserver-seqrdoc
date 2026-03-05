<?php

namespace App\models;

use Illuminate\Database\Eloquent\Model;
use App\Models\FunctionalUsers;

class FunctionalUserLoginHistory extends Model
{
    protected $table = 'functional_user_login_history';

    // public $timestamps = false;

    protected $fillable = ['functional_user_id', 'login_time', 'logout_time'];

    public function functionalUser()
    {
        return $this->belongsTo(FunctionalUsers::class, 'functional_user_id', 'id');
    }
}
