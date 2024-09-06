<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    protected $table = 'product_master';


    protected function Product(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => $this->productFullName($value),
        );
    }

    protected function Name(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => $this->productFullName($value),
        );
    }

    protected function PolicyType(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => $this->policyTypeFullName($value),
        );
    }

    function productFullName($name){
        switch ($name) {
            case "TW":
                return "Two Wheeler";
              break;
            case "PC":
                return "Private Car";
              break;
            case "PCV":
                return "Passenger Carrying Vehicle";
              break;
            case "GCV":
                return "Goods Carrying Vehicles";
              break;   
            case "Misc D":
                return "Miscellaneous Vehicle";
              break;
            case "MEWP":
                return "Motor Extended Warranty Policy";
              break;  
            case "CVEWP":
                return "Commercial Vehicle Extended Warranty Policy";
              break; 
              case "MTRR":
                return "Motor Trade Road Risks";
              break;          
            default:
                return $name;
        }
    }

    function policyTypeFullName($name){
        switch ($name) {
            case "CP":
                return "Comprehensive";
              break;
            case "OD":
                return "Standalone Own Damage";
              break;
            case "TP":
                return "Third Party";
              break;          
            default:
                return $name;
        }
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
