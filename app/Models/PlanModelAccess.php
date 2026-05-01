<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PlanModelAccess extends Model
{
    public $timestamps = false;

    protected $table = 'plan_model_access';

    protected $fillable = ['plan_slug', 'model_id', 'created_at'];

    protected $casts = [
        'created_at' => 'datetime',
    ];

    public function plan(): BelongsTo
    {
        return $this->belongsTo(SubscriptionPlan::class, 'plan_slug', 'slug');
    }
}
