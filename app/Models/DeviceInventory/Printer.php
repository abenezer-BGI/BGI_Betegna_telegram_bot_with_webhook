<?php

namespace App\Models\DeviceInventory;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use MichaelAChrisco\ReadOnly\ReadOnlyTrait;

class Printer extends Model
{
    use HasFactory, ReadOnlyTrait;

    protected $guarded = [];
    protected $connection = 'device_inventory';
    protected $casts = [
        'issue_date' => 'date:Y-m-d',
    ];

    public function owner()
    {
        return $this->belongsTo(DeviceOwner::class,'device_owner_id','id');
    }

    public function site()
    {
        return $this->belongsTo(Site::class);
    }
}
