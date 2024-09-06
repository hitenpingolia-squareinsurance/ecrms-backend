<?php

namespace App\Models\business_master;

use App\Models\Employee\Zone;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class State extends Model
{
    use HasFactory;

    protected $table = 'states';

    protected $fillable = [
        'id', 
        'zone_id',
        'name',
        'status',
        'created_at',
        'updated_at',
    ];

    public $timestamps = false;

    // Inverse relationship with Pincode
    public function pincodes()
    {
        return $this->hasMany(Pincode::class, 'state_id', 'id');
    }

    public function rtos()
    {
        return $this->hasMany(Rto::class, 'state_id', 'id');
    }

    public function zone()
    {
        return $this->belongsTo(Zone::class, 'zone_id', 'id');
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

