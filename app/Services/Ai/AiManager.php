<?php

namespace App\Services\Ai;

use App\Services\Ai\Contracts\AiProviderInterface;
use App\Services\Ai\Providers\GeminiAiProvider;
use App\Services\Ai\Providers\MockAiProvider;
use App\Services\Ai\Providers\OpenAiProvider;
use InvalidArgumentException;

class AiManager
{
    protected array $providers = [];

    public function provider(?string $name = null): AiProviderInterface
    {
        $name = $name ?: config('ai.default_provider', 'mock');

        if (!isset($this->providers[$name])) {
            $this->providers[$name] = $this->createProvider($name);
        }

        return $this->providers[$name];
    }

    protected function createProvider(string $name): AiProviderInterface
    {
        return match ($name) {
            'mock' => new MockAiProvider(),
            'openai' => new OpenAiProvider(),
            'gemini' => new GeminiAiProvider(),
            default => new MockAiProvider(),
        };
    }

    public function isAiEnabled(): bool
    {
        return (bool) config('ai.enabled', true);
    }

    public function isFeatureEnabled(string $feature): bool
    {
        return (bool) config("ai.features.{$feature}", true);
    }
}
