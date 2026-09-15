<?php

namespace App\Services\Ai;

use App\Models\AiUsageLog;
use App\Models\Cv;
use App\Models\User;
use Illuminate\Support\Facades\Log;

class AiCareerAssistantService
{
    public function __construct(
        protected AiManager $aiManager
    ) {}

    /**
     * Build minimal sanitized document context for the AI prompt.
     */
    protected function extractDocumentContext(Cv $cv): array
    {
        $cv->loadMissing(['personalInfo', 'letterDetail', 'experiences', 'educations', 'skills', 'certifications', 'projects']);

        $skillsList = $cv->skills->pluck('name')->toArray();
        $recentExp = $cv->experiences->take(3)->map(function ($exp) {
            return "{$exp->job_title} at {$exp->company_name}";
        })->toArray();
        $recentEdu = $cv->educations->take(2)->map(function ($edu) {
            return "{$edu->degree} in {$edu->field_of_study} from {$edu->institution}";
        })->toArray();

        return [
            'document_type' => $cv->documentType?->slug ?? 'standard-cv',
            'full_name' => $cv->personalInfo?->full_name ?? 'Candidate',
            'job_title' => $cv->personalInfo?->job_title ?? $cv->title,
            'skills' => $skillsList,
            'recent_experience' => $recentExp,
            'recent_education' => $recentEdu,
            'has_summary' => !empty($cv->summary),
        ];
    }

    /**
     * Dispatch AI generation and record audit log.
     */
    protected function executeAndLog(string $feature, Cv $cv, string $prompt, array $params = [], ?User $user = null): AiResponse
    {
        $user = $user ?: $cv->user;
        $context = $this->extractDocumentContext($cv);

        if (!$this->aiManager->isAiEnabled()) {
            return AiResponse::failure('AI Assistant is currently offline for maintenance.', 'system', 'disabled');
        }

        if (!$this->aiManager->isFeatureEnabled($feature)) {
            return AiResponse::failure("Feature '{$feature}' is temporarily unavailable.", 'system', 'disabled');
        }

        $provider = $this->aiManager->provider();
        $options = [
            'feature' => $feature,
            'params' => $params,
            'document_data' => $context,
        ];

        try {
            $response = $provider->generate($prompt, $options);

            // If external provider failed and we have mock fallback, recover cleanly
            if (!$response->success && $provider->getProviderName() !== 'mock') {
                Log::info("External AI provider {$provider->getProviderName()} failed. Using Mock heuristic fallback.");
                $mockProvider = $this->aiManager->provider('mock');
                $response = $mockProvider->generate($prompt, $options);
            }
        } catch (\Throwable $e) {
            Log::error("AI Service Exception on feature [{$feature}]: " . $e->getMessage());
            $mockProvider = $this->aiManager->provider('mock');
            $response = $mockProvider->generate($prompt, $options);
        }

        // Record usage log
        try {
            AiUsageLog::create([
                'user_id' => $user->id,
                'cv_id' => $cv->id,
                'feature' => $feature,
                'provider' => $response->provider ?? $provider->getProviderName(),
                'model' => $response->model ?? $provider->getModel(),
                'prompt_tokens' => $response->promptTokens ?? 0,
                'completion_tokens' => $response->completionTokens ?? 0,
                'total_tokens' => $response->totalTokens ?? 0,
                'status' => $response->success ? 'success' : 'failed',
                'context_summary' => [
                    'feature' => $feature,
                    'document_id' => $cv->id,
                    'document_type' => $context['document_type'],
                    'params_keys' => array_keys($params),
                ],
                'error_message' => $response->errorMessage,
                'ip_address' => request()->ip(),
            ]);
        } catch (\Throwable $logError) {
            Log::warning("Could not persist AI usage log: " . $logError->getMessage());
        }

        $response->feature = $feature;

        return $response;
    }

