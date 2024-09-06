<?php

namespace App\Models\Employee;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;

class Department extends Model
{
    use HasFactory;
    protected $fillable = [
        'name',
        'status',
        'sequence',
        'add_stamp',
        'update_stamp',
        'profiles',
        'core_lob',
    ];
    public $timestamps = false;
   

}
