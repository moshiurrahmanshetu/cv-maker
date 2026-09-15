<?php

namespace App\Services\Ai;

class AiResponse
{
    public function __construct(
        public bool $success,
        public string|array|null $content = null,
        public ?array $variants = null,
        public ?int $promptTokens = 0,
        public ?int $completionTokens = 0,
        public ?int $totalTokens = 0,
        public ?string $provider = null,
        public ?string $model = null,
        public ?string $errorMessage = null,
        public ?array $rawResponse = null,
        public ?string $feature = null
    ) {}

    public static function success(
        string|array $content,
        ?array $variants = null,
        int $promptTokens = 0,
        int $completionTokens = 0,
        ?string $provider = null,
        ?string $model = null,
        ?array $rawResponse = null,
        ?string $feature = null
    ): self {
        return new self(
            success: true,
            content: $content,
            variants: $variants ?? (is_array($content) && isset($content['variants']) ? $content['variants'] : null),
            promptTokens: $promptTokens,
            completionTokens: $completionTokens,
            totalTokens: $promptTokens + $completionTokens,
            provider: $provider,
            model: $model,
            rawResponse: $rawResponse,
            feature: $feature
        );
    }

    public static function failure(
        string $errorMessage,
        ?string $provider = null,
        ?string $model = null,
        ?array $rawResponse = null,
        ?string $feature = null
    ): self {
        return new self(
            success: false,
            content: null,
            variants: null,
            promptTokens: 0,
            completionTokens: 0,
            totalTokens: 0,
            provider: $provider,
            model: $model,
            errorMessage: $errorMessage,
            rawResponse: $rawResponse,
            feature: $feature
        );
    }

    public function toArray(): array
    {
        $data = [];
        if (is_array($this->content)) {
            $data = $this->content;
        } elseif (is_string($this->content)) {
            $data = ['text' => $this->content];
        }
        if ($this->variants) {
            $data['variants'] = $this->variants;
        }

        return [
            'success' => $this->success,
            'feature' => $this->feature,
            'data' => $data,
            'content' => $this->content,
            'variants' => $this->variants,
            'tokens' => [
                'prompt' => $this->promptTokens,
                'completion' => $this->completionTokens,
                'total' => $this->totalTokens,
            ],
            'tokens_used' => $this->totalTokens,
            'provider' => $this->provider,
            'model' => $this->model,
            'error' => $this->errorMessage,
        ];
    }
}
