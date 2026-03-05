<?php 

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RationCardDetail extends Model
{

    
    protected $table = 'ration_card_details';
    
    protected $fillable = [
        'id', 'ration_card_no', 'head_of_family', 'husband_or_father_name', 
        'ration_shop_no', 'district', 'constituency', 'mandal', 'village_or_ward', 
        'residential_address', 'created_at', 'student_table_id'
    ];

    public function familyDetails()
    {
        return $this->hasMany(RationCardMember::class, 'ration_card_id', 'id');
    }
}


?>
