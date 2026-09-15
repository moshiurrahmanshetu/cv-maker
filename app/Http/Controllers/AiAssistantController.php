<?php

namespace App\Http\Controllers;

use App\Models\Cv;
use App\Models\CvSkill;
use App\Services\Ai\AiCareerAssistantService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AiAssistantController extends Controller
{
    public function __construct(
        protected AiCareerAssistantService $aiService
    ) {}

    /**
     * Generate Profile Summary variants with AI.
     */
    public function generateSummary(Request $request, Cv $cv): JsonResponse
    {
        $this->authorize('update', $cv);

        $params = $request->validate([
            'target_position' => ['nullable', 'string', 'max:255'],
            'profession' => ['nullable', 'string', 'max:255'],
            'specialization' => ['nullable', 'string', 'max:255'],
            'years_of_experience' => ['nullable', 'string', 'max:50'],
            'key_skills' => ['nullable', 'string', 'max:500'],
            'industry' => ['nullable', 'string', 'max:255'],
            'additional_info' => ['nullable', 'string', 'max:1000'],
        ]);

        $response = $this->aiService->generateProfileSummary($cv, $params, Auth::user());

        return response()->json($response->toArray());
    }

    /**
     * Generate Career Objective variants with AI.
     */
    public function generateObjective(Request $request, Cv $cv): JsonResponse
    {
        $this->authorize('update', $cv);

        $params = $request->validate([
            'target_position' => ['nullable', 'string', 'max:255'],
            'key_skills' => ['nullable', 'string', 'max:500'],
            'tone' => ['nullable', 'string', 'max:50'],
        ]);

        $response = $this->aiService->generateCareerObjective($cv, $params, Auth::user());

        return response()->json($response->toArray());
    }

    /**
     * Rewrite Work Experience description or generate bullet points.
     */
    public function rewriteExperience(Request $request, Cv $cv): JsonResponse
    {
        $this->authorize('update', $cv);

        $params = $request->validate([
            'position' => ['nullable', 'string', 'max:255'],
            'company' => ['nullable', 'string', 'max:255'],
            'draft' => ['nullable', 'string', 'max:2000'],
            'mode' => ['nullable', 'in:improve,describe,concise,professional,bullets'],
        ]);

        $response = $this->aiService->rewriteExperience($cv, $params, Auth::user());

        return response()->json($response->toArray());
    }

    /**
     * Rewrite Project description or generate technical highlights.
     */
    public function rewriteProject(Request $request, Cv $cv): JsonResponse
    {
        $this->authorize('update', $cv);

        $params = $request->validate([
            'project_name' => ['nullable', 'string', 'max:255'],
            'technologies' => ['nullable', 'string', 'max:500'],
            'draft' => ['nullable', 'string', 'max:2000'],
            'mode' => ['nullable', 'in:describe,improve,concise,bullets'],
        ]);

        $response = $this->aiService->rewriteProject($cv, $params, Auth::user());

        return response()->json($response->toArray());
    }

    /**
     * Suggest relevant hard & soft skills based on role & document history.
     */
    public function suggestSkills(Request $request, Cv $cv): JsonResponse
    {
        $this->authorize('update', $cv);

        $params = $request->validate([
            'target_position' => ['nullable', 'string', 'max:255'],
            'category' => ['nullable', 'string', 'max:100'],
        ]);

        $response = $this->aiService->suggestSkills($cv, $params, Auth::user());

        return response()->json($response->toArray());
    }

    /**
     * Append selected AI skills directly to the CV.
     */
    public function appendSkills(Request $request, Cv $cv): JsonResponse
    {
        $this->authorize('update', $cv);

        $validated = $request->validate([
            'skills' => ['required', 'array', 'min:1'],
            'skills.*' => ['required', 'string', 'max:100'],
        ]);

        $existingNames = $cv->skills()->pluck('name')->map(fn($n) => strtolower(trim($n)))->toArray();
        $nextSort = ($cv->skills()->max('sort_order') ?? 0) + 1;
        $addedCount = 0;

        foreach ($validated['skills'] as $skillName) {
            $cleanName = trim($skillName);
            if (empty($cleanName) || in_array(strtolower($cleanName), $existingNames)) {
                continue;
            }

            CvSkill::create([
                'cv_id' => $cv->id,
                'name' => $cleanName,
                'level' => 'Advanced',
                'rating' => 80,
                'sort_order' => $nextSort++,
            ]);

            $existingNames[] = strtolower($cleanName);
            $addedCount++;
        }

        $cv->completion_percentage = $cv->calculateCompletion();
        $cv->save();

        return response()->json([
            'success' => true,
            'added_count' => $addedCount,
            'completion_percentage' => $cv->completion_percentage,
            'skills' => $cv->skills()->get(),
        ]);
    }

    /**
     * Improve arbitrary text (grammar, tone, ATS-compatibility, conciseness).
     */
    public function improveContent(Request $request, Cv $cv): JsonResponse
    {
        $this->authorize('update', $cv);

        $params = $request->validate([
            'text' => ['required', 'string', 'max:3000'],
            'tone' => ['nullable', 'in:improve,professional,concise,impactful,grammar,ats'],
        ]);

        $response = $this->aiService->improveContent($cv, $params, Auth::user());

        return response()->json($response->toArray());
    }

    /**
     * Generate structured Cover Letter content.
     */
    public function generateCoverLetter(Request $request, Cv $cv): JsonResponse
    {
        $this->authorize('update', $cv);

        $params = $request->validate([
            'recipient_name' => ['nullable', 'string', 'max:255'],
            'company_name' => ['nullable', 'string', 'max:255'],
            'job_title' => ['nullable', 'string', 'max:255'],
            'key_skills' => ['nullable', 'string', 'max:500'],
            'additional_notes' => ['nullable', 'string', 'max:1000'],
        ]);

        $response = $this->aiService->generateCoverLetter($cv, $params, Auth::user());

        return response()->json($response->toArray());
    }

    /**
     * Generate structured Motivation Letter content.
     */
    public function generateMotivationLetter(Request $request, Cv $cv): JsonResponse
    {
        $this->authorize('update', $cv);

        $params = $request->validate([
            'company_name' => ['nullable', 'string', 'max:255'], // Target institution
            'job_title' => ['nullable', 'string', 'max:255'], // Program / research area
            'goals' => ['nullable', 'string', 'max:1000'],
            'additional_notes' => ['nullable', 'string', 'max:1000'],
        ]);

        $response = $this->aiService->generateMotivationLetter($cv, $params, Auth::user());

        return response()->json($response->toArray());
    }
}
