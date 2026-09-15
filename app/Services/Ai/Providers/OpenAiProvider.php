<?php

namespace App\Services\Ai\Providers;

use App\Services\Ai\AiResponse;
use App\Services\Ai\Contracts\AiProviderInterface;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class OpenAiProvider implements AiProviderInterface
{
    protected string $providerName = 'openai';
    protected string $apiKey;
    protected string $model;
    protected string $baseUrl;
    protected int $timeout;
    protected float $temperature;
    protected int $maxTokens;

    public function __construct()
    {
        $config = config('ai.providers.openai', []);
        $this->apiKey = $config['api_key'] ?? '';
        $this->model = $config['model'] ?? 'gpt-4o-mini';
        $this->baseUrl = rtrim($config['base_url'] ?? 'https://api.openai.com/v1', '/');
        $this->timeout = $config['timeout'] ?? 30;
        $this->temperature = (float)($config['temperature'] ?? 0.7);
        $this->maxTokens = (int)($config['max_tokens'] ?? 1500);
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
            return AiResponse::failure('OpenAI API key is missing or not configured in environment.', $this->providerName, $this->model);
        }

        try {
            $systemPrompt = "You are a world-class professional career advisor and resume editor. Provide precise, impactful, professional, metric-oriented content without fabricating unverified facts, achievements, or employment history.";

            $response = Http::withToken($this->apiKey)
                ->timeout($this->timeout)
                ->post("{$this->baseUrl}/chat/completions", [
                    'model' => $this->model,
                    'messages' => [
                        ['role' => 'system', 'content' => $systemPrompt],
                        ['role' => 'user', 'content' => $prompt],
                    ],
                    'temperature' => $this->temperature,
                    'max_tokens' => $this->maxTokens,
                ]);

            if ($response->successful()) {
                $data = $response->json();
                $content = $data['choices'][0]['message']['content'] ?? '';
                $promptTokens = $data['usage']['prompt_tokens'] ?? 0;
                $completionTokens = $data['usage']['completion_tokens'] ?? 0;

                return AiResponse::success($content, null, $promptTokens, $completionTokens, $this->providerName, $this->model, $data);
            }

            $errorMsg = $response->json('error.message') ?? 'OpenAI API request failed with status ' . $response->status();
            Log::warning("OpenAI API error: {$errorMsg}");
            return AiResponse::failure($errorMsg, $this->providerName, $this->model, $response->json());
        } catch (\Throwable $e) {
            Log::error("OpenAI Exception: " . $e->getMessage());
            return AiResponse::failure('Unable to connect to AI provider: ' . $e->getMessage(), $this->providerName, $this->model);
        }
    }
}
