<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SysOrderDetails extends Model
{
    use HasFactory;

    protected $table = 'orders_details';

    protected $fillable = ['sys_orders_id', 'resto_name', 'menu_name','price'];

    protected $hidden = [
        'created_at',
        'updated_at',
    ];

    public function order()
    {
        return $this->belongsTo(SysOrders::class, 'sys_orders_id');
    }
}
