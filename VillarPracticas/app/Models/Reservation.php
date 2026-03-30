<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Reservation extends Model
{
    use HasFactory;

    protected $fillable = ['user_id', 'date', 'time_slot_id'];

    public function usuari() {
         return $this->belongsTo(usuaris::class, 'user_id');
    }
}
