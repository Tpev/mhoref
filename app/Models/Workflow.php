<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Workflow extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'name',
        'description',
    ];

    /**
     * A workflow can have many stages.
     */
    public function stages()
    {
        return $this->hasMany(WorkflowStage::class);
    }
	public function steps()
{
    return $this->hasManyThrough(
        WorkflowStep::class, 
        WorkflowStage::class,
        'workflow_id',    // Foreign key on WorkflowStage table...
        'workflow_stage_id', // Foreign key on WorkflowStep table...
        'id',             // Local key on Workflow table...
        'id'              // Local key on WorkflowStage table...
    );
}

}
