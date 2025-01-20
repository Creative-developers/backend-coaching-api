<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CoachingSession extends Model
{
    protected $fillable = ['coach_id', 'client_id', 'user_id','session_date', 'completed_at', 'status'];

    public function coach()
    {
        return $this->belongsTo(User::class, 'coach_id');
    }

    public function client()
    {
        return $this->belongsTo(Client::class, 'client_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
