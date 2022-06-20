<?php

namespace App\Models\DeviceInventory;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use MichaelAChrisco\ReadOnly\ReadOnlyTrait;

class Computer extends Model
{
    use HasFactory, ReadOnlyTrait;
    use ReadOnlyTrait;

    protected $connection = 'device_inventory';

    protected $guarded = [];

    protected $casts = [
        'issue_date'=>'date:d-m-Y',
    ];

//    protected $dateFormat = 'd-m-Y';

    public function site(){
        return $this->belongsTo(Site::class);
    }

    public function owner(){
        return $this->belongsTo(DeviceOwner::class,'device_owner_id','id');
    }

}
