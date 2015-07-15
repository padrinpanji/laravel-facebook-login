<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

	class User extends Eloquent {
		public function profiles(){
			return $this->hasMany('Profile');
		}
	}