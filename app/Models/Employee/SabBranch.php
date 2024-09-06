<?php
namespace App\Models\Employee;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SabBranch extends Model
{
    use HasFactory;
    
    protected $table = 'sub_branches';
    
    protected $fillable = [
        'name',
        'branch_id',
        'current_tier',
        'zone_id',
        'ro_id',
        'status',
        'add_stamp',
        'update_stamp',
    ];
    
    public $timestamps = false;

    // Relationship with Zone
    public function zone()
    {
        return $this->belongsTo(Zone::class, 'zone_id');
    }

    // Relationship with Branch
    public function branch()
    {
        return $this->belongsTo(Branch::class, 'branch_id');
    }

    // Relationship with RegionalOffice
    public function regionalOffice()
    {
        return $this->belongsTo(RegionalOffice::class, 'ro_id');
    }
}
