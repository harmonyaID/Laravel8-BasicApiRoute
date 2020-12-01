<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SysRestoBusinessHours extends Model
{
    use HasFactory;

    protected $table = 'resto_business_hours';

    protected $fillable = ['sys_resto_id','day_open', 'start_time', 'end_time', 'status'];

    public function resto()
    {
        return $this->belongsTo(SysResto::class, 'sys_resto_id');
    }
}
