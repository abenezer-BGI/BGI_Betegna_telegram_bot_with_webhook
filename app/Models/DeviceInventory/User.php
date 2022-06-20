<?php

namespace App\Models\DeviceInventory;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Laratrust\Traits\LaratrustUserTrait;
use LdapRecord\Laravel\Auth\LdapAuthenticatable;
use MichaelAChrisco\ReadOnly\ReadOnlyTrait;

//use Illuminate\Database\Eloquent\Factories\HasFactory;
//use Illuminate\Database\Eloquent\Model;

class User extends Authenticatable
{
    use ReadOnlyTrait;

    protected $guarded = [];
    protected $connection = 'device_inventory';

    public static function get_user_permissions($user_id)
    {
        return DB::table('permission_user')->where("user_id", '=', $user_id)->join('permissions', 'permission_id', "=", "id")->get(['id', 'display_name']);
    }

    public static function get_role_permissions($user_id)
    {
        $role_id = self::get_role($user_id);
//        ddd(empty($role_id));
        if (!empty($role_id[0])) {
            return DB::table('permission_role')->where('role_id', '=', $role_id[0]->role_id)->join('permissions', 'permission_id', "=", "id")->get(['id', 'display_name']);
        } else {
            return;
        }
    }

    public static function get_role($user_id)
    {
        return DB::table('role_user')->where('user_id', '=', $user_id)->join('roles', 'role_id', '=', 'id')->get(['role_id', 'display_name']);
    }

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->string('password');
            $table->rememberToken();
            $table->timestamps();
        });
    }

    public function site()
    {
        return $this->belongsTo(Site::class);
    }
}

