<?php

namespace App\models;

use Illuminate\Database\Eloquent\Model;

class License extends Model
{
    protected $table = 'tbl_license';

    protected $fillable = ['business_name','pdf_file', 'json_data','request_mode'];
}
