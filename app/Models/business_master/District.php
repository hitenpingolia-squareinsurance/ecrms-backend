<?php

namespace App\Models\business_master;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class District extends Model
{
    use HasFactory;

    protected $table = 'geo_districts';

    protected $fillable = [
        'id',
        'state_id',
        'district_name',
        'status',
        'created_at',
        'updated_at',
    ];

    public $timestamps = false;

    // Inverse relationship with Pincode
    public function pincodes()
    {
        return $this->hasMany(Pincode::class, 'district_id', 'id');
    }

}
