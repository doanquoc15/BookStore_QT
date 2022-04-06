<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class social extends Model
{
    public $timestamps = false;
    protected $fillable = [
        'provider_user_id',  'provider',  'user'
    ];

    protected $primaryKey = 'user_id';
    protected $table = 'social';
    public function login(){
        return $this->belongsTo('App\loginCustomer', 'user');
    }
}
