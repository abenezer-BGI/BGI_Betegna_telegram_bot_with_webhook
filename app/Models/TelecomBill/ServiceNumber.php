<?php

namespace App\Models\TelecomBill;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ServiceNumber extends Model
{
    protected $connection = 'telecom_bill';
    protected $fillable = [
        'number',
        'status',
        'service_owner_id',
        'service_type_id',
        'created_by',
        'updated_by',
    ];

    use HasFactory;

//    public function usage_type()
//    {
//        return $this->belongsToMany(UsageType::class);
//    }
//
//    public function service_type()
//    {
//        return $this->belongsTo(ServiceType::class);
//    }
//
//    public function service_owner()
//    {
//        return $this->belongsTo(ServiceOwner::class);
//    }
//
//    public function expense()
//    {
//        return $this->hasMany(Expense::class);
//    }
//
////    public function monthly_expense($month,$year){
////        return $this->service_type->usage_type->where("display_name","Discount")->
////            \App\Models\Expense::where("service_number_id",$service_number->id)->where("month",$month)->where("year",$year)->sum("expense")
////            - \App\Models\Expense::where("service_number_id",$service_number->id)->where("month",$month)->where("year",$year)->where("usage_type_id",\App\Models\ServiceNumber::find($service_number->id)->service_type->usage_type->where("display_name","Discount")->first()->id)->
////    }
}
