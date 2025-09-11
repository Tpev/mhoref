<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Referral extends Model
{
    use HasFactory;

    protected $fillable = [
        'workflow_id',
        // 'patient_id', 'status', etc. if needed
        'status',
    ];

    /**
     * A referral is attached to a particular workflow.
     */
    public function workflow()
    {
        return $this->belongsTo(Workflow::class);
    }

    /**
     * A referral can have many progress records.
     */
    public function progress()
    {
        return $this->hasMany(ReferralProgress::class);
    }
    /**
     * Relationship to UploadedFile.
     */
    public function uploadedFiles()
    {
        return $this->hasMany(UploadedFile::class);
    }
	public function comments()
{
    return $this->hasMany(StepComment::class);
}
public function intake()
{
    return $this->hasOne(\App\Models\ReferralIntake::class);
}
}
