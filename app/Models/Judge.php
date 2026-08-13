<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Judge extends Model
{
    protected $fillable = ['name', 'email', 'credential', 'vote_status'];

    public function vote(): HasOne { return $this->hasOne(JudgeVote::class); }
}
