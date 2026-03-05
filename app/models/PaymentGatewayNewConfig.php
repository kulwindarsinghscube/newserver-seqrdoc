<?php

namespace App\models;

use Illuminate\Database\Eloquent\Model;

class PaymentGatewayNewConfig extends Model
{
    protected $table='payment_gateway_new_config';
    protected $primaryKey="id";

    protected $fillable=['pg_id','pg_status','amount','crendential','updated_by'];
}
