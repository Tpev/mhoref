<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ReferralIntake extends Model
{
    use HasFactory;

    protected $fillable = [
        'referral_id',
        'workflow_id',
        'workflow_step_id',
        'submitted_by',
        'submitted_at',

        'patient_first_name',
        'patient_last_name',
        'patient_dob',
        'patient_phone',

        'pcp_first_name',
        'pcp_last_name',
        'pcp_npi',

        'last_visit_note',
        'diag_for_referral',
        'smoking_status',
        'bmi',
        'medication_list',

        'xray_files',
        'surgery_report_path',
        'implant_info',
        'prior_joint_surgery',
    ];

    protected $casts = [
        'submitted_at'       => 'datetime',
        'patient_dob'        => 'date',
        'bmi'                => 'decimal:2',
        'xray_files'         => 'array',
        'prior_joint_surgery'=> 'boolean',
    ];

    // Optional relationships
    public function referral()      { return $this->belongsTo(Referral::class); }
    public function workflow()      { return $this->belongsTo(Workflow::class); }
    public function workflowStep()  { return $this->belongsTo(WorkflowStep::class, 'workflow_step_id'); }
    public function submitter()     { return $this->belongsTo(User::class, 'submitted_by'); }
}
