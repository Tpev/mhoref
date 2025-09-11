<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StepComment extends Model
{
    use HasFactory;

    protected $fillable = [
        'workflow_step_id',
        'referral_id',
        'user_id',
        'comment',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function step()
    {
        return $this->belongsTo(WorkflowStep::class, 'workflow_step_id');
    }

    public function referral()
    {
        return $this->belongsTo(Referral::class);
    }
}

