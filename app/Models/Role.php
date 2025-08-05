<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Role extends Model
{
    protected $fillable = ['role'];

    const ADMIN_ROLE_ID = 1;
    const OFFICE_OWNER_ROLE_ID = 2;
    const USER_ROLE_ID = 3;

    public function users()
    {
        return $this->hasMany(User::class);
    }
}
