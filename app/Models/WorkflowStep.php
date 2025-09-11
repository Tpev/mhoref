<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WorkflowStep extends Model
{
    use HasFactory;

    protected $fillable = [
        'workflow_stage_id',
        'name',
        'type',
        'next_step_id',
        'metadata',
        'order',
		'type',       // 'action', 'decision', etc.
    ];
    protected $casts = [
        'metadata' => 'array', // So 'metadata' is automatically cast to/from array
		'group_can_write'   => 'array',
        'group_can_see'     => 'array',
        'group_get_notif'   => 'array',
    ];
    /**
     * A step belongs to one stage.
     */
    public function stage()
    {
        return $this->belongsTo(WorkflowStage::class, 'workflow_stage_id');
    }

    /**
     * Optionally reference the next step in a linear sequence.
     */
    public function nextStep()
    {
        return $this->belongsTo(WorkflowStep::class, 'next_step_id');
    }

// WorkflowStep.php

public function comments()
{
    return $this->hasMany(StepComment::class);
}



}
