<?php

namespace App\Models;
use App\Models\StudentTable;
use Illuminate\Database\Eloquent\Model;

class Site extends Model
{
    public $table="sites";
    public $primaryKey="site_id";
    public $fillable=['site_url','status'];

    public function students() {
        return $this->hasMany(StudentTable::class, 'site_id', 'site_id'); // Adjust as necessary
    }
}
