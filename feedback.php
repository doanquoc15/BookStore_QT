<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class feedback extends Model
{
    public $timestamps = false; //set time to false
    protected $fillable = [
        'product_id', 'star_number','name','email','rating','created_at','created_status'
    ];
    protected $primaryKey = 'id_feedback';
    protected $table = 'feedback';
}
