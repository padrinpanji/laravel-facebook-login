<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class Profile extends Eloquent {

    public function user()
    {
        return $this->belongsTo('User');
    }
}