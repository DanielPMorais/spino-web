<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CampusStudent extends Model
{
    protected $fillable = ['enrollment', 'name', 'course', 'is_active'];
}
