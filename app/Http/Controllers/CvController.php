<?php

namespace App\Http\Controllers;

use App\Models\Cv;
use App\Models\CvAward;
use App\Models\CvCertification;
use App\Models\CvCustomSection;
use App\Models\CvEducation;
use App\Models\CvExperience;
use App\Models\CvLanguage;
use App\Models\CvPersonalInfo;
use App\Models\CvProject;
use App\Models\CvReference;
use App\Models\CvSkill;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CvController extends Controller
{
    /**
     * Display a listing of the user's CVs.
     */
    public function index(Request $request)
    {
        $query = Auth::user()->cvs()->with('personalInfo');

        if ($request->filled('status') && in_array($request->status, ['draft', 'published'])) {
            $query->where('status', $request->status);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('summary', 'like', "%{$search}%");
            });
        }

        $cvs = $query->latest('updated_at')->paginate(9)->withQueryString();

        return view('cvs.index', compact('cvs'));
    }

    /**
     * Show the form for creating a new CV.
     */
    public function create()
    {
        return view('cvs.create');
    }

    /**
     * Store a newly created CV.
     * Newly created CVs are never forced to publish immediately.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'template_key' => ['nullable', 'string', 'max:50'],
            'job_title' => ['nullable', 'string', 'max:255'],
        ]);

        $cv = DB::transaction(function () use ($validated, $request) {
            $cv = Cv::create([
                'user_id' => Auth::id(),
                'title' => $validated['title'],
                'slug' => Str::slug($validated['title']) . '-' . Str::random(6),
                'status' => 'draft',
                'template_key' => $validated['template_key'] ?? 'classic',
                'completion_percentage' => 15,
            ]);

            // Create initial personal info record
            CvPersonalInfo::create([
                'cv_id' => $cv->id,
                'full_name' => Auth::user()->name,
                'email' => Auth::user()->email,
                'job_title' => $validated['job_title'] ?? null,
            ]);

            return $cv;
        });

        return redirect()->route('cvs.builder.show', ['cv' => $cv, 'section' => 'personal-info'])->with('success', 'CV draft created! Continue adding your details.');
    }

    /**
     * Display the specified CV (Preview Placeholder).
     */
    public function show(Cv $cv)
    {
        $this->authorize('view', $cv);

        $cv->load([
            'personalInfo',
            'experiences',
            'educations',
            'skills',
            'languages',
            'certifications',
            'projects',
            'awards',
            'references',
            'customSections',
        ]);

        return view('cvs.show', compact('cv'));
    }

    /**
     * Show the form for editing the CV (redirects to section builder).
     */
    public function edit(Cv $cv)
    {
        $this->authorize('update', $cv);

        return redirect()->route('cvs.builder.show', $cv);
    }

    /**
     * Update the specified CV in storage.
     */
    public function update(Request $request, Cv $cv)
    {
        $this->authorize('update', $cv);

        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'summary' => ['nullable', 'string'],
            'status' => ['nullable', 'in:draft,published'],
            'template_key' => ['nullable', 'string', 'max:50'],
            'primary_color' => ['nullable', 'string', 'max:30'],
            'font_family' => ['nullable', 'string', 'max:50'],
            
            // Personal Info
            'personal_info.full_name' => ['nullable', 'string', 'max:255'],
            'personal_info.job_title' => ['nullable', 'string', 'max:255'],
            'personal_info.email' => ['nullable', 'email', 'max:255'],
            'personal_info.phone' => ['nullable', 'string', 'max:50'],
            'personal_info.address' => ['nullable', 'string', 'max:255'],
            'personal_info.city' => ['nullable', 'string', 'max:100'],
            'personal_info.country' => ['nullable', 'string', 'max:100'],
            'personal_info.postal_code' => ['nullable', 'string', 'max:30'],
            'personal_info.website' => ['nullable', 'url', 'max:255'],
            'personal_info.linkedin' => ['nullable', 'string', 'max:255'],
            'personal_info.github' => ['nullable', 'string', 'max:255'],

            // Collections
            'experiences' => ['nullable', 'array'],
            'experiences.*.job_title' => ['nullable', 'string', 'max:255'],
            'experiences.*.employer' => ['nullable', 'string', 'max:255'],
            'experiences.*.city' => ['nullable', 'string', 'max:100'],
            'experiences.*.country' => ['nullable', 'string', 'max:100'],
            'experiences.*.start_date' => ['nullable', 'string', 'max:50'],
            'experiences.*.end_date' => ['nullable', 'string', 'max:50'],
            'experiences.*.is_current' => ['nullable', 'boolean'],
            'experiences.*.description' => ['nullable', 'string'],

            'educations' => ['nullable', 'array'],
            'educations.*.institution' => ['nullable', 'string', 'max:255'],
            'educations.*.degree' => ['nullable', 'string', 'max:255'],
            'educations.*.field_of_study' => ['nullable', 'string', 'max:255'],
            'educations.*.city' => ['nullable', 'string', 'max:100'],
            'educations.*.country' => ['nullable', 'string', 'max:100'],
            'educations.*.start_date' => ['nullable', 'string', 'max:50'],
            'educations.*.end_date' => ['nullable', 'string', 'max:50'],
            'educations.*.is_current' => ['nullable', 'boolean'],
            'educations.*.grade_or_gpa' => ['nullable', 'string', 'max:50'],
            'educations.*.description' => ['nullable', 'string'],

            'skills' => ['nullable', 'array'],
            'skills.*.name' => ['nullable', 'string', 'max:100'],
            'skills.*.level' => ['nullable', 'string', 'max:50'],
            'skills.*.category' => ['nullable', 'string', 'max:50'],

            'languages' => ['nullable', 'array'],
            'languages.*.language' => ['nullable', 'string', 'max:100'],
            'languages.*.proficiency' => ['nullable', 'string', 'max:50'],

            'projects' => ['nullable', 'array'],
            'projects.*.title' => ['nullable', 'string', 'max:255'],
            'projects.*.role' => ['nullable', 'string', 'max:255'],
            'projects.*.project_url' => ['nullable', 'string', 'max:255'],
            'projects.*.start_date' => ['nullable', 'string', 'max:50'],
            'projects.*.end_date' => ['nullable', 'string', 'max:50'],
            'projects.*.description' => ['nullable', 'string'],

            'certifications' => ['nullable', 'array'],
            'certifications.*.name' => ['nullable', 'string', 'max:255'],
            'certifications.*.issuing_organization' => ['nullable', 'string', 'max:255'],
            'certifications.*.issue_date' => ['nullable', 'string', 'max:50'],
            'certifications.*.credential_id' => ['nullable', 'string', 'max:100'],

            'awards' => ['nullable', 'array'],
            'awards.*.title' => ['nullable', 'string', 'max:255'],
            'awards.*.issuer' => ['nullable', 'string', 'max:255'],
            'awards.*.issue_date' => ['nullable', 'string', 'max:50'],
            'awards.*.description' => ['nullable', 'string'],

            'references' => ['nullable', 'array'],
            'references.*.full_name' => ['nullable', 'string', 'max:255'],
            'references.*.job_title' => ['nullable', 'string', 'max:255'],
            'references.*.company' => ['nullable', 'string', 'max:255'],
            'references.*.email' => ['nullable', 'string', 'max:255'],
            'references.*.phone' => ['nullable', 'string', 'max:50'],
        ]);

        DB::transaction(function () use ($validated, $request, $cv) {
            // Determine action status
            $action = $request->input('action', 'save_draft');
            $status = ($action === 'publish') ? 'published' : ($validated['status'] ?? $cv->status);

            // Update main CV
            $cv->update([
                'title' => $validated['title'],
                'summary' => $validated['summary'] ?? null,
                'status' => $status,
                'template_key' => $validated['template_key'] ?? $cv->template_key,
                'primary_color' => $validated['primary_color'] ?? $cv->primary_color,
                'font_family' => $validated['font_family'] ?? $cv->font_family,
            ]);

            // Update Personal Info
            if (isset($validated['personal_info'])) {
                $cv->personalInfo()->updateOrCreate(
                    ['cv_id' => $cv->id],
                    $validated['personal_info']
                );
            }

            // Sync Experiences
            $cv->experiences()->delete();
            if (!empty($validated['experiences'])) {
                foreach ($validated['experiences'] as $index => $item) {
                    if (!empty($item['job_title']) || !empty($item['employer'])) {
                        $cv->experiences()->create(array_merge($item, [
                            'sort_order' => $index + 1,
                            'is_current' => !empty($item['is_current']),
                        ]));
                    }
                }
            }

            // Sync Educations
            $cv->educations()->delete();
            if (!empty($validated['educations'])) {
                foreach ($validated['educations'] as $index => $item) {
                    if (!empty($item['institution']) || !empty($item['degree'])) {
                        $cv->educations()->create(array_merge($item, [
                            'sort_order' => $index + 1,
                            'is_current' => !empty($item['is_current']),
                        ]));
                    }
                }
            }

            // Sync Skills
            $cv->skills()->delete();
            if (!empty($validated['skills'])) {
                foreach ($validated['skills'] as $index => $item) {
                    if (!empty($item['name'])) {
                        $cv->skills()->create(array_merge($item, [
                            'sort_order' => $index + 1,
                        ]));
                    }
                }
            }

            // Sync Languages
            $cv->languages()->delete();
            if (!empty($validated['languages'])) {
                foreach ($validated['languages'] as $index => $item) {
                    if (!empty($item['language'])) {
                        $cv->languages()->create(array_merge($item, [
                            'sort_order' => $index + 1,
                        ]));
                    }
                }
            }

            // Sync Projects
            $cv->projects()->delete();
            if (!empty($validated['projects'])) {
                foreach ($validated['projects'] as $index => $item) {
                    if (!empty($item['title'])) {
                        $cv->projects()->create(array_merge($item, [
                            'sort_order' => $index + 1,
                        ]));
                    }
                }
            }

            // Sync Certifications
            $cv->certifications()->delete();
            if (!empty($validated['certifications'])) {
                foreach ($validated['certifications'] as $index => $item) {
                    if (!empty($item['name'])) {
                        $cv->certifications()->create(array_merge($item, [
                            'sort_order' => $index + 1,
                        ]));
                    }
                }
            }

            // Sync Awards
            $cv->awards()->delete();
            if (!empty($validated['awards'])) {
                foreach ($validated['awards'] as $index => $item) {
                    if (!empty($item['title'])) {
                        $cv->awards()->create(array_merge($item, [
                            'sort_order' => $index + 1,
                        ]));
                    }
                }
            }

            // Sync References
            $cv->references()->delete();
            if (!empty($validated['references'])) {
                foreach ($validated['references'] as $index => $item) {
                    if (!empty($item['full_name'])) {
                        $cv->references()->create(array_merge($item, [
                            'sort_order' => $index + 1,
                        ]));
                    }
                }
            }

            // Update completion percentage
            $cv->completion_percentage = $cv->calculateCompletion();
            $cv->save();
        });

        $message = ($cv->status === 'published')
            ? 'CV published successfully!'
            : 'Draft saved successfully.';

        if ($request->input('redirect_to') === 'show') {
            return redirect()->route('cvs.show', $cv)->with('success', $message);
        }

        return redirect()->route('cvs.edit', $cv)->with('success', $message);
    }

    /**
     * Duplicate an existing CV.
     */
    public function duplicate(Cv $cv)
    {
        $this->authorize('duplicate', $cv);

        $newCv = DB::transaction(function () use ($cv) {
            $newCv = $cv->replicate([
                'slug',
                'created_at',
                'updated_at',
            ]);
            $newCv->title = $cv->title . ' (Copy)';
            $newCv->slug = Str::slug($newCv->title) . '-' . Str::random(6);
            $newCv->status = 'draft';
            $newCv->user_id = Auth::id();
            $newCv->save();

            // Duplicate Personal Info
            if ($cv->personalInfo) {
                $info = $cv->personalInfo->replicate(['cv_id', 'created_at', 'updated_at']);
                $info->cv_id = $newCv->id;
                $info->save();
            }

            // Duplicate Experiences
            foreach ($cv->experiences as $exp) {
                $newExp = $exp->replicate(['cv_id', 'created_at', 'updated_at']);
                $newExp->cv_id = $newCv->id;
                $newExp->save();
            }

            // Duplicate Educations
            foreach ($cv->educations as $edu) {
                $newEdu = $edu->replicate(['cv_id', 'created_at', 'updated_at']);
                $newEdu->cv_id = $newCv->id;
                $newEdu->save();
            }

            // Duplicate Skills
            foreach ($cv->skills as $skill) {
                $newSkill = $skill->replicate(['cv_id', 'created_at', 'updated_at']);
                $newSkill->cv_id = $newCv->id;
                $newSkill->save();
            }

            // Duplicate Languages
            foreach ($cv->languages as $lang) {
                $newLang = $lang->replicate(['cv_id', 'created_at', 'updated_at']);
                $newLang->cv_id = $newCv->id;
                $newLang->save();
            }

            // Duplicate Projects
            foreach ($cv->projects as $proj) {
                $newProj = $proj->replicate(['cv_id', 'created_at', 'updated_at']);
                $newProj->cv_id = $newCv->id;
                $newProj->save();
            }

            // Duplicate Certifications
            foreach ($cv->certifications as $cert) {
                $newCert = $cert->replicate(['cv_id', 'created_at', 'updated_at']);
                $newCert->cv_id = $newCv->id;
                $newCert->save();
            }

            // Duplicate Awards
            foreach ($cv->awards as $award) {
                $newAward = $award->replicate(['cv_id', 'created_at', 'updated_at']);
                $newAward->cv_id = $newCv->id;
                $newAward->save();
            }

            // Duplicate References
            foreach ($cv->references as $ref) {
                $newRef = $ref->replicate(['cv_id', 'created_at', 'updated_at']);
                $newRef->cv_id = $newCv->id;
                $newRef->save();
            }

            return $newCv;
        });

        return redirect()->route('cvs.index')->with('success', "CV '{$newCv->title}' duplicated successfully.");
    }

    /**
     * Quick status toggle (Draft <-> Published).
     */
    public function toggleStatus(Cv $cv)
    {
        $this->authorize('update', $cv);

        $cv->status = ($cv->status === 'published') ? 'draft' : 'published';
        $cv->save();

        $statusLabel = ucfirst($cv->status);
        return back()->with('success', "CV status changed to {$statusLabel}.");
    }

    /**
     * Remove the specified CV from storage.
     */
    public function destroy(Cv $cv)
    {
        $this->authorize('delete', $cv);

        $title = $cv->title;
        $cv->delete();

        return redirect()->route('cvs.index')->with('success', "CV '{$title}' has been deleted.");
    }
}
