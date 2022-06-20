<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BotUser extends Model
{
    use HasFactory;

    protected $table = 'users';

    protected $fillable = [
        'id',
        'first_name',
        'last_name',
        'username',
        'is_bot',
        'telegram_user_id',
        'service_number',
        'chat_id',
        'created_at',
        'updated_at',
    ];
}
