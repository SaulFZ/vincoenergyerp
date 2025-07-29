<?php
namespace App\Models\Auth;

use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use Notifiable, SoftDeletes;

    protected $fillable = [
        "name",
        "username",
        "email",
        "password",
        "status",
        "employee_id",
        "role_id",
    ];

    protected $hidden = ["password", "remember_token"];

    protected $casts = [
        "email_verified_at" => "datetime",
        "password"          => "hashed",
    ];

    public function employee()
    {
        return $this->belongsTo(
            \App\Models\Employee::class,
            "employee_id",
            "id"
        )->withTrashed();
    }

    // Agrega este método a la clase User
    public function directPermissions()
    {
        return $this->belongsToMany(Permission::class, 'user_direct_permissions')
            ->withTimestamps();
    }

    public function role()
    {
        return $this->belongsTo(Role::class);
    }

    public function permissions()
    {
        return $this->hasOne(\App\Models\Sistemas\UserPermission::class);
    }

    public function isActive()
    {
        return $this->status === "active";
    }

    public function scopeActive($query)
    {
        return $query->where("status", "active");
    }

    public function scopeInactive($query)
    {
        return $query->where("status", "inactive");
    }

}
