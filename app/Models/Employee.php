<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class Employee extends Authenticatable
{
    use Notifiable;

    protected $fillable = ['name', 'username', 'password', 'position', 'status', 'device_id'];

    public function attendances()
    {
        return $this->hasMany(Attendance::class);
    }
}
