<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Member extends Model
{
    protected $fillable = ['team_id', 'enrollment', 'name', 'course', 'email', 'is_leader', 'email_verified_at', 'email_verification_code', 'email_verification_expires_at'];

    protected function casts(): array
    {
        return [
            'is_leader' => 'boolean',
            'email_verified_at' => 'datetime',
            'email_verification_expires_at' => 'datetime',
        ];
    }

    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class);
    }
}
