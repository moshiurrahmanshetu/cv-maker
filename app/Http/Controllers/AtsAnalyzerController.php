<?php

namespace App\Http\Controllers;

use App\Models\Cv;
use App\Services\Ats\AtsAnalyzerService;
use Illuminate\Http\Request;

class AtsAnalyzerController extends Controller
{
    public function __construct(
        protected AtsAnalyzerService $atsAnalyzerService
    ) {}

    /**
     * Display ATS Analysis and Job Matching Dashboard for a document.
     */
    public function show(Request $request, Cv $cv)
    {
        $this->authorize('view', $cv);

        $analysis = $cv->latestAtsAnalysis;

        // Automatically run initial analysis if no analysis exists yet
        if (!$analysis) {
            $analysis = $this->atsAnalyzerService->analyze($cv);
        }

        return view('cvs.ats.show', compact('cv', 'analysis'));
    }

    /**
     * Re-run ATS Analysis on the current document state.
     */
    public function analyze(Request $request, Cv $cv)
    {
        $this->authorize('update', $cv);

        $existingJobDescription = $request->input('job_description', $cv->latestAtsAnalysis?->job_description);

        $analysis = $this->atsAnalyzerService->analyze($cv, $existingJobDescription);

        if ($request->expectsJson()) {
            return response()->json([
                'status' => 'success',
                'message' => 'ATS Analysis updated successfully.',
                'analysis' => $analysis,
            ]);
        }

        return redirect()->route('cvs.ats.show', $cv)
            ->with('success', 'ATS Analysis updated with latest document changes.');
    }

    /**
     * Match document against a pasted Job Description.
     */
    public function matchJob(Request $request, Cv $cv)
    {
        $this->authorize('update', $cv);

        $validated = $request->validate([
            'job_description' => ['required', 'string', 'min:20', 'max:25000'],
        ], [
            'job_description.min' => 'Please paste a meaningful job description of at least 20 characters.',
        ]);

        $analysis = $this->atsAnalyzerService->analyze($cv, $validated['job_description']);

        if ($request->expectsJson()) {
            return response()->json([
                'status' => 'success',
                'message' => 'Job description analyzed and keywords matched.',
                'analysis' => $analysis,
            ]);
        }

        return redirect()->route('cvs.ats.show', $cv)
            ->with('success', 'Job description analyzed! See matched skills, missing keywords, and match score below.');
    }

    /**
     * Clear target job description and reset to general ATS analysis.
     */
    public function clearJob(Request $request, Cv $cv)
    {
        $this->authorize('update', $cv);

        $analysis = $this->atsAnalyzerService->analyze($cv, null);

        if ($request->expectsJson()) {
            return response()->json([
                'status' => 'success',
                'message' => 'Target job description cleared.',
                'analysis' => $analysis,
            ]);
        }

        return redirect()->route('cvs.ats.show', $cv)
            ->with('success', 'Job description removed. Displaying base ATS analysis.');
    }
}
