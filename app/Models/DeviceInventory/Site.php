<?php

namespace App\Models\DeviceInventory;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use MichaelAChrisco\ReadOnly\ReadOnlyTrait;
use Illuminate\Support\Facades\DB;

class Site extends Model
{
    use HasFactory, ReadOnlyTrait;

    protected $guarded = [];
    protected $connection = 'device_inventory';

//    public function site(Request $request)
//    {
//        return Organization::find($request->all()["id"])->hasMany(Site::class)->get();
//    }

    public function organization(){
        return $this->belongsToMany(Organization::class,'organization_site');
    }
}
