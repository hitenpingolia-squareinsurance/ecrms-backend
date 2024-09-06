<?php

namespace App\Models\Employee;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Zone extends Model
{
    use HasFactory;
    protected $fillable = [
        "name",
        "status"
    ];
    public function regionalOffices()
    {
        return $this->hasMany(RegionalOffice::class, 'zone_id');
    }
    public function branches()
    {
        return $this->hasMany(Branch::class, 'Zone_Id', 'id');
    }
}
