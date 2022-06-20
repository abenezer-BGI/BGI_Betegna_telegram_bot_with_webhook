<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BotStatus extends Model
{
    use HasFactory;

    protected $table = 'bot_status';

    protected $fillable = [
        'id',
        'user_id',
        'last_question',
        'last_answer',
        'path',
        'back_path',
        'root_path',
        'created_at',
        'updated_at',
    ];
}
