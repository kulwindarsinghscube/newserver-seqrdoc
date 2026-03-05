<?php

namespace App\models\convodataverification;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\User as Authenticatable;

class ConvoAdmin extends Authenticatable
{
    protected $fillable = ['username', 'password', 'role'];
    protected $hidden = ['password'];
}
