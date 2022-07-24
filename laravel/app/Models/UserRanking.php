<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserRanking extends Model
{
    use HasFactory;

    // create更新項目
    protected $fillable = [
      'user_profile_id',
      'score'
    ];
}
