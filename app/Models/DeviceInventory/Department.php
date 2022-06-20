<?php

namespace App\Models\DeviceInventory;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use MichaelAChrisco\ReadOnly\ReadOnlyTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class Department extends Model
{
    use HasFactory, ReadOnlyTrait;

    protected $connection = 'device_inventory';

    protected $guarded = [];

    public function department(Request $request){
//        $id = $request->all()["id"];
//        $result = DB::select("SELECT * FROM departments,joined_department_site WHERE departments.id = joined_department_site.Department_ID AND joined_department_site.Site_ID = $id");
        return Site::find($request->all()["id"])->hasMany(Department::class)->get();
//        return Department::query()->where("");
    }

}
