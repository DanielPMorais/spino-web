<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Team extends Model
{
    protected $fillable = ['name', 'code', 'category', 'quota_type', 'status', 'audit_status', 'measured_weight_grams', 'declared_load_grams', 'materials_compliant', 'dimensions_compliant', 'no_coating', 'audit_notes', 'audited_at', 'actual_load_grams', 'efficiency_score', 'precision_score', 'test_status', 'tested_at'];

    protected function casts(): array
    {
        return ['materials_compliant' => 'boolean', 'dimensions_compliant' => 'boolean', 'no_coating' => 'boolean', 'audited_at' => 'datetime', 'efficiency_score' => 'decimal:2', 'precision_score' => 'decimal:2', 'tested_at' => 'datetime'];
    }

    public function members(): HasMany
    {
        return $this->hasMany(Member::class);
    }

    public function loadSamples(): HasMany { return $this->hasMany(BridgeLoadSample::class); }
}
