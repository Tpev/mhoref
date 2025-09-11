{{-- ─────────────────────────────────────────────────────────────
|  Signature Response – step card
|───────────────────────────────────────────────────────────── --}}

@php
    use App\Models\SignatureRequest;

    /* 1. Locate the matching SignatureRequest row for this step */
    $request = SignatureRequest::with('documents')
                ->where('referral_id', $referral->id)
                ->when(
                    isset($step->metadata['request_step_id']),
                    fn ($q) => $q->where(
                        'workflow_step_id',
                        $step->metadata['request_step_id']
                    )
                )
                ->latest()
                ->first();

    /* 2. Collections & helpers for the view */
    $docs     = $request?->documents ?? collect();
    $isSigner = auth()->id() === ($request->assigned_user_id ?? null);
@endphp

{{-- ── load once: SignaturePad + dialog() polyfill --}}
@once
    <script src="https://cdn.jsdelivr.net/npm/signature_pad@4.1.5/dist/signature_pad.umd.min.js"></script>

    <link  rel="stylesheet"
           href="https://unpkg.com/dialog-polyfill@0.5.6/dist/dialog-polyfill.css">
    <script src="https://unpkg.com/dialog-polyfill@0.5.6/dist/dialog-polyfill.min.js"></script>

    <style>
        [x-cloak]{display:none!important}          /* hide until Alpine boots   */
        .freeze{overflow:hidden!important}         /* lock scroll on modal open */
    </style>
@endonce

{{-- ─────────────────────────────────────────────────────────────
|  Alpine wrapper – handles PDF preview dialog
|───────────────────────────────────────────────────────────── --}}
<div
    x-data="pdfPreview()"
    x-init="init()"
    @keydown.escape.window="close()"
    class="space-y-6"
>

    {{-- header --}}
    <div class="flex items-center justify-between">
        <h5 class="font-semibold text-gray-800 dark:text-gray-100">
            {{ $step->name }}
        </h5>

        @if($request && $request->status === 'completed')
            <span class="inline-flex items-center px-2 py-0.5 text-xs font-semibold
                         bg-emerald-600 text-white rounded-full shadow">
                All signed
            </span>
        @endif
    </div>

    {{-- waiting placeholder --}}
    @unless($request)
        <p class="italic text-sm text-gray-500">Waiting for signature request…</p>
        @return
    @endunless

    {{-- SINGLE shared <dialog> --}}
<dialog x-ref="dlg"
        class="rounded-lg shadow-2xl backdrop:bg-black/70 p-0"
        x-trap="$refs.dlg.open"
        @close.window="close()">
    {{-- let overflow spill so the shadow isn’t clipped --}}
    <div class="relative w-[95vw] h-[95vh] overflow-visible">

        {{-- close button – fully inside the view --}}
        <button
            class="absolute top-4 right-4 flex items-center justify-center
                   w-12 h-12 rounded-full bg-white/95 text-gray-800 text-3xl
                   shadow-lg ring-1 ring-gray-200 transition
                   hover:bg-white hover:shadow-xl hover:scale-[1.08]
                   focus:outline-none focus:ring-2 focus:ring-emerald-500"
            @click="close()"
            aria-label="Close preview">
            &times;
        </button>

        <embed :src="src"
               type="application/pdf"
               class="w-full h-full rounded-lg border-4 border-white">
    </div>
</dialog>

    {{-- document list --}}
    <ul class="space-y-4">
        @foreach($docs as $doc)
            @php
                $hasSavedSig = filled(auth()->user()->default_signature_path);
                $pdfUrl      = $doc->signed_at
                                 ? Storage::url($doc->signed_pdf_path)
                                 : Storage::url($doc->orig_path);
            @endphp

            <li wire:key="doc-{{ $doc->id }}"
                class="p-4 rounded-lg border shadow-sm transition
                       {{ $doc->signed_at
                              ? 'border-emerald-400 bg-emerald-50/40'
                              : 'border-gray-200 hover:shadow-md' }}">

                {{-- top row --}}
                <div class="flex items-center justify-between text-sm">
                    <button class="text-emerald-700 hover:underline"
                            @click="open('{{ $pdfUrl }}#toolbar=0&navpanes=0&scrollbar=0')">
                        {{ $doc->orig_name }}
                    </button>

                    <div class="flex items-center space-x-3">
                        @if($doc->signed_at)
                            <span class="text-emerald-600 font-semibold">✓</span>
                            <a href="{{ $pdfUrl }}"
                               class="text-xs underline decoration-dotted underline-offset-4"
                               target="_blank">Download</a>
                        @endif

                        @if($isSigner && !$doc->signed_at)
                            <button
                                class="text-xs px-3 py-1 border border-emerald-600
                                       text-emerald-700 rounded
                                       hover:bg-emerald-600 hover:text-white
                                       focus:outline-none focus:ring-2
                                       focus:ring-emerald-500 transition"
                                wire:click="{{ $hasSavedSig
                                                ? "useSavedSignature({$doc->id})"
                                                : "openSignInline({$doc->id})" }}">
                                {{ $hasSavedSig ? 'Sign now' : 'Sign' }}
                            </button>
                        @endif
                    </div>
                </div>

                {{-- inline SignaturePad --}}
                @if(($openDocId ?? null) === $doc->id)
                    <div wire:ignore
                         x-init="pad = new SignaturePad($refs.canvas)"
                         class="mt-4 space-y-3">

                        <canvas x-ref="canvas"
                                class="w-full h-40 rounded border
                                       bg-white shadow-inner"></canvas>

                        <div class="flex justify-end space-x-3 text-xs">
                            <button class="px-3 py-1"
                                    wire:click="cancelSignInline">
                                Cancel
                            </button>

                            <button class="px-3 py-1 bg-emerald-600 text-white rounded
                                           hover:bg-emerald-700
                                           focus:outline-none focus:ring-2
                                           focus:ring-emerald-500"
                                    @click="
                                        $wire.signatureData = pad.toDataURL('image/png');
                                        $wire.saveSignature();
                                    ">
                                Save
                            </button>
                        </div>
                    </div>
                @endif
            </li>
        @endforeach
    </ul>
</div>

{{-- ── Alpine helper --}}
<script>
function pdfPreview () {
    return {
        src: '',
        init () {
            /* register <dialog> for browsers missing native support */
            if (window.dialogPolyfill && this.$refs.dlg &&
                !this.$refs.dlg.showModal) {
                dialogPolyfill.registerDialog(this.$refs.dlg);
            }
        },
        open (url) {
            this.src = url;
            this.$refs.dlg.showModal();
            document.documentElement.classList.add('freeze');
        },
        close () {
            this.$refs.dlg.close();
            this.src = '';
            document.documentElement.classList.remove('freeze');
        }
    }
}
</script>
