<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class SysRestoMenu extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'resto_menu';

    protected $fillable = ['sys_resto_id','name', 'menu_price', 'status'];

    protected $dates = ['deleted_at'];

    public function resto()
    {
        return $this->belongsTo(SysResto::class, 'sys_resto_id');
    }
}
