<?php

namespace App\Services;

use setasign\Fpdi\Fpdi;
use Illuminate\Support\Facades\Storage;

class PdfSigner
{
    public static function sign(string $srcPdf, string $sigPng, array $opts): string
    {
        $disk   = Storage::disk('public');
        $absSrc = $disk->path($srcPdf);
        $absPng = $disk->path($sigPng);

        $pdf  = new Fpdi();
        $pageCount = $pdf->setSourceFile($absSrc);
        $tpl = $pdf->importPage(1);
        $size = $pdf->getTemplateSize($tpl);

        $pdf->AddPage($size['orientation'], [$size['width'], $size['height']]);
        $pdf->useTemplate($tpl);

        $pdf->Image(
            $absPng,
            $opts['x'] ?? 150,
            $opts['y'] ?? 250,
            $opts['w'] ?? 40,
            0,
            'PNG'
        );

        $newRel = str_replace('.pdf', '_signed.pdf', $srcPdf);
        $pdf->Output($disk->path($newRel), 'F');

        return $newRel;         // path relative to public disk
    }
}
