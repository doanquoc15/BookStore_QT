<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class orders extends Model
{
    public $timestamps = false; //set time to false
    protected $fillable = [
        'order_total', 'created_at','created_status'
    ];
    protected $primaryKey = 'order_id';
    protected $table = 'orders';
}
