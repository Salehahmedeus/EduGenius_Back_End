<?php

namespace App\Modules\Authentication\Models;

use Illuminate\Database\Eloquent\Model;

class User extends Model
{
    protected $fillable = ['email', 'password_hash'];
}
