<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class showroom_favorite extends Model
{
    use HasFactory;

    protected $fillable = ['user_id', 'showroom_id'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function showroom()
    {
        return $this->belongsTo(Showroom::class);
    }
}
