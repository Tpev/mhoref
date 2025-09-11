<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ReferralProgress extends Model
{
    use HasFactory;

    protected $table = 'referral_progresses';

    protected $fillable = [
        'referral_id',
        'workflow_step_id',
        'completed_by',
        'completed_at',
        'status',
        'notes',
    ];

    protected $casts = [
        'completed_at' => 'datetime',
	
    ];
public function getNotesDataAttribute(): array
{
    $raw = $this->attributes['notes'] ?? null;

    if (is_array($raw))   return $raw;           // safety if ever cast again
    if (is_string($raw))  return json_decode($raw, true) ?: [];
    return [];
}
    /**
     * Which referral this progress record belongs to.
     */
    public function referral()
    {
        return $this->belongsTo(Referral::class);
    }

    /**
     * Which step has been completed or is in progress.
     */
    public function step()
    {
        return $this->belongsTo(WorkflowStep::class, 'workflow_step_id');
    }

    /**
     * Which user completed this step (if applicable).
     */
    public function completedBy()
    {
        return $this->belongsTo(User::class, 'completed_by');
    }
    /**
     * Relationship to UploadedFile.
     */
    public function uploadedFiles()
    {
        return $this->hasMany(UploadedFile::class);
    }
	    public function user()
    {
        return $this->belongsTo(User::class, 'completed_by');
    }
}
