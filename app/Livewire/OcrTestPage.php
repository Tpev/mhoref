<?php

namespace App\Livewire;

use Illuminate\Support\Facades\Http;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Validate;
use Livewire\Component;
use Livewire\WithFileUploads;

class OcrTestPage extends Component
{
    use WithFileUploads;

    #[Validate('required|file|mimes:pdf|max:20480')] // 20 MB
    public $pdf;

    public array $apiResponse = [];
    public ?string $error = null;

    public function submit()
    {
        $this->reset('apiResponse', 'error');
        $this->validate();

        try {
            $apiUrl = config('services.ocr.url') ?? env('OCR_API_URL');

            if (!$apiUrl) {
                throw new \RuntimeException('OCR_API_URL is not configured.');
            }

            // Attach the uploaded tmp file to the Python API
            $response = Http::timeout(90)
                ->attach('file', fopen($this->pdf->getRealPath(), 'r'), $this->pdf->getClientOriginalName())
                ->post($apiUrl);

            if ($response->failed()) {
                $msg = $response->json('detail') ?? $response->body();
                throw new \RuntimeException('API error: ' . $msg);
            }

            $this->apiResponse = $response->json() ?? [];
            $this->dispatch('ts-toast', [
                'title' => 'OCR complete',
                'description' => 'We parsed your PDF successfully.',
                'icon' => 'success',
            ]);

        } catch (ValidationException $e) {
            throw $e;
        } catch (\Throwable $e) {
            $this->error = $e->getMessage();
            $this->dispatch('ts-toast', [
                'title' => 'OCR failed',
                'description' => $this->error,
                'icon' => 'danger',
            ]);
        }
    }

    public function render()
    {
        return view('livewire.ocr-test-page')->layout('layouts.app');;
    }
}
