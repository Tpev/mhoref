@php
    $docs     = $this->docs;
    $isSigner = $this->isSigner;
@endphp

<div x-data="pdfPreview()" x-init="init()" @keydown.escape.window="close()"
     class="p-4 rounded-lg border shadow hover:shadow-lg transition space-y-4"
     :class="{ 'border-emerald-400 bg-emerald-50/40' : {{ $this->request->status === 'completed' }} }">

    {{-- header --}}
    <div class="flex items-center justify-between">
        <div>
            <h4 class="font-semibold text-gray-900">
                {{ $this->request->referral->patient_name ?? 'Referral #'.$this->request->referral_id }}
            </h4>
            <p class="text-xs text-gray-500">
                {{ $this->request->documents->count() }} doc{{ $this->request->documents->count() > 1 ? 's' : '' }}
                &middot; {{ $this->request->created_at->diffForHumans() }}
            </p>
        </div>

        @if($this->request->status === 'completed')
            <span class="inline-flex items-center px-2 py-0.5 text-xs font-semibold
                         bg-emerald-600 text-white rounded-full shadow">
                All signed
            </span>
        @endif
    </div>

    {{-- dialog (same as before) --}}
    <dialog x-ref="dlg" class="rounded-lg shadow-2xl backdrop:bg-black/70 p-0"
            x-trap="$refs.dlg.open" @close.window="close()">
        <div class="relative w-[95vw] h-[95vh] overflow-visible">
            <button class="absolute top-4 right-4 flex items-center justify-center
                           w-12 h-12 rounded-full bg-white/95 text-gray-800 text-3xl
                           shadow-lg ring-1 ring-gray-200 transition
                           hover:bg-white hover:shadow-xl hover:scale-[1.08]
                           focus:outline-none focus:ring-2 focus:ring-emerald-500"
                    @click="close()" aria-label="Close preview">&times;</button>
            <embed :src="src" type="application/pdf"
                   class="w-full h-full rounded-lg border-4 border-white">
        </div>
    </dialog>

    {{-- document list --}}
    <ul class="space-y-3">
        @foreach($docs as $doc)
            @php
                $pdfUrl = $doc->signed_at
                          ? Storage::url($doc->signed_pdf_path)
                          : Storage::url($doc->orig_path);
            @endphp
            <li wire:key="doc-{{ $doc->id }}"
                class="p-3 bg-gray-50 rounded flex justify-between items-center
                       {{ $doc->signed_at ? 'opacity-60 line-through' : '' }}">
                <button class="text-emerald-700 hover:underline text-sm"
                        @click="open('{{ $pdfUrl }}#toolbar=0&navpanes=0&scrollbar=0')">
                    {{ $doc->orig_name }}
                </button>

                <div class="flex items-center space-x-3">
                    @if($doc->signed_at)
                        <span class="text-emerald-600 font-semibold">✓</span>
                        <a href="{{ $pdfUrl }}" target="_blank"
                           class="text-xs underline decoration-dotted">Download</a>
                    @elseif($isSigner)
                        <button class="text-xs px-3 py-1 border border-emerald-600
                                       text-emerald-700 rounded hover:bg-emerald-600
                                       hover:text-white transition"
                                wire:click="{{ auth()->user()->default_signature_path
                                                ? "useSavedSignature({$doc->id})"
                                                : "openSignInline({$doc->id})" }}">
                            {{ auth()->user()->default_signature_path ? 'Sign now' : 'Sign' }}
                        </button>
                    @endif
                </div>
            </li>

            {{-- inline SignaturePad --}}
            @if($openDocId === $doc->id)
                <div wire:ignore x-init="pad = new SignaturePad($refs.canvas)"
                     class="mt-2 space-y-2">
                    <canvas x-ref="canvas"
                            class="w-full h-36 rounded border bg-white shadow-inner"></canvas>
                    <div class="flex justify-end space-x-2 text-xs">
                        <button wire:click="cancelSignInline">Cancel</button>
                        <button class="px-3 py-1 bg-emerald-600 text-white rounded
                                       hover:bg-emerald-700"
                                @click="$wire.signatureData = pad.toDataURL('image/png');
                                        $wire.saveSignature();">
                            Save
                        </button>
                    </div>
                </div>
            @endif
        @endforeach
    </ul>

@once
    {{-- load only once on the dashboard page --}}
    <script src="https://cdn.jsdelivr.net/npm/signature_pad@4.1.5/dist/signature_pad.umd.min.js"></script>
    <link  rel="stylesheet"
           href="https://unpkg.com/dialog-polyfill@0.5.6/dist/dialog-polyfill.css">
    <script src="https://unpkg.com/dialog-polyfill@0.5.6/dist/dialog-polyfill.min.js"></script>
    <style>[x-cloak]{display:none!important}.freeze{overflow:hidden!important}</style>
@endonce

<script>
function pdfPreview(){
  return{
    src:'',
    init(){
      if(window.dialogPolyfill && this.$refs.dlg && !this.$refs.dlg.showModal){
        dialogPolyfill.registerDialog(this.$refs.dlg);
      }
    },
    open(url){this.src=url;this.$refs.dlg.showModal();document.documentElement.classList.add('freeze');},
    close(){this.$refs.dlg.close();this.src='';document.documentElement.classList.remove('freeze');}
  }
}
</script>


</div>

