<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Member extends Model
{
    protected $fillable = ['team_id', 'enrollment', 'name', 'course', 'email', 'is_leader'];

    protected function casts(): array
    {
        return ['is_leader' => 'boolean'];
    }

    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class);
    }
}
