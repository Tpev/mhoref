<?php

namespace App\Http\Controllers;

use App\Models\Referral;
use App\Models\ReferralProgress;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;

class ReferralFormPdfController extends Controller
{
    /**
     * Generate a signed PDF for a completed “form” step.
     *
     * GET /referrals/{referral}/steps/{step}/pdf
     */
    public function __invoke(Request $request, Referral $referral, int $stepId)
    {
        // ─── 1. Authorise ──────────────────────────────────────
             // adjust to your policy

        // ─── 2. Pull latest completed progress & answers ─────
        $progress = ReferralProgress::where([
                'referral_id'      => $referral->id,
                'workflow_step_id' => $stepId,
                'status'           => 'completed',
            ])->latest()->firstOrFail();

        $answers = json_decode($progress->notes, true) ?? [];

        // ─── 3. Resolve absolute path for signature image ────
        $sigAbs = '';
        if (!empty($answers['physician_signature'])
            && str_starts_with($answers['physician_signature'], 'signatures/')) {
            $sigAbs = storage_path('app/public/'.$answers['physician_signature']);
        }

        // ─── 4. Render PDF ────────────────────────────────────
        $pdf = Pdf::loadView('pdfs.referral-order', [
            'data'          => $answers,
            'signaturePath' => $sigAbs,
        ])->setPaper('a4');

        // stream inline in browser
        $fileName = 'referral_'.$referral->id.'_step_'.$stepId.'.pdf';
        return $pdf->stream($fileName);
    }
}
