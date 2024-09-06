<?php

namespace App\Models\Employee;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EmployeeMaster extends Model
{
    use HasFactory;
    protected $table = 'employee_master';
    protected $fillable = [
        'master_type','parent_id','name','code','status','insert_date','update_stamp',
    ];
    public $timestamps = false;
}
