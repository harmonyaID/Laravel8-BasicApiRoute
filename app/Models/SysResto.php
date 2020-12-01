<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class SysResto extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'resto';

    protected $fillable = ['user_id','name', 'resto_balance', 'location', 'latitude', 'longitude', 'status'];

    protected $dates = ['deleted_at'];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function menu()
    {
        return $this->hasMany(SysRestoMenu::class);
    }

    public function opentime()
    {
        return $this->hasMany(SysRestoBusinessHours::class);
    }
}
