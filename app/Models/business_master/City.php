<?php

namespace App\Models\business_master;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class City extends Model
{
    use HasFactory;

    protected $table = 'geo_cities';

    protected $fillable = [
        'id',
        'state_id',
        'district_id',
        'city_name',
        'status',
        'created_at',
        'updated_at',
    ];

    public $timestamps = false;

    // Inverse relationship with Pincode
    public function pincodes()
    {
        return $this->hasMany(Pincode::class, 'city_id', 'id');
    }
    public function areas()
    {
        return $this->hasMany(Area::class, 'city_id', 'id');
    }
}
