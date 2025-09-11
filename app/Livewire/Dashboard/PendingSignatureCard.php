<?php

namespace App\Livewire\Dashboard;

use Livewire\Component;
use App\Models\SignatureRequest;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use App\Services\PdfSigner;

class PendingSignatureCard extends Component
{
    public SignatureRequest $request;

    /* interactive state  */
    public ?int   $openDocId     = null;
    public string $signatureData = '';   // base64 PNG from SignaturePad

    /* computed */
    public function getDocsProperty()    { return $this->request->documents; }
    public function getIsSignerProperty(){ return auth()->id() === $this->request->assigned_user_id; }

    /* -------- actions ---------- */
    public function openSignInline(int $docId)
    {
        $this->openDocId = $docId;
    }

    public function cancelSignInline()
    {
        $this->openDocId     = null;
        $this->signatureData = '';
    }

    public function useSavedSignature(int $docId)
    {
        $url  = auth()->user()->default_signature_path;      // e.g. 'signatures/profile/42.png'
        $path = ltrim(Str::after($url, 'signatures'), '/');  // normalize
        if (! Storage::disk('public')->exists("signatures/{$path}")) {
            session()->flash('error', 'Saved signature not found');
            return;
        }
        $raw = Storage::disk('public')->get("signatures/{$path}");
        $this->applySignature($docId, $raw);
    }

    public function saveSignature()
    {
        if (! $this->openDocId || ! str_starts_with($this->signatureData, 'data:image')) {
            session()->flash('error','No signature data');
            return;
        }

        // strip the data URI prefix, decode to raw PNG
        $raw = base64_decode(Str::after($this->signatureData, ','));
        $this->applySignature($this->openDocId, $raw);
    }

    /**
     * Core logic: store PNG, call PdfSigner, update DB, refresh UI.
     */
    private function applySignature(int $docId, string $pngRaw): void
    {
        // 1. Locate the document
        $doc = $this->docs->firstWhere('id', $docId);

        // 2. Persist the PNG to public storage
        $pngRel = 'signatures/' . Str::uuid() . '.png';
        Storage::disk('public')->put($pngRel, $pngRaw);

        // 3. Flatten onto the PDF (last page, bottom-right by default)
        $signedRel = PdfSigner::sign(
            $doc->orig_path,  // relative path on public disk
            $pngRel,
            ['w'=>100]        // override width if you like; x/y default to bottom-right
        );

        // 4. Update the document record
        $doc->update([
            'signature_png_path' => $pngRel,
            'signed_pdf_path'    => $signedRel,
            'signed_at'          => now(),
        ]);

        // 5. If all docs signed, mark request completed
        if ($this->docs->every->signed_at) {
            $this->request->update(['status' => 'completed']);
        }

        // 6. Close pad and refresh
        $this->openDocId     = null;
        $this->signatureData = '';
        $this->dispatch('signature-saved');
        session()->flash('success','Document signed.');
    }

    public function render()
    {
        return view('livewire.dashboard.pending-signature-card');
    }
}