    /**
     * 1. Profile Summary Generator (3 variants: Professional, Concise, Modern)
     */
    public function generateProfileSummary(Cv $cv, array $params = [], ?User $user = null): AiResponse
    {
        $context = $this->extractDocumentContext($cv);
        $role = $params['target_position'] ?? $context['job_title'];
        $years = $params['years_of_experience'] ?? '5+';
        $skills = $params['key_skills'] ?? implode(', ', array_slice($context['skills'], 0, 5));
        $field = $params['profession'] ?? $params['industry'] ?? 'Technology';

        $prompt = "Generate 3 professional profile summary variants (Professional, Concise, Modern) for a {$role} with {$years} years experience in {$field}. Key skills: {$skills}. Do not fabricate non-provided achievements.";

        return $this->executeAndLog('profile_summary', $cv, $prompt, $params, $user);
    }

    /**
     * 2. Career Objective Generator
     */
    public function generateCareerObjective(Cv $cv, array $params = [], ?User $user = null): AiResponse
    {
        $context = $this->extractDocumentContext($cv);
        $role = $params['target_position'] ?? $context['job_title'];
        $skills = $params['key_skills'] ?? implode(', ', array_slice($context['skills'], 0, 4));

        $prompt = "Generate 3 tailored career objective variants for a {$role} leveraging skills in {$skills}.";

        return $this->executeAndLog('career_objective', $cv, $prompt, $params, $user);
    }

    /**
     * 3. Work Experience Description Generator & Rewriter
     */
    public function rewriteExperience(Cv $cv, array $params = [], ?User $user = null): AiResponse
    {
        $mode = $params['mode'] ?? 'bullets';
        $position = $params['position'] ?? 'Engineer';
        $company = $params['company'] ?? 'Company';
        $draft = $params['draft'] ?? '';

        $prompt = "Transform the following work experience for {$position} at {$company} into professional resume content. Mode: {$mode}. Draft notes: \"{$draft}\". Avoid unverified statistics.";

        return $this->executeAndLog('experience_rewrite', $cv, $prompt, $params, $user);
    }

    /**
     * 4. Project Description Generator & Rewriter
     */
    public function rewriteProject(Cv $cv, array $params = [], ?User $user = null): AiResponse
    {
        $mode = $params['mode'] ?? 'describe';
        $name = $params['project_name'] ?? 'Project';
        $tech = $params['technologies'] ?? 'Software stack';
        $draft = $params['draft'] ?? '';

        $prompt = "Create a project summary for {$name} built with {$tech}. Mode: {$mode}. Draft input: \"{$draft}\".";

        return $this->executeAndLog('project_rewrite', $cv, $prompt, $params, $user);
    }

    /**
     * 5. Skills Suggestion
     */
    public function suggestSkills(Cv $cv, array $params = [], ?User $user = null): AiResponse
    {
        $context = $this->extractDocumentContext($cv);
        $role = $params['target_position'] ?? $context['job_title'];

        $prompt = "Suggest categorized hard, soft, and tool skills for a {$role}.";

        return $this->executeAndLog('skills_suggestion', $cv, $prompt, $params, $user);
    }

    /**
     * 6. Content Improvement & ATS Rewriter
     */
    public function improveContent(Cv $cv, array $params = [], ?User $user = null): AiResponse
    {
        $tone = $params['tone'] ?? 'professional';
        $text = $params['text'] ?? '';

        $prompt = "Improve the following text for a career document with tone: {$tone}. Text: \"{$text}\".";

        return $this->executeAndLog('content_improve', $cv, $prompt, $params, $user);
    }

    /**
     * 7. Cover Letter Generator
     */
    public function generateCoverLetter(Cv $cv, array $params = [], ?User $user = null): AiResponse
    {
        $context = $this->extractDocumentContext($cv);
        $role = $params['job_title'] ?? $context['job_title'];
        $company = $params['company_name'] ?? 'Target Organization';

        $prompt = "Generate a structured professional cover letter for {$role} at {$company}.";

        return $this->executeAndLog('cover_letter', $cv, $prompt, $params, $user);
    }

    /**
     * 8. Motivation Letter Generator
     */
    public function generateMotivationLetter(Cv $cv, array $params = [], ?User $user = null): AiResponse
    {
        $context = $this->extractDocumentContext($cv);
        $program = $params['job_title'] ?? 'Graduate Academic Program';
        $institution = $params['company_name'] ?? 'University';

        $prompt = "Generate a structured academic motivation letter for {$program} at {$institution}.";

        return $this->executeAndLog('motivation_letter', $cv, $prompt, $params, $user);
    }
}
