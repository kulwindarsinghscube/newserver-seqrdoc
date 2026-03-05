<?php

namespace App\models;

use Illuminate\Database\Eloquent\Model;

class PaymentGatewayNew extends Model
{
    protected $table='payment_gateway_new';
    protected $primaryKey='id';
    protected $fillable=['pg_name','pg_title','merchant_key','salt','status','publish','test_merchant_key','test_salt','site_id','website','channel','industry_type','payment_mode','created_at','created_by','updated_at','updated_by'];
}
