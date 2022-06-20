<?php

namespace App\Models\TelecomBill;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Expense extends Model
{

    protected $guarded = [];

    protected $connection = 'telecom_bill';

    use HasFactory;

    public function service_number(){
       return $this->belongsTo(ServiceNumber::class);
    }
//
//    public function usage_type(){
//        return $this->belongsTo(UsageType::class);
//    }

}
