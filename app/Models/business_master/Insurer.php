<?php

namespace App\Models\business_master;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Insurer extends Model
{
    use HasFactory;
    protected $table = 'insurers';

    protected $fillable = [
        'name',
        'mid_name',
        'short_name',
        'motor',
        'health',
        'non_motor',
        'life',
        'travel',
        'pa',
        'mail_id',
        'contact_no',
        'status',
        'add_stamp',
        'update_stamp',
    ];

    public $timestamps = false;
    public function cpas()
    {
        return $this->hasMany(Cpa::class, 'insurer_id', 'id');
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
