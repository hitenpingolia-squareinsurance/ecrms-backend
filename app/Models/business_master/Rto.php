<?php

namespace App\Models\business_master;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Rto extends Model
{
    use HasFactory;
    protected $table = 'rto';

    protected $fillable = [
        'name',
        'code',
        'state_id',
        'status',
        'created_at',
        'updated_at',
    ];
    public $timestamps = false;
    public function state()
    {
        return $this->belongsTo(State::class, 'state_id', 'id');
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
