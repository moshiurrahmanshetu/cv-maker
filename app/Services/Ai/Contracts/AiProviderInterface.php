<?php

namespace App\Services\Ai\Contracts;

use App\Services\Ai\AiResponse;

interface AiProviderInterface
{
    /**
     * Generate content from prompt and contextual options.
     */
    public function generate(string $prompt, array $options = []): AiResponse;

    /**
     * Get the identifier name of the provider.
     */
    public function getProviderName(): string;

    /**
     * Get the active model name.
     */
    public function getModel(): string;
}
