<?php

namespace App\Models\business_master;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pincode extends Model
{
    use HasFactory;

    protected $table = 'geo_pincode';

    protected $fillable = [
        'id',
        'state_id',
        'district_id',
        'city_id',
        'area_id',
        'pin_code',
        'status',
        'created_at',
        'updated_at',
    ];

    public $timestamps = false;

    // Relationship with State
    public function state()
    {
        return $this->belongsTo(State::class, 'state_id', 'id');
    }

    // Relationship with District


    public function district()
    {
        return $this->belongsTo(District::class, 'district_id', 'id');
    }

    // Relationship with City
    public function city()
    {
        return $this->belongsTo(City::class, 'city_id', 'id');
    }
    public function area()
    {
        return $this->belongsTo(Area::class, 'area_id', 'area_id');
    }

    protected function CreatedAt(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => date('d-m-Y h:i:s A',strtotime($value)),
        );
    }

    protected function UpdatedAt(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => date('d-m-Y h:i:s A',strtotime($value)),
        );
    }

}
