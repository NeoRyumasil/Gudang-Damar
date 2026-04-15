<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Exception;

class ImageService
{
    private string $token;
    private string $model;
    private string $baseUrl = 'https://router.huggingface.co/hf-inference/models/';

    public function __construct()
    {
        $this->token = config('services.huggingface.key');
        $this->model = config('services.huggingface.model');
    }

   public function generate(string $prompt, array $options = [], int $maxRetries = 3): string
{
    $attempt = 0;

    while ($attempt < $maxRetries) {
        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
            'Accept'        => 'image/png',
        ])
        ->timeout(120)
        ->post($this->baseUrl . $this->model, [
            'inputs'     => $prompt,
            'parameters' => array_merge([
                'num_inference_steps' => 20,
                'guidance_scale'      => 7.5,
            ], $options),
        ]);

        // ⬇️ Tambah ini untuk debug
        Log::info('HF Response Status: ' . $response->status());
        Log::info('HF Response Headers: ' . json_encode($response->headers()));
        Log::info('HF Response Body: ' . $response->body());

        if ($response->status() === 503) {
            $waitTime = (int) ($response->json('estimated_time', 20));
            $attempt++;

            if ($attempt >= $maxRetries) {
                throw new Exception("Model tidak merespons setelah {$maxRetries} percobaan.");
            }

            sleep(min($waitTime, 30));
            continue;
        }

        if ($response->failed()) {
            throw new Exception("API error: " . $response->body());
        }

        $filename = 'generated/' . Str::uuid() . '.png';
        Storage::disk('public')->put($filename, $response->body());

        return Storage::url($filename);
    }

    throw new Exception("Failed to generate image after {$maxRetries} attempts.");
}
}