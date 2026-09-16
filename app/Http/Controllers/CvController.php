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
use App\Models\CvTemplate;
use App\Models\DocumentLetterDetail;
use App\Models\DocumentType;
use App\Services\Pdf\PdfGeneratorService;
use App\Services\TemplateService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CvController extends Controller
{
    public function __construct(
        protected TemplateService $templateService,
        protected PdfGeneratorService $pdfService
    ) {}


    /**
     * Display a listing of the user's career documents.
     */
    public function index(Request $request)
    {
        $query = Auth::user()->cvs()->with(['documentType', 'personalInfo', 'letterDetail', 'template']);

        if ($request->filled('status') && in_array($request->status, ['draft', 'published'])) {
            $query->where('status', $request->status);
        }

        if ($request->filled('document_type')) {
            $docTypeSlug = $request->document_type;
            $query->whereHas('documentType', function ($q) use ($docTypeSlug) {
                $q->where('slug', $docTypeSlug);
            });
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('summary', 'like', "%{$search}%");
            });
        }

        $cvs = $query->latest('updated_at')->paginate(9)->withQueryString();
        $documentTypes = DocumentType::where('is_active', true)->orderBy('sort_order')->get();

        return view('cvs.index', compact('cvs', 'documentTypes'));
    }

    /**
     * Show the form for creating a new career document (Multi-Step Creation Wizard).
     */
    public function create(Request $request)
    {
        $documentTypes = DocumentType::where('is_active', true)->with('activeTemplates')->orderBy('sort_order')->get();
        $categories = $this->templateService->getCategoriesWithTemplates();
        $allTemplates = $this->templateService->getActiveTemplates();

        // Selected Document Type resolution
        $selectedType = null;
        if ($request->filled('type')) {
            $selectedType = DocumentType::where('slug', $request->type)->orWhere('id', $request->type)->first();
        }
        if (!$selectedType) {
            $selectedType = $documentTypes->first();
        }

        // Selected Template resolution
        $compatibleTemplates = $selectedType 
            ? $this->templateService->getActiveTemplatesForDocumentType($selectedType->id)
            : $allTemplates;

        $selectedTemplate = null;
        if ($request->filled('template_id')) {
            $selectedTemplate = $this->templateService->findTemplate($request->template_id);
        } elseif ($request->filled('template')) {
            $selectedTemplate = $this->templateService->findTemplate($request->template);
        }

        if (!$selectedTemplate) {
            $selectedTemplate = $this->templateService->getDefaultTemplate($selectedType);
        }

        return view('cvs.create', compact(
            'documentTypes',
            'categories',
            'allTemplates',
            'compatibleTemplates',
            'selectedType',
            'selectedTemplate'
        ));
    }

    /**
     * Store a newly created career document.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'document_type_id' => ['nullable', 'exists:document_types,id'],
            'template_id' => ['nullable', 'exists:cv_templates,id'],
            'template_key' => ['nullable', 'string', 'max:50'],
            'job_title' => ['nullable', 'string', 'max:255'],
            'company_or_institution' => ['nullable', 'string', 'max:255'],
        ]);

        $docType = null;
        if (!empty($validated['document_type_id'])) {
            $docType = DocumentType::find($validated['document_type_id']);
        }
        if (!$docType) {
            $docType = DocumentType::where('slug', 'standard-cv')->first() ?? DocumentType::first();
        }

        $template = null;
        if (!empty($validated['template_id'])) {
            $template = CvTemplate::find($validated['template_id']);
        } elseif (!empty($validated['template_key'])) {
            $template = $this->templateService->findTemplate($validated['template_key']);
        }

        if (!$template) {
            $template = $this->templateService->getDefaultTemplate($docType);
        }

        $cv = DB::transaction(function () use ($validated, $docType, $template) {
            $cv = Cv::create([
                'user_id' => Auth::id(),
                'document_type_id' => $docType?->id,
                'template_id' => $template?->id,
                'title' => $validated['title'],
                'slug' => Str::slug($validated['title']) . '-' . Str::random(6),
                'status' => 'draft',
                'template_key' => $template?->key ?? 'classic-executive',
                'completion_percentage' => 15,
                'settings' => [
                    'font_family' => ($docType?->slug === 'motivation-letter') ? 'Georgia' : 'Inter',
                    'accent_color' => ($docType?->slug === 'motivation-letter') ? '#8c1d40' : '#1e293b',
                    'font_size' => 'normal',
                    'heading_size' => 'normal',
                    'line_spacing' => 'normal',
                    'section_spacing' => 'normal',
                    'photo_size' => 'medium',
                    'show_icons' => true,
                ],
            ]);

            // Create initial personal info record
            CvPersonalInfo::create([
                'cv_id' => $cv->id,
                'full_name' => Auth::user()->name,
                'email' => Auth::user()->email,
                'job_title' => $validated['job_title'] ?? null,
            ]);

            // If Letter-based document type, initialize structured letter details
            if ($docType && $docType->isLetterBased()) {
                $isMotivation = $docType->slug === 'motivation-letter';
                DocumentLetterDetail::create([
                    'cv_id' => $cv->id,
                    'recipient_name' => $isMotivation ? 'Graduate Admissions Committee' : 'Hiring Team / Hiring Manager',
                    'recipient_title' => $isMotivation ? 'Department Admissions' : 'Director of Talent',
                    'company_name' => $validated['company_or_institution'] ?? ($isMotivation ? 'Target University / Institution' : 'Target Company'),
                    'letter_date' => date('F j, Y'),
                    'subject' => $isMotivation 
                        ? 'Statement of Purpose – ' . ($validated['job_title'] ?? 'Graduate Program Application')
                        : 'Application for ' . ($validated['job_title'] ?? 'Professional Position'),
                    'salutation' => $isMotivation ? 'Dear Members of the Admissions Committee,' : 'Dear Hiring Manager,',
                    'closing' => $isMotivation ? 'Respectfully submitted,' : 'Sincerely,',
                    'sender_signature' => Auth::user()->name,
                ]);
            }

            return $cv;
        });

        $initialSection = $cv->isLetter() ? 'letter-details' : 'personal-info';
        $docName = $docType?->name ?? 'Document';

        return redirect()->route('cvs.builder.show', ['cv' => $cv, 'section' => $initialSection])
            ->with('success', "'{$docName}' created! Continue adding your details.");
    }

    /**
     * Display the specified document using its selected template.
     */
    public function show(Cv $cv)
    {
        $this->authorize('view', $cv);

        $cvData = $this->templateService->prepareCvData($cv);
        $templateModel = $cv->template ?? $this->templateService->findTemplate($cv->template_key);
        $templateView = $this->templateService->resolveViewPath($templateModel);
        $activeTemplates = $this->templateService->getActiveTemplatesForDocumentType($cv->document_type_id);

        return view('cvs.show', compact('cv', 'cvData', 'templateModel', 'templateView', 'activeTemplates'));
    }

    /**
     * Switch template for an existing document on the fly without losing data.
     */
    public function switchTemplate(Request $request, Cv $cv)
    {
        $this->authorize('update', $cv);

        $validated = $request->validate([
            'template_id' => ['required', 'exists:cv_templates,id'],
        ]);

        $template = CvTemplate::findOrFail($validated['template_id']);

        $cv->update([
            'template_id' => $template->id,
            'template_key' => $template->key,
        ]);

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => "Template switched to {$template->name}.",
                'template_name' => $template->name,
            ]);
        }

        return back()->with('success', "Template switched to '{$template->name}'. All your document content is preserved.");
    }

    /**
     * Show the form for editing the document (redirects to section builder).
     */
    public function edit(Cv $cv)
    {
        $this->authorize('update', $cv);

        return redirect()->route('cvs.builder.show', $cv);
    }

    /**
     * Update the specified document in storage.
     */
    public function update(Request $request, Cv $cv)
    {
        $this->authorize('update', $cv);

        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'summary' => ['nullable', 'string'],
            'status' => ['nullable', 'in:draft,published'],
            'template_id' => ['nullable', 'exists:cv_templates,id'],
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

            // Letter Details
            'letter_detail.recipient_name' => ['nullable', 'string', 'max:255'],
            'letter_detail.recipient_title' => ['nullable', 'string', 'max:255'],
            'letter_detail.company_name' => ['nullable', 'string', 'max:255'],
            'letter_detail.company_address' => ['nullable', 'string'],
            'letter_detail.letter_date' => ['nullable', 'string', 'max:50'],
            'letter_detail.subject' => ['nullable', 'string', 'max:255'],
            'letter_detail.salutation' => ['nullable', 'string', 'max:255'],
            'letter_detail.opening' => ['nullable', 'string'],
            'letter_detail.body' => ['nullable', 'string'],
            'letter_detail.call_to_action' => ['nullable', 'string'],
            'letter_detail.closing' => ['nullable', 'string', 'max:100'],
            'letter_detail.sender_signature' => ['nullable', 'string', 'max:255'],

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
            $action = $request->input('action', 'save_draft');
            $status = ($action === 'publish') ? 'published' : ($validated['status'] ?? $cv->status);

            // Update main record
            $cv->update([
                'title' => $validated['title'],
                'summary' => $validated['summary'] ?? null,
                'status' => $status,
                'template_id' => $validated['template_id'] ?? $cv->template_id,
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

            // Update Letter Details if present
            if (isset($validated['letter_detail'])) {
                $cv->letterDetail()->updateOrCreate(
                    ['cv_id' => $cv->id],
                    $validated['letter_detail']
                );
            }

            // Sync Experiences
            if (isset($validated['experiences'])) {
                $cv->experiences()->delete();
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
            if (isset($validated['educations'])) {
                $cv->educations()->delete();
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
            if (isset($validated['skills'])) {
                $cv->skills()->delete();
                foreach ($validated['skills'] as $index => $item) {
                    if (!empty($item['name'])) {
                        $cv->skills()->create(array_merge($item, [
                            'sort_order' => $index + 1,
                        ]));
                    }
                }
            }

            // Sync Languages
            if (isset($validated['languages'])) {
                $cv->languages()->delete();
                foreach ($validated['languages'] as $index => $item) {
                    if (!empty($item['language'])) {
                        $cv->languages()->create(array_merge($item, [
                            'sort_order' => $index + 1,
                        ]));
                    }
                }
            }

            // Sync Projects
            if (isset($validated['projects'])) {
                $cv->projects()->delete();
                foreach ($validated['projects'] as $index => $item) {
                    if (!empty($item['title'])) {
                        $cv->projects()->create(array_merge($item, [
                            'sort_order' => $index + 1,
                        ]));
                    }
                }
            }

            // Sync Certifications
            if (isset($validated['certifications'])) {
                $cv->certifications()->delete();
                foreach ($validated['certifications'] as $index => $item) {
                    if (!empty($item['name'])) {
                        $cv->certifications()->create(array_merge($item, [
                            'sort_order' => $index + 1,
                        ]));
                    }
                }
            }

            // Sync Awards
            if (isset($validated['awards'])) {
                $cv->awards()->delete();
                foreach ($validated['awards'] as $index => $item) {
                    if (!empty($item['title'])) {
                        $cv->awards()->create(array_merge($item, [
                            'sort_order' => $index + 1,
                        ]));
                    }
                }
            }

            // Sync References
            if (isset($validated['references'])) {
                $cv->references()->delete();
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
            ? 'Document published successfully!'
            : 'Draft saved successfully.';

        if ($request->input('redirect_to') === 'show') {
            return redirect()->route('cvs.show', $cv)->with('success', $message);
        }

        return redirect()->route('cvs.edit', $cv)->with('success', $message);
    }

    /**
     * Duplicate an existing document with all associated data.
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
            $newCv->document_type_id = $cv->document_type_id;
            $newCv->template_id = $cv->template_id;
            $newCv->template_key = $cv->template_key;
            $newCv->settings = $cv->settings;
            $newCv->save();

            // Duplicate Personal Info
            if ($cv->personalInfo) {
                $info = $cv->personalInfo->replicate(['cv_id', 'created_at', 'updated_at']);
                $info->cv_id = $newCv->id;
                $info->save();
            }

            // Duplicate Letter Details
            if ($cv->letterDetail) {
                $letter = $cv->letterDetail->replicate(['cv_id', 'created_at', 'updated_at']);
                $letter->cv_id = $newCv->id;
                $letter->save();
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

            // Duplicate Custom Sections
            foreach ($cv->customSections as $cust) {
                $newCust = $cust->replicate(['cv_id', 'created_at', 'updated_at']);
                $newCust->cv_id = $newCv->id;
                $newCust->save();
            }

            return $newCv;
        });

        return redirect()->route('cvs.index')->with('success', "Document '{$newCv->title}' duplicated successfully.");
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
        return back()->with('success', "Document status changed to {$statusLabel}.");
    }

    /**
     * Remove the specified document from storage.
     */
    public function destroy(Cv $cv)
    {
        $this->authorize('delete', $cv);

        $title = $cv->title;
        $cv->delete();

        return redirect()->route('cvs.index')->with('success', "Document '{$title}' has been deleted.");
    }

    /**
     * Download the rendered PDF for the document.
     */
    public function downloadPdf(Request $request, Cv $cv)
    {
        $this->authorize('view', $cv);

        // Server-side Premium Entitlement Verification
        if ($cv->isPremium() && !Auth::user()->hasAccessToTemplate($cv->template)) {
            return redirect()->route('checkout.template', $cv->template)
                ->with('error', "This document uses the premium template '{$cv->template->name}'. Please unlock it to download PDF exports.");
        }

        return $this->pdfService->generate($cv, [
            'download' => true,
        ]);
    }

    /**
     * Preview the rendered PDF directly inline in the browser.
     */
    public function previewPdf(Request $request, Cv $cv)
    {
        $this->authorize('view', $cv);

        return $this->pdfService->generate($cv, [
            'download' => false,
        ]);
    }
}

