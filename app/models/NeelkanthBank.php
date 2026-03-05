<?php

namespace App\models;

use Illuminate\Database\Eloquent\Model;

class NeelkanthBank extends Model
{
    protected $table = 'neelkanth_bank_requests';

    protected $fillable = [
    	'session_manager_id','template_id','excel_file','generation_type','ip_address','status','total_documents','generated_documents','regenerated_documents','printable_pdf_link','response','call_back_url','input_source','created_at','updated_at'
    ];
}
