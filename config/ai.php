<?php

return [

    /*
    |--------------------------------------------------------------------------
    | AI Assistant Master Switch
    |--------------------------------------------------------------------------
    |
    | When disabled, all AI assistance generation endpoints return a friendly
    | service status without crashing the document builder.
    |
    */
    'enabled' => env('AI_ENABLED', true),

    /*
    |--------------------------------------------------------------------------
    | Default AI Provider
    |--------------------------------------------------------------------------
    |
    | Supported: "mock", "openai", "gemini", "anthropic"
    |
    */
    'default_provider' => env('AI_PROVIDER', 'mock'),

    /*
    |--------------------------------------------------------------------------
    | Rate Limiting & Abuse Prevention
    |--------------------------------------------------------------------------
    |
    | Maximum AI generations allowed per user per minute.
    |
    */
    'rate_limit_per_minute' => env('AI_RATE_LIMIT_PER_MINUTE', 25),

    /*
    |--------------------------------------------------------------------------
    | Provider Configurations
    |--------------------------------------------------------------------------
    |
    | API Keys, Base URLs, and default models for each provider.
    | Sensitive keys must always remain server-side.
    |
    */
    'providers' => [

        'mock' => [
            'name' => 'Built-in Mock AI (Smart Heuristics)',
            'model' => 'mock-career-v1',
            'description' => 'Local heuristic generator designed for zero-API-cost rapid testing and development.',
        ],

        'openai' => [
            'name' => 'OpenAI',
            'api_key' => env('OPENAI_API_KEY', env('AI_API_KEY', '')),
            'model' => env('OPENAI_MODEL', env('AI_MODEL', 'gpt-4o-mini')),
            'base_url' => env('OPENAI_BASE_URL', 'https://api.openai.com/v1'),
            'timeout' => 30,
            'max_tokens' => 1500,
            'temperature' => 0.7,
        ],

        'gemini' => [
            'name' => 'Google Gemini',
            'api_key' => env('GEMINI_API_KEY', env('AI_API_KEY', '')),
            'model' => env('GEMINI_MODEL', env('AI_MODEL', 'gemini-1.5-flash')),
            'base_url' => 'https://generativelanguage.googleapis.com/v1beta',
            'timeout' => 30,
        ],

        'anthropic' => [
            'name' => 'Anthropic Claude',
            'api_key' => env('ANTHROPIC_API_KEY', env('AI_API_KEY', '')),
            'model' => env('ANTHROPIC_MODEL', env('AI_MODEL', 'claude-3-5-haiku-20241022')),
            'base_url' => 'https://api.anthropic.com/v1',
            'timeout' => 30,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Feature Toggles
    |--------------------------------------------------------------------------
    |
    | Enable or disable individual AI assistance capabilities.
    |
    */
    'features' => [
        'profile_summary' => true,
        'career_objective' => true,
        'experience_rewrite' => true,
        'project_rewrite' => true,
        'skills_suggestion' => true,
        'content_improve' => true,
        'cover_letter' => true,
        'motivation_letter' => true,
    ],

    /*
    |--------------------------------------------------------------------------
    | Privacy & Data Minimization Settings
    |--------------------------------------------------------------------------
    |
    | Governs what data is sent to external AI providers.
    |
    */
    'privacy' => [
        'send_full_name' => false,
        'send_contact_info' => false, // Exclude email, phone, physical address from prompts
        'send_other_documents' => false, // Never send unrelated user CVs
    ],
];
