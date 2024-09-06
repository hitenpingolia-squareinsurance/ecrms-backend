<?php

namespace App\Models\Employee;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RegionalOffice extends Model
{
    use HasFactory;
    protected $table = 'regional_office';
    protected $fillable = [
        "name","status", "add_stamp",'update_stamp',"zone_id"
    ];
    public $timestamps = false;
    public function zone()
    {
        return $this->belongsTo(Zone::class, 'zone_id');
    }
     public function branches()
    {
        return $this->hasMany(Branch::class, 'ro_id', 'id');
    } 
}