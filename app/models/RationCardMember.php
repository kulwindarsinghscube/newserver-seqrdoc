<?php 

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RationCardMember extends Model
{

    protected $table = 'ration_card_members';

    protected $fillable = [
        'id', 'ration_card_id', 'ration_card_number', 'name', 'uid', 'relation', 'dob'
    ];
}


?>