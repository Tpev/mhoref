<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WorkflowStage extends Model
{
    use HasFactory;

    protected $fillable = [
        'workflow_id',
        'name',
        'order',
        'description',
    ];

    /**
     * A stage belongs to a particular workflow.
     */
    public function workflow()
    {
        return $this->belongsTo(Workflow::class);
    }

    /**
     * A stage can have multiple steps.
     */
    public function steps()
    {
        return $this->hasMany(WorkflowStep::class);
    }
}
