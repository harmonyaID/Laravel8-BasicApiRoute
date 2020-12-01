<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SysToken extends Model
{
    use HasFactory;

    protected $table = 'token';

    protected $fillable = ['user_id', 'user_type', 'token'];
}
