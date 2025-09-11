<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UploadedFile extends Model
{
    use HasFactory;

    protected $fillable = [
        'referral_id',
        'referral_progress_id',
        'original_name',
        'path',
    ];

    /**
     * Relationship to Referral.
     */
    public function referral()
    {
        return $this->belongsTo(Referral::class);
    }

    /**
     * Relationship to ReferralProgress.
     */
    public function referralProgress()
    {
        return $this->belongsTo(ReferralProgress::class);
    }
	    // Optionally, add an accessor for the file URL
    public function getUrlAttribute()
    {
        return asset('storage/' . $this->path);
    }
}
