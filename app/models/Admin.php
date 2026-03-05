<?php

namespace App\models;

use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;
use App\models\Role;


class Admin extends Authenticatable
{
    use Notifiable;

    protected $guard = 'admin';
    protected $table = 'admin_table';

    protected $fillable = [
        'fullname','username', 'email','mobile_no', 'password','status','role_id','publish',
    ];

    protected $hidden = [
        'password', 'remember_token',
    ];

    public function getNameAttribute($value)
    {
        return $this->attribute = ucfirst($value);
    }
    public function user_permissions(){
        return $this->hasMany('App\Models\UserPermission','user_id','id');
    }

    public function role()
    {
        return $this->belongsTo(Role::class, 'id'); // Replace Role with your actual Role model
    }

    public function getRoleName($id = null)
    {
        $id = $id ?? $this->role_id; // Use the current user's role_id if no ID is provided
        $role = Role::find($id); // Replace Role with your actual Role model
        return $role ? $role->name : 'Unknown Role';
    }

    
}
