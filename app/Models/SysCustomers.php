<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class SysCustomers extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'customers';

    protected $fillable = ['user_id','fullname', 'balance', 'latitude', 'langitude', 'status'];

    protected $dates = ['deleted_at'];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function order()
    {
        return $this->hasMany(SysOrders::class);
    }
}
