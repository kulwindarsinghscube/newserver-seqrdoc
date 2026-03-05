<?php

namespace App\models;

use Illuminate\Database\Eloquent\Model;

class StudentTable extends Model
{
    protected $table = 'student_table';

    public $timestamps = true;


    protected $fillable = [
        'serial_no','student_name', 'certificate_filename','template_id', 'key','path','created_by','updated_by','status','publish','scan_count','site_id','template_type','certificate_type','bc_txn_hash','bc_ipfs_hash','pinata_ipfs_hash','bc_sc_id','bc_file_hash', 'global_SId','institute_SId','functional_user_id','tpsdidoc_type'
    ];
}
