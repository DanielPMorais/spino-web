<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CompetitionSetting extends Model
{
    protected $fillable = ['voting_open'];

    protected function casts(): array { return ['voting_open' => 'boolean']; }
}
