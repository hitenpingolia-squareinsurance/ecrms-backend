<?php

namespace App\Models\Employee;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Branch extends Model
{
    use HasFactory;

    protected $fillable = [
        'name', 'status', 'address', 'zone_id', 'ro_id','address_link', 'add_stamp', 'update_stamp'
    ];

    public $timestamps = false;

    public function zone()
    {
        return $this->belongsTo(Zone::class, 'zone_id', 'id'); 
    }
    public function regionalOffice()
    {
        return $this->belongsTo(RegionalOffice::class, 'ro_id', 'id');
    }
  
}
