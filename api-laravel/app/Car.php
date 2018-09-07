<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Car extends Model
{
    //
    protected $table = 'cars';

    //Relation Many->1
    public function user() {
        return $this->belongsTo('App\User', 'user_id');
    }
}
