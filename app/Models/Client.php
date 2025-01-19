<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Client extends Model
{
    protected $fillable = ['coach_id','user_id','phone_number','date_of_birth', 'notes'];

    public function coach()
    {
        return $this->belongsTo(User::class, 'coach_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
