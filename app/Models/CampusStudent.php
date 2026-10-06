<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CampusStudent extends Model
{
    protected $fillable = ['enrollment', 'name', 'course', 'ira', 'is_active'];

    protected function casts(): array
    {
        return ['ira' => 'decimal:2', 'is_active' => 'boolean'];
    }
}
