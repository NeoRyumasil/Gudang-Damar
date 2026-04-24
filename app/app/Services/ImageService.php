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

    
    private string $promptSuffix =
        ', product photography, white background, studio lighting, ' .
        'sharp focus, photorealistic, high quality, 4k';

    private string $negativePrompt =
        'text, typography, lettering, words, letters, watermark, logo, ' .
        'abstract art, painting, illustration, cartoon, anime, ' .
        'person, people, hands, face, ' .
        'blurry, low quality, low resolution, dark background, ' .
        'multiple objects, duplicate, frame, border, signature, NSFW';

    public function __construct()
    {
        $this->token = config('services.huggingface.key');
        $this->model = config('services.huggingface.model');
    }

    public function generate(string $prompt, array $options = [], int $maxRetries = 3): string
    {
        $attempt = 0;

        $fullPrompt = $prompt . $this->promptSuffix;

        Log::info('ImageService fullPrompt: ' . $fullPrompt);

        while ($attempt < $maxRetries) {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->token,
                'Accept'        => 'image/png',
            ])
            ->timeout(120)
            ->post($this->baseUrl . $this->model, [
                'inputs'     => $fullPrompt,
                'parameters' => array_merge([
                    'num_inference_steps' => 30,
                    'guidance_scale'      => 8.5,
                    'negative_prompt'     => $this->negativePrompt,
                ], $options),
            ]);

            Log::info('HF Response Status: ' . $response->status());
            Log::info('HF Response Body (first 300 chars): ' . substr($response->body(), 0, 300));

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