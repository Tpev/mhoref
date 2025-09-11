<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use thiagoalessio\TesseractOCR\TesseractOCR;

class OcrMedExtractController extends Controller
{
    public function extract(Request $request)
    {
        $request->validate([
            'image' => 'required|image|max:10240',
        ]);

$path = $request->file('image')->store('', 'ocr'); // stored directly in app/private/ocr-temps
$fullPath = storage_path('app/private/ocr-temps/' . $path);

if (!file_exists($fullPath)) {
    return response()->json(['error' => 'Image not found or unreadable'], 400);
}

$text = (new TesseractOCR($fullPath))
    ->executable('C:/Program Files/Tesseract-OCR/tesseract.exe')
    ->run();



$lines = array_filter(array_map('trim', explode("\n", $text)));
$meds = [];

foreach ($lines as $line) {
    if (
        strlen($line) < 200 &&
        preg_match('/\b(?:mg|ml|cp|cpr|gél|pom|collyre|amp|patch|supp|%)\b/i', $line) && 
        preg_match('/[A-Z]{3,}/', $line)
    ) {
        $meds[] = $line;
    }
}


return response()->json([

    'extracted_meds' => $meds,
]);


    }
}
