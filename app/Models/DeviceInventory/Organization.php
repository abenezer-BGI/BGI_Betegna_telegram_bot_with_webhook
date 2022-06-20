<?php

namespace App\Models\DeviceInventory;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use MichaelAChrisco\ReadOnly\ReadOnlyTrait;
use Illuminate\Http\Request;

class Organization extends Model
{
    use HasFactory, ReadOnlyTrait;

    protected $guarded = [];
    protected $connection = 'device_inventory';

    public function sites(){
        return $this->belongsToMany(Site::class);
    }
}
