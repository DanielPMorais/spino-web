<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TestRun extends Model
{
    protected $fillable = [
        'uuid', 'team_id', 'test_device_id', 'status',
        'armed_at', 'started_at', 'finished_at', 'cancelled_at', 'created_by',
        'manual_rupture_load_grams', 'manual_rupture_notes',
        'manual_rupture_recorded_at', 'manual_rupture_recorded_by',
    ];

    protected function casts(): array
    {
        return [
            'armed_at' => 'datetime',
            'started_at' => 'datetime',
            'finished_at' => 'datetime',
            'cancelled_at' => 'datetime',
            'manual_rupture_recorded_at' => 'datetime',
        ];
    }

    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class);
    }

    public function device(): BelongsTo
    {
        return $this->belongsTo(TestDevice::class, 'test_device_id');
    }

    public function samples(): HasMany
    {
        return $this->hasMany(BridgeLoadSample::class);
    }
}
