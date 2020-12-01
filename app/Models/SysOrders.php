<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class SysOrders extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'orders';

    protected $fillable = ['sys_customers_id','customer_id', 'fullname', 'total'];

    protected $dates = ['deleted_at'];

    public function customer()
    {
        return $this->belongsTo(SysCustomers::class, 'sys_customers_id');
    }

    public function item()
    {
        return $this->hasMany(SysOrderDetails::class);
    }
}
