<?php

namespace App\Models\business_master;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Area extends Model
{
    use HasFactory;

    protected $table = 'geo_areas';

    protected $fillable = [
        'area_id',
        'state_id',
        'district_id',
        'city_id',
        'area_name',
    ];

    public $timestamps = false;

    // Inverse relationship with Pincode
    public function pincodes()
    {
        return $this->hasMany(Pincode::class, 'area_id', 'area_id');
    }

    public function city()
    {
        return $this->belongsTo(City::class, 'city_id', 'id');
    }
}
