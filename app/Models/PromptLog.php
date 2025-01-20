<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PromptLog extends Model
{
    protected $table = 'prompt_log';
    protected $fillable = ['prompt' ,'response','relevance','clarity','tone','average_score'];

}
