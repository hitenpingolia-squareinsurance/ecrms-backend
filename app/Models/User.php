<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    protected function Name(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => ucwords(strtolower($value)),
        );
    }

    protected function Email(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => strtolower($value),
        );
    }

    protected function Dob(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => date('d-m-Y',strtotime($value)),
        );
    }

    protected function Doj(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => date('d-m-Y',strtotime($value)),
        );
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

    public function details(){
        return $this->hasOne(User_detail::class);
    }

}
