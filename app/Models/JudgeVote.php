<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class JudgeVote extends Model
{
    protected $fillable = ['judge_id', 'first_team_id', 'second_team_id', 'third_team_id'];

    public function judge(): BelongsTo { return $this->belongsTo(Judge::class); }
    public function firstTeam(): BelongsTo { return $this->belongsTo(Team::class, 'first_team_id'); }
    public function secondTeam(): BelongsTo { return $this->belongsTo(Team::class, 'second_team_id'); }
    public function thirdTeam(): BelongsTo { return $this->belongsTo(Team::class, 'third_team_id'); }
}
