<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use MichaelAChrisco\ReadOnly\ReadOnlyTrait;

class ELeader extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'fidelity_id',
        'deleted_by',
        'phone_number',
        'client_name',
        'user_id',
        'bgi_id',
    ];
}
