<?php

namespace App\Models\DeviceInventory;

use Illuminate\Database\Eloquent\Model;
use MichaelAChrisco\ReadOnly\ReadOnlyTrait;

/**
 * @property integer $id
 * @property string $email
 * @property string $password
 * @property int $cost_center_id
 * @property int $department_id
 * @property int $site_id
 * @property int $organization_id
 * @property string $remember_token
 * @property integer $created_by
 * @property integer $updated_by
 * @property string $created_at
 * @property string $updated_at
 */
class DeviceOwner extends Model
{
//    /**
//     * The "type" of the auto-incrementing ID.
//     *
//     * @var string
//     */
//    protected $keyType = 'integer';
//    protected $primaryKey = 'id';

//    protected $primaryKey = 'logon_name';

    protected $connection = 'device_inventory';

    /**
     * @var array
     */
    protected $guarded = [];

    public function fetch_devices()
    {
        return $this->hasMany(Computer::class, "device_owner_id")->select("id", "device_type", "model", "brand", "serial_number")->union($this->hasMany(Monitor::class, "device_owner_id")->select("id", "device_type", "model", "brand", "serial_number"));
    }

    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    public function site()
    {
        return $this->belongsTo(Site::class);
    }

    public function organization()
    {
        return $this->belongsTo(Organization::class);
    }

}
