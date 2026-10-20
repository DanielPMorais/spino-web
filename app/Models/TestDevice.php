<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TestDevice extends Model
{
    protected $fillable = ['code', 'name', 'last_seen_at'];

    protected function casts(): array
    {
        return ['last_seen_at' => 'datetime'];
    }

    public function testRuns(): HasMany
    {
        return $this->hasMany(TestRun::class);
    }
}
