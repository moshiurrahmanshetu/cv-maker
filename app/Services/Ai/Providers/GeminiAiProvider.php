<?php

namespace App\Services\Ai\Providers;

use App\Services\Ai\AiResponse;
use App\Services\Ai\Contracts\AiProviderInterface;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GeminiAiProvider implements AiProviderInterface
{
    protected string $providerName = 'gemini';
    protected string $apiKey;
    protected string $model;
    protected string $baseUrl;
    protected int $timeout;

    public function __construct()
    {
        $config = config('ai.providers.gemini', []);
        $this->apiKey = $config['api_key'] ?? '';
        $this->model = $config['model'] ?? 'gemini-1.5-flash';
        $this->baseUrl = rtrim($config['base_url'] ?? 'https://generativelanguage.googleapis.com/v1beta', '/');
        $this->timeout = $config['timeout'] ?? 30;
    }

    public function getProviderName(): string
    {
        return $this->providerName;
    }

    public function getModel(): string
    {
        return $this->model;
    }

    public function generate(string $prompt, array $options = []): AiResponse
    {
        if (empty($this->apiKey)) {
            return AiResponse::failure('Gemini API key is missing or not configured in environment.', $this->providerName, $this->model);
        }

        try {
            $endpoint = "{$this->baseUrl}/models/{$this->model}:generateContent?key={$this->apiKey}";

            $response = Http::timeout($this->timeout)->post($endpoint, [
                'contents' => [
                    [
                        'parts' => [
                            ['text' => $prompt]
                        ]
                    ]
                ],
                'generationConfig' => [
                    'temperature' => 0.7,
                    'maxOutputTokens' => 1500,
                ]
            ]);

            if ($response->successful()) {
                $data = $response->json();
                $content = $data['candidates'][0]['content']['parts'][0]['text'] ?? '';
                $promptTokens = $data['usageMetadata']['promptTokenCount'] ?? 0;
                $completionTokens = $data['usageMetadata']['candidatesTokenCount'] ?? 0;

                return AiResponse::success($content, null, $promptTokens, $completionTokens, $this->providerName, $this->model, $data);
            }

            $errorMsg = $response->json('error.message') ?? 'Gemini API request failed with status ' . $response->status();
            Log::warning("Gemini API error: {$errorMsg}");
            return AiResponse::failure($errorMsg, $this->providerName, $this->model, $response->json());
        } catch (\Throwable $e) {
            Log::error("Gemini Exception: " . $e->getMessage());
            return AiResponse::failure('Unable to connect to Gemini AI provider: ' . $e->getMessage(), $this->providerName, $this->model);
        }
    }
}
