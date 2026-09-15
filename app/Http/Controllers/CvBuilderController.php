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
use App\Models\DocumentLetterDetail;
use App\Services\TemplateService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class CvBuilderController extends Controller
{
    public function __construct(
        protected TemplateService $templateService
    ) {}

    /**
     * Display the CV Builder interface for a specific section.
     */
    public function show(Request $request, Cv $cv)
    {
        $this->authorize('update', $cv);

        $cv->load([
            'documentType',
            'personalInfo',
            'letterDetail',
            'experiences',
            'educations',
            'skills',
            'languages',
            'certifications',
            'projects',
            'awards',
            'references',
            'customSections',
            'template',
        ]);

        $isLetter = $cv->isLetter();

        $validSections = $isLetter ? [
            'personal-info',
            'letter-details',
            'letter-content',
            'letter-closing',
            'customization',
        ] : [
            'personal-info',
            'summary',
            'experience',
            'education',
            'skills',
            'languages',
            'certifications',
            'projects',
            'awards',
            'references',
            'custom',
            'customization',
        ];

        $activeSection = $request->get('section', 'personal-info');
        if (!in_array($activeSection, $validSections)) {
            $activeSection = 'personal-info';
        }

        $checklist = $cv->getSectionChecklist();

        // Optional editing item if passed in query string ?edit_id=...
        $editId = $request->get('edit_id');
        $editItem = null;
        if ($editId) {
            $editItem = match ($activeSection) {
                'experience' => $cv->experiences()->find($editId),
                'education' => $cv->educations()->find($editId),
                'skills' => $cv->skills()->find($editId),
                'languages' => $cv->languages()->find($editId),
                'certifications' => $cv->certifications()->find($editId),
                'projects' => $cv->projects()->find($editId),
                'awards' => $cv->awards()->find($editId),
                'references' => $cv->references()->find($editId),
                'custom' => $cv->customSections()->find($editId),
                default => null,
            };
        }

        // Prepare live preview data & compatible templates
        $cvData = $this->templateService->prepareCvData($cv);
        $templateModel = $cv->template ?? $this->templateService->findTemplate($cv->template_key);
        $templateView = $this->templateService->resolveViewPath($templateModel);
        $compatibleTemplates = $this->templateService->getActiveTemplatesForDocumentType($cv->document_type_id);

        return view('cvs.builder.layout', compact(
            'cv',
            'activeSection',
            'checklist',
            'editItem',
            'cvData',
            'templateModel',
            'templateView',
            'compatibleTemplates',
            'isLetter'
        ));
    }

    /**
     * Render and return the HTML preview snippet for live dynamic iframe/DOM updates.
     */
    public function renderPreview(Request $request, Cv $cv)
    {
        $this->authorize('update', $cv);

        $cvData = $this->templateService->prepareCvData($cv);
        $templateModel = $cv->template ?? $this->templateService->findTemplate($cv->template_key);
        $templateView = $this->templateService->resolveViewPath($templateModel);

        $renderedHtml = view($templateView, compact('cvData', 'cv'))->render();

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'html' => $renderedHtml,
                'template' => $templateModel?->name ?? 'Default',
                'template_key' => $templateModel?->key ?? $cv->template_key,
                'completion_percentage' => $cv->completion_percentage,
            ]);
        }

        return response($renderedHtml);
    }

    /**
     * Debounced background autosave endpoint for zero-friction editing.
     */
    public function autosave(Request $request, Cv $cv)
    {
        $this->authorize('update', $cv);

        $data = $request->all();

        // 1. Document Title
        if ($request->filled('title')) {
            $cv->title = $request->input('title');
        }

        // 2. Profile Summary
        if ($request->has('summary')) {
            $cv->summary = $request->input('summary');
        }

        // 3. Personal / Sender Info
        if ($request->has('personal_info')) {
            $infoData = $request->input('personal_info', []);
            $info = $cv->personalInfo ?: new CvPersonalInfo(['cv_id' => $cv->id]);
            $info->fill(array_intersect_key($infoData, array_flip([
                'full_name', 'job_title', 'email', 'phone', 'address', 'city', 'country', 'postal_code', 'website', 'linkedin', 'github', 'other_url'
            ])));
            $info->save();
        }

        // 4. Letter Details
        if ($request->has('letter_details')) {
            $letterData = $request->input('letter_details', []);
            $letter = $cv->letterDetail ?: new DocumentLetterDetail(['cv_id' => $cv->id]);
            $letter->fill(array_intersect_key($letterData, array_flip([
                'recipient_name', 'recipient_title', 'company_name', 'company_address', 'letter_date', 'subject', 'salutation', 'opening', 'body', 'call_to_action', 'closing', 'sender_signature'
            ])));
            $letter->save();
        }

        // 5. Settings / Customization
        if ($request->has('settings')) {
            $currentSettings = is_array($cv->settings) ? $cv->settings : [];
            $newSettings = array_merge($currentSettings, $request->input('settings', []));
            $cv->settings = $newSettings;
            if (isset($newSettings['accent_color'])) {
                $cv->primary_color = $newSettings['accent_color'];
            }
            if (isset($newSettings['font_family'])) {
                $cv->font_family = $newSettings['font_family'];
            }
        }

        $cv->completion_percentage = $cv->calculateCompletion();
        $cv->save();

        return response()->json([
            'success' => true,
            'message' => 'Autosaved successfully',
            'saved_at' => now()->format('h:i:s A'),
            'completion_percentage' => $cv->completion_percentage,
        ]);
    }

    /**
     * Save Letter Details for Cover Letter / Motivation Letter.
     */
    public function saveLetterDetails(Request $request, Cv $cv)
    {
        $this->authorize('update', $cv);

        $validated = $request->validate([
            'recipient_name' => ['nullable', 'string', 'max:255'],
            'recipient_title' => ['nullable', 'string', 'max:255'],
            'company_name' => ['nullable', 'string', 'max:255'],
            'company_address' => ['nullable', 'string'],
            'letter_date' => ['nullable', 'string', 'max:50'],
            'subject' => ['nullable', 'string', 'max:255'],
            'salutation' => ['nullable', 'string', 'max:255'],
            'opening' => ['nullable', 'string'],
            'body' => ['nullable', 'string'],
            'call_to_action' => ['nullable', 'string'],
            'closing' => ['nullable', 'string', 'max:100'],
            'sender_signature' => ['nullable', 'string', 'max:255'],
        ]);

        $letter = $cv->letterDetail ?: new DocumentLetterDetail(['cv_id' => $cv->id]);
        $letter->fill($validated);
        $letter->save();

        $cv->completion_percentage = $cv->calculateCompletion();
        $cv->save();

        $nextSection = $request->input('section', 'letter-details');

        return redirect()->route('cvs.builder.show', ['cv' => $cv, 'section' => $nextSection])
            ->with('success', 'Letter details updated successfully.');
    }

    /**
     * Save Design Customization Settings (colors, typography, spacing).
     */
    public function saveSettings(Request $request, Cv $cv)
    {
        $this->authorize('update', $cv);

        $validated = $request->validate([
            'accent_color' => ['nullable', 'string', 'max:30'],
            'font_family' => ['nullable', 'string', 'max:50'],
            'font_size' => ['nullable', 'in:small,normal,large'],
            'heading_size' => ['nullable', 'in:compact,normal,large'],
            'line_spacing' => ['nullable', 'in:compact,normal,relaxed'],
            'section_spacing' => ['nullable', 'in:compact,normal,spacious'],
            'photo_size' => ['nullable', 'in:small,medium,large,hidden'],
            'show_icons' => ['nullable', 'boolean'],
        ]);

        $currentSettings = is_array($cv->settings) ? $cv->settings : [];
        $merged = array_merge($currentSettings, $validated, [
            'show_icons' => $request->boolean('show_icons'),
        ]);

        $cv->settings = $merged;
        if (!empty($validated['accent_color'])) {
            $cv->primary_color = $validated['accent_color'];
        }
        if (!empty($validated['font_family'])) {
            $cv->font_family = $validated['font_family'];
        }
        $cv->save();

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Customization saved.',
                'settings' => $cv->settings,
            ]);
        }

        return redirect()->route('cvs.builder.show', ['cv' => $cv, 'section' => 'customization'])
            ->with('success', 'Design settings updated.');
    }

    /**
     * Save Personal Information section (with profile photo upload/removal).
     */
    public function savePersonalInfo(Request $request, Cv $cv)
    {
        $this->authorize('update', $cv);

        $validated = $request->validate([
            'full_name' => ['nullable', 'string', 'max:255'],
            'job_title' => ['nullable', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'],
            'address' => ['nullable', 'string', 'max:255'],
            'city' => ['nullable', 'string', 'max:100'],
            'country' => ['nullable', 'string', 'max:100'],
            'postal_code' => ['nullable', 'string', 'max:30'],
            'website' => ['nullable', 'string', 'max:255'],
            'linkedin' => ['nullable', 'string', 'max:255'],
            'github' => ['nullable', 'string', 'max:255'],
            'other_url' => ['nullable', 'string', 'max:255'],
            'photo' => ['nullable', 'image', 'mimes:jpeg,png,jpg,webp', 'max:2048'],
            'remove_photo' => ['nullable', 'boolean'],
        ]);

        $info = $cv->personalInfo ?: new CvPersonalInfo(['cv_id' => $cv->id]);

        // Handle Photo removal
        if ($request->boolean('remove_photo') && $info->photo_path) {
            Storage::disk('public')->delete($info->photo_path);
            $info->photo_path = null;
        }

        // Handle New Photo Upload
        if ($request->hasFile('photo')) {
            if ($info->photo_path) {
                Storage::disk('public')->delete($info->photo_path);
            }
            $path = $request->file('photo')->store('photos/' . $cv->id, 'public');
            $info->photo_path = $path;
        }

        $info->fill([
            'full_name' => $validated['full_name'] ?? null,
            'job_title' => $validated['job_title'] ?? null,
            'email' => $validated['email'] ?? null,
            'phone' => $validated['phone'] ?? null,
            'address' => $validated['address'] ?? null,
            'city' => $validated['city'] ?? null,
            'country' => $validated['country'] ?? null,
            'postal_code' => $validated['postal_code'] ?? null,
            'website' => $validated['website'] ?? null,
            'linkedin' => $validated['linkedin'] ?? null,
            'github' => $validated['github'] ?? null,
            'other_url' => $validated['other_url'] ?? null,
        ]);
        $info->save();

        $cv->completion_percentage = $cv->calculateCompletion();
        $cv->save();

        return redirect()->route('cvs.builder.show', ['cv' => $cv, 'section' => 'personal-info'])
            ->with('success', 'Personal Information updated successfully.');
    }

    /**
     * Save Profile Summary section.
     */
    public function saveSummary(Request $request, Cv $cv)
    {
        $this->authorize('update', $cv);

        $validated = $request->validate([
            'summary' => ['nullable', 'string', 'max:3000'],
        ]);

        $cv->summary = $validated['summary'] ?? null;
        $cv->completion_percentage = $cv->calculateCompletion();
        $cv->save();

        return redirect()->route('cvs.builder.show', ['cv' => $cv, 'section' => 'summary'])
            ->with('success', 'Profile Summary saved successfully.');
    }

    /**
     * Save/synchronize a batch of repeatable records for a section in one unified operation.
     */
    public function saveSectionBatch(Request $request, Cv $cv, string $section)
    {
        $this->authorize('update', $cv);

        $validSections = [
            'experience',
            'education',
            'skills',
            'languages',
            'certifications',
            'projects',
            'awards',
            'references',
            'custom',
        ];

        if (!in_array($section, $validSections)) {
            abort(404);
        }

        // 1. Process deletions
        $deletedIds = $request->input('deleted_ids', []);
        if (is_array($deletedIds) && count($deletedIds) > 0) {
            $deletedIds = array_filter(array_map('intval', $deletedIds));
            if (!empty($deletedIds)) {
                match ($section) {
                    'experience' => $cv->experiences()->whereIn('id', $deletedIds)->delete(),
                    'education' => $cv->educations()->whereIn('id', $deletedIds)->delete(),
                    'skills' => $cv->skills()->whereIn('id', $deletedIds)->delete(),
                    'languages' => $cv->languages()->whereIn('id', $deletedIds)->delete(),
                    'certifications' => $cv->certifications()->whereIn('id', $deletedIds)->delete(),
                    'projects' => $cv->projects()->whereIn('id', $deletedIds)->delete(),
                    'awards' => $cv->awards()->whereIn('id', $deletedIds)->delete(),
                    'references' => $cv->references()->whereIn('id', $deletedIds)->delete(),
                    'custom' => $cv->customSections()->whereIn('id', $deletedIds)->delete(),
                };
            }
        }

        // 2. Process items batch
        $rawItems = $request->input('items', []);
        if (!is_array($rawItems)) {
            $rawItems = [];
        }

        // Validate and persist items in a database transaction
        DB::transaction(function () use ($cv, $section, $rawItems, $request) {
            $order = 1;
            foreach ($rawItems as $key => $itemData) {
                if (!is_array($itemData)) continue;

                $itemId = isset($itemData['id']) && is_numeric($itemData['id']) ? (int)$itemData['id'] : null;

                // Skip completely empty new items
                if (!$itemId && $this->isItemDataCompletelyEmpty($section, $itemData)) {
                    continue;
                }

                $validated = $this->validateBatchItemData($section, $itemData, $request, (string)$key);

                match ($section) {
                    'experience' => $this->persistBatchExperience($cv, $itemId, $validated, $itemData, $order),
                    'education' => $this->persistBatchEducation($cv, $itemId, $validated, $itemData, $order),
                    'skills' => $this->persistBatchSkill($cv, $itemId, $validated, $itemData, $order),
                    'languages' => $this->persistBatchLanguage($cv, $itemId, $validated, $itemData, $order),
                    'certifications' => $this->persistBatchCertification($cv, $itemId, $validated, $itemData, $order),
                    'projects' => $this->persistBatchProject($cv, $itemId, $validated, $itemData, $order),
                    'awards' => $this->persistBatchAward($cv, $itemId, $validated, $itemData, $order),
                    'references' => $this->persistBatchReference($cv, $itemId, $validated, $itemData, $order),
                    'custom' => $this->persistBatchCustomSection($cv, $itemId, $validated, $itemData, $order),
                };

                $order++;
            }
        });

        $cv->completion_percentage = $cv->calculateCompletion();
        $cv->save();

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Changes saved successfully.',
                'completion_percentage' => $cv->completion_percentage,
            ]);
        }

        $sectionLabels = [
            'experience' => 'Work Experience',
            'education' => 'Education',
            'skills' => 'Skills',
            'languages' => 'Languages',
            'certifications' => 'Certifications',
            'projects' => 'Projects',
            'awards' => 'Awards',
            'references' => 'References',
            'custom' => 'Custom Section',
        ];
        $label = $sectionLabels[$section] ?? 'Section';

        return redirect()->route('cvs.builder.show', ['cv' => $cv, 'section' => $section])
            ->with('success', "{$label} changes saved successfully.");
    }

    protected function isItemDataCompletelyEmpty(string $section, array $data): bool
    {
        $nonEmptyKeys = match ($section) {
            'experience' => ['job_title', 'employer', 'description'],
            'education' => ['institution', 'degree', 'field_of_study', 'description'],
            'skills' => ['name', 'category'],
            'languages' => ['language'],
            'certifications' => ['name', 'issuing_organization', 'credential_id', 'description'],
            'projects' => ['title', 'role', 'description', 'technologies'],
            'awards' => ['title', 'issuer', 'description'],
            'references' => ['full_name', 'job_title', 'company', 'email', 'phone'],
            'custom' => ['section_title', 'title', 'subtitle', 'content'],
            default => ['name', 'title'],
        };

        foreach ($nonEmptyKeys as $key) {
            if (!empty(trim((string)($data[$key] ?? '')))) {
                return false;
            }
        }

        return true;
    }

    protected function validateBatchItemData(string $section, array $itemData, Request $request, string $key): array
    {
        $rules = match ($section) {
            'experience' => [
                'job_title' => ['required', 'string', 'max:255'],
                'employer' => ['required', 'string', 'max:255'],
                'city' => ['nullable', 'string', 'max:100'],
                'country' => ['nullable', 'string', 'max:100'],
                'start_date' => ['nullable', 'string', 'max:50'],
                'end_date' => ['nullable', 'string', 'max:50'],
                'description' => ['nullable', 'string'],
            ],
            'education' => [
                'institution' => ['required', 'string', 'max:255'],
                'degree' => ['required', 'string', 'max:255'],
                'field_of_study' => ['nullable', 'string', 'max:255'],
                'city' => ['nullable', 'string', 'max:100'],
                'country' => ['nullable', 'string', 'max:100'],
                'start_date' => ['nullable', 'string', 'max:50'],
                'end_date' => ['nullable', 'string', 'max:50'],
                'grade_or_gpa' => ['nullable', 'string', 'max:50'],
                'description' => ['nullable', 'string'],
            ],
            'skills' => [
                'name' => ['required', 'string', 'max:100'],
                'level' => ['required', 'string', 'max:30'],
                'rating' => ['nullable', 'integer', 'min:1', 'max:100'],
                'category' => ['nullable', 'string', 'max:50'],
            ],
            'languages' => [
                'language' => ['required', 'string', 'max:100'],
                'proficiency' => ['required', 'string', 'max:50'],
            ],
            'certifications' => [
                'name' => ['required', 'string', 'max:255'],
                'issuing_organization' => ['nullable', 'string', 'max:255'],
                'issue_date' => ['nullable', 'string', 'max:50'],
                'expiration_date' => ['nullable', 'string', 'max:50'],
                'credential_id' => ['nullable', 'string', 'max:100'],
                'credential_url' => ['nullable', 'string', 'max:255'],
                'description' => ['nullable', 'string'],
            ],
            'projects' => [
                'title' => ['required', 'string', 'max:255'],
                'role' => ['nullable', 'string', 'max:255'],
                'project_url' => ['nullable', 'string', 'max:255'],
                'technologies' => ['nullable', 'string', 'max:255'],
                'start_date' => ['nullable', 'string', 'max:50'],
                'end_date' => ['nullable', 'string', 'max:50'],
                'description' => ['nullable', 'string'],
            ],
            'awards' => [
                'title' => ['required', 'string', 'max:255'],
                'issuer' => ['nullable', 'string', 'max:255'],
                'issue_date' => ['nullable', 'string', 'max:50'],
                'description' => ['nullable', 'string'],
            ],
            'references' => [
                'full_name' => ['required', 'string', 'max:255'],
                'job_title' => ['nullable', 'string', 'max:255'],
                'company' => ['nullable', 'string', 'max:255'],
                'email' => ['nullable', 'string', 'max:255'],
                'phone' => ['nullable', 'string', 'max:50'],
                'relationship' => ['nullable', 'string', 'max:255'],
            ],
            'custom' => [
                'section_title' => ['required', 'string', 'max:255'],
                'title' => ['nullable', 'string', 'max:255'],
                'subtitle' => ['nullable', 'string', 'max:255'],
                'date_period' => ['nullable', 'string', 'max:100'],
                'content' => ['nullable', 'string'],
            ],
            default => [],
        };

        $validator = \Illuminate\Support\Facades\Validator::make($itemData, $rules);

        if ($validator->fails()) {
            throw new \Illuminate\Validation\ValidationException($validator);
        }

        return $validator->validated();
    }

    protected function persistBatchExperience(Cv $cv, ?int $id, array $validated, array $raw, int $order): void
    {
        $isCurrent = !empty($raw['is_current']);
        $payload = array_merge($validated, [
            'is_current' => $isCurrent,
            'sort_order' => $order,
        ]);

        if ($id) {
            $item = $cv->experiences()->where('id', $id)->first();
            if ($item) {
                $item->update($payload);
                return;
            }
        }

        $cv->experiences()->create($payload);
    }

    protected function persistBatchEducation(Cv $cv, ?int $id, array $validated, array $raw, int $order): void
    {
        $isCurrent = !empty($raw['is_current']);
        $payload = array_merge($validated, [
            'is_current' => $isCurrent,
            'sort_order' => $order,
        ]);

        if ($id) {
            $item = $cv->educations()->where('id', $id)->first();
            if ($item) {
                $item->update($payload);
                return;
            }
        }

        $cv->educations()->create($payload);
    }

    protected function persistBatchSkill(Cv $cv, ?int $id, array $validated, array $raw, int $order): void
    {
        $payload = array_merge($validated, [
            'rating' => isset($validated['rating']) ? (int)$validated['rating'] : 80,
            'sort_order' => $order,
        ]);

        if ($id) {
            $item = $cv->skills()->where('id', $id)->first();
            if ($item) {
                $item->update($payload);
                return;
            }
        }

        $cv->skills()->create($payload);
    }

    protected function persistBatchLanguage(Cv $cv, ?int $id, array $validated, array $raw, int $order): void
    {
        $payload = array_merge($validated, [
            'sort_order' => $order,
        ]);

        if ($id) {
            $item = $cv->languages()->where('id', $id)->first();
            if ($item) {
                $item->update($payload);
                return;
            }
        }

        $cv->languages()->create($payload);
    }

    protected function persistBatchCertification(Cv $cv, ?int $id, array $validated, array $raw, int $order): void
    {
        $payload = array_merge($validated, [
            'sort_order' => $order,
        ]);

        if ($id) {
            $item = $cv->certifications()->where('id', $id)->first();
            if ($item) {
                $item->update($payload);
                return;
            }
        }

        $cv->certifications()->create($payload);
    }

    protected function persistBatchProject(Cv $cv, ?int $id, array $validated, array $raw, int $order): void
    {
        $payload = array_merge($validated, [
            'sort_order' => $order,
        ]);

        if ($id) {
            $item = $cv->projects()->where('id', $id)->first();
            if ($item) {
                $item->update($payload);
                return;
            }
        }

        $cv->projects()->create($payload);
    }

    protected function persistBatchAward(Cv $cv, ?int $id, array $validated, array $raw, int $order): void
    {
        $payload = array_merge($validated, [
            'sort_order' => $order,
        ]);

        if ($id) {
            $item = $cv->awards()->where('id', $id)->first();
            if ($item) {
                $item->update($payload);
                return;
            }
        }

        $cv->awards()->create($payload);
    }

    protected function persistBatchReference(Cv $cv, ?int $id, array $validated, array $raw, int $order): void
    {
        $isHidden = !empty($raw['is_hidden']);
        $payload = array_merge($validated, [
            'is_hidden' => $isHidden,
            'sort_order' => $order,
        ]);

        if ($id) {
            $item = $cv->references()->where('id', $id)->first();
            if ($item) {
                $item->update($payload);
                return;
            }
        }

        $cv->references()->create($payload);
    }

    protected function persistBatchCustomSection(Cv $cv, ?int $id, array $validated, array $raw, int $order): void
    {
        $payload = array_merge($validated, [
            'sort_order' => $order,
        ]);

        if ($id) {
            $item = $cv->customSections()->where('id', $id)->first();
            if ($item) {
                $item->update($payload);
                return;
            }
        }

        $cv->customSections()->create($payload);
    }

    /**
     * Store a new repeatable record for a section.
     */
    public function storeItem(Request $request, Cv $cv, string $section)
    {
        $this->authorize('update', $cv);

        match ($section) {
            'experience' => $this->storeExperience($request, $cv),
            'education' => $this->storeEducation($request, $cv),
            'skills' => $this->storeSkill($request, $cv),
            'languages' => $this->storeLanguage($request, $cv),
            'certifications' => $this->storeCertification($request, $cv),
            'projects' => $this->storeProject($request, $cv),
            'awards' => $this->storeAward($request, $cv),
            'references' => $this->storeReference($request, $cv),
            'custom' => $this->storeCustomSection($request, $cv),
            default => abort(404),
        };

        $cv->completion_percentage = $cv->calculateCompletion();
        $cv->save();

        return redirect()->route('cvs.builder.show', ['cv' => $cv, 'section' => $section])
            ->with('success', 'Record added successfully.');
    }

    /**
     * Update an existing repeatable record.
     */
    public function updateItem(Request $request, Cv $cv, string $section, int $id)
    {
        $this->authorize('update', $cv);

        match ($section) {
            'experience' => $this->updateExperience($request, $cv, $id),
            'education' => $this->updateEducation($request, $cv, $id),
            'skills' => $this->updateSkill($request, $cv, $id),
            'languages' => $this->updateLanguage($request, $cv, $id),
            'certifications' => $this->updateCertification($request, $cv, $id),
            'projects' => $this->updateProject($request, $cv, $id),
            'awards' => $this->updateAward($request, $cv, $id),
            'references' => $this->updateReference($request, $cv, $id),
            'custom' => $this->updateCustomSection($request, $cv, $id),
            default => abort(404),
        };

        $cv->completion_percentage = $cv->calculateCompletion();
        $cv->save();

        return redirect()->route('cvs.builder.show', ['cv' => $cv, 'section' => $section])
            ->with('success', 'Record updated successfully.');
    }

    /**
     * Delete a repeatable record.
     */
    public function deleteItem(Request $request, Cv $cv, string $section, int $id)
    {
        $this->authorize('update', $cv);

        match ($section) {
            'experience' => $cv->experiences()->where('id', $id)->delete(),
            'education' => $cv->educations()->where('id', $id)->delete(),
            'skills' => $cv->skills()->where('id', $id)->delete(),
            'languages' => $cv->languages()->where('id', $id)->delete(),
            'certifications' => $cv->certifications()->where('id', $id)->delete(),
            'projects' => $cv->projects()->where('id', $id)->delete(),
            'awards' => $cv->awards()->where('id', $id)->delete(),
            'references' => $cv->references()->where('id', $id)->delete(),
            'custom' => $cv->customSections()->where('id', $id)->delete(),
            default => abort(404),
        };

        $cv->completion_percentage = $cv->calculateCompletion();
        $cv->save();

        return redirect()->route('cvs.builder.show', ['cv' => $cv, 'section' => $section])
            ->with('success', 'Record removed.');
    }

    /**
     * Reorder repeatable items (Move Up / Move Down).
     */
    public function reorderItems(Request $request, Cv $cv, string $section)
    {
        $this->authorize('update', $cv);

        $validated = $request->validate([
            'id' => ['required', 'integer'],
            'direction' => ['required', 'in:up,down'],
        ]);

        $query = match ($section) {
            'experience' => $cv->experiences(),
            'education' => $cv->educations(),
            'skills' => $cv->skills(),
            'languages' => $cv->languages(),
            'certifications' => $cv->certifications(),
            'projects' => $cv->projects(),
            'awards' => $cv->awards(),
            'references' => $cv->references(),
            'custom' => $cv->customSections(),
            default => abort(404),
        };

        $items = $query->orderBy('sort_order')->get();
        $currentIndex = $items->search(fn($item) => $item->id === (int)$validated['id']);

        if ($currentIndex !== false) {
            $targetIndex = ($validated['direction'] === 'up') ? $currentIndex - 1 : $currentIndex + 1;
            if ($targetIndex >= 0 && $targetIndex < $items->count()) {
                $currentItem = $items[$currentIndex];
                $targetItem = $items[$targetIndex];

                $tempOrder = $currentItem->sort_order ?: ($currentIndex + 1);
                $targetOrder = $targetItem->sort_order ?: ($targetIndex + 1);

                $currentItem->sort_order = $targetOrder;
                $targetItem->sort_order = $tempOrder;

                $currentItem->save();
                $targetItem->save();
            }
        }

        return redirect()->route('cvs.builder.show', ['cv' => $cv, 'section' => $section])
            ->with('success', 'Items reordered.');
    }

    /**
     * Toggle reference visibility (Hide/Show without deleting).
     */
    public function toggleReferenceVisibility(Request $request, Cv $cv, int $id)
    {
        $this->authorize('update', $cv);

        $reference = $cv->references()->where('id', $id)->firstOrFail();
        $reference->is_hidden = !$reference->is_hidden;
        $reference->save();

        $status = $reference->is_hidden ? 'hidden' : 'visible';
        return redirect()->route('cvs.builder.show', ['cv' => $cv, 'section' => 'references'])
            ->with('success', "Reference is now {$status}.");
    }

    // -------------------------------------------------------------
    // Section Storage Helpers
    // -------------------------------------------------------------

    protected function storeExperience(Request $request, Cv $cv): void
    {
        $validated = $request->validate([
            'job_title' => ['required', 'string', 'max:255'],
            'employer' => ['required', 'string', 'max:255'],
            'city' => ['nullable', 'string', 'max:100'],
            'country' => ['nullable', 'string', 'max:100'],
            'start_date' => ['nullable', 'string', 'max:50'],
            'end_date' => ['nullable', 'string', 'max:50'],
            'is_current' => ['nullable', 'boolean'],
            'description' => ['nullable', 'string'],
        ]);

        $maxOrder = $cv->experiences()->max('sort_order') ?? 0;
        $cv->experiences()->create(array_merge($validated, [
            'is_current' => $request->boolean('is_current'),
            'sort_order' => $maxOrder + 1,
        ]));
    }

    protected function updateExperience(Request $request, Cv $cv, int $id): void
    {
        $validated = $request->validate([
            'job_title' => ['required', 'string', 'max:255'],
            'employer' => ['required', 'string', 'max:255'],
            'city' => ['nullable', 'string', 'max:100'],
            'country' => ['nullable', 'string', 'max:100'],
            'start_date' => ['nullable', 'string', 'max:50'],
            'end_date' => ['nullable', 'string', 'max:50'],
            'is_current' => ['nullable', 'boolean'],
            'description' => ['nullable', 'string'],
        ]);

        $item = $cv->experiences()->where('id', $id)->firstOrFail();
        $item->update(array_merge($validated, [
            'is_current' => $request->boolean('is_current'),
        ]));
    }

    protected function storeEducation(Request $request, Cv $cv): void
    {
        $validated = $request->validate([
            'institution' => ['required', 'string', 'max:255'],
            'degree' => ['required', 'string', 'max:255'],
            'field_of_study' => ['nullable', 'string', 'max:255'],
            'city' => ['nullable', 'string', 'max:100'],
            'country' => ['nullable', 'string', 'max:100'],
            'start_date' => ['nullable', 'string', 'max:50'],
            'end_date' => ['nullable', 'string', 'max:50'],
            'is_current' => ['nullable', 'boolean'],
            'grade_or_gpa' => ['nullable', 'string', 'max:50'],
            'description' => ['nullable', 'string'],
        ]);

        $maxOrder = $cv->educations()->max('sort_order') ?? 0;
        $cv->educations()->create(array_merge($validated, [
            'is_current' => $request->boolean('is_current'),
            'sort_order' => $maxOrder + 1,
        ]));
    }

    protected function updateEducation(Request $request, Cv $cv, int $id): void
    {
        $validated = $request->validate([
            'institution' => ['required', 'string', 'max:255'],
            'degree' => ['required', 'string', 'max:255'],
            'field_of_study' => ['nullable', 'string', 'max:255'],
            'city' => ['nullable', 'string', 'max:100'],
            'country' => ['nullable', 'string', 'max:100'],
            'start_date' => ['nullable', 'string', 'max:50'],
            'end_date' => ['nullable', 'string', 'max:50'],
            'is_current' => ['nullable', 'boolean'],
            'grade_or_gpa' => ['nullable', 'string', 'max:50'],
            'description' => ['nullable', 'string'],
        ]);

        $item = $cv->educations()->where('id', $id)->firstOrFail();
        $item->update(array_merge($validated, [
            'is_current' => $request->boolean('is_current'),
        ]));
    }

    protected function storeSkill(Request $request, Cv $cv): void
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'level' => ['required', 'string', 'max:30'],
            'rating' => ['nullable', 'integer', 'min:1', 'max:100'],
            'category' => ['nullable', 'string', 'max:50'],
        ]);

        $maxOrder = $cv->skills()->max('sort_order') ?? 0;
        $cv->skills()->create(array_merge($validated, [
            'rating' => $validated['rating'] ?? 80,
            'sort_order' => $maxOrder + 1,
        ]));
    }

    protected function updateSkill(Request $request, Cv $cv, int $id): void
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'level' => ['required', 'string', 'max:30'],
            'rating' => ['nullable', 'integer', 'min:1', 'max:100'],
            'category' => ['nullable', 'string', 'max:50'],
        ]);

        $item = $cv->skills()->where('id', $id)->firstOrFail();
        $item->update($validated);
    }

    protected function storeLanguage(Request $request, Cv $cv): void
    {
        $validated = $request->validate([
            'language' => ['required', 'string', 'max:100'],
            'proficiency' => ['required', 'string', 'max:50'],
        ]);

        $maxOrder = $cv->languages()->max('sort_order') ?? 0;
        $cv->languages()->create(array_merge($validated, [
            'sort_order' => $maxOrder + 1,
        ]));
    }

    protected function updateLanguage(Request $request, Cv $cv, int $id): void
    {
        $validated = $request->validate([
            'language' => ['required', 'string', 'max:100'],
            'proficiency' => ['required', 'string', 'max:50'],
        ]);

        $item = $cv->languages()->where('id', $id)->firstOrFail();
        $item->update($validated);
    }

    protected function storeCertification(Request $request, Cv $cv): void
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'issuing_organization' => ['nullable', 'string', 'max:255'],
            'issue_date' => ['nullable', 'string', 'max:50'],
            'expiration_date' => ['nullable', 'string', 'max:50'],
            'credential_id' => ['nullable', 'string', 'max:100'],
            'credential_url' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
        ]);

        $maxOrder = $cv->certifications()->max('sort_order') ?? 0;
        $cv->certifications()->create(array_merge($validated, [
            'sort_order' => $maxOrder + 1,
        ]));
    }

    protected function updateCertification(Request $request, Cv $cv, int $id): void
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'issuing_organization' => ['nullable', 'string', 'max:255'],
            'issue_date' => ['nullable', 'string', 'max:50'],
            'expiration_date' => ['nullable', 'string', 'max:50'],
            'credential_id' => ['nullable', 'string', 'max:100'],
            'credential_url' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
        ]);

        $item = $cv->certifications()->where('id', $id)->firstOrFail();
        $item->update($validated);
    }

    protected function storeProject(Request $request, Cv $cv): void
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'role' => ['nullable', 'string', 'max:255'],
            'project_url' => ['nullable', 'string', 'max:255'],
            'technologies' => ['nullable', 'string', 'max:255'],
            'start_date' => ['nullable', 'string', 'max:50'],
            'end_date' => ['nullable', 'string', 'max:50'],
            'description' => ['nullable', 'string'],
        ]);

        $maxOrder = $cv->projects()->max('sort_order') ?? 0;
        $cv->projects()->create(array_merge($validated, [
            'sort_order' => $maxOrder + 1,
        ]));
    }

    protected function updateProject(Request $request, Cv $cv, int $id): void
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'role' => ['nullable', 'string', 'max:255'],
            'project_url' => ['nullable', 'string', 'max:255'],
            'technologies' => ['nullable', 'string', 'max:255'],
            'start_date' => ['nullable', 'string', 'max:50'],
            'end_date' => ['nullable', 'string', 'max:50'],
            'description' => ['nullable', 'string'],
        ]);

        $item = $cv->projects()->where('id', $id)->firstOrFail();
        $item->update($validated);
    }

    protected function storeAward(Request $request, Cv $cv): void
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'issuer' => ['nullable', 'string', 'max:255'],
            'issue_date' => ['nullable', 'string', 'max:50'],
            'description' => ['nullable', 'string'],
        ]);

        $maxOrder = $cv->awards()->max('sort_order') ?? 0;
        $cv->awards()->create(array_merge($validated, [
            'sort_order' => $maxOrder + 1,
        ]));
    }

    protected function updateAward(Request $request, Cv $cv, int $id): void
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'issuer' => ['nullable', 'string', 'max:255'],
            'issue_date' => ['nullable', 'string', 'max:50'],
            'description' => ['nullable', 'string'],
        ]);

        $item = $cv->awards()->where('id', $id)->firstOrFail();
        $item->update($validated);
    }

    protected function storeReference(Request $request, Cv $cv): void
    {
        $validated = $request->validate([
            'full_name' => ['required', 'string', 'max:255'],
            'job_title' => ['nullable', 'string', 'max:255'],
            'company' => ['nullable', 'string', 'max:255'],
            'email' => ['nullable', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'],
            'relationship' => ['nullable', 'string', 'max:255'],
            'is_hidden' => ['nullable', 'boolean'],
        ]);

        $maxOrder = $cv->references()->max('sort_order') ?? 0;
        $cv->references()->create(array_merge($validated, [
            'is_hidden' => $request->boolean('is_hidden'),
            'sort_order' => $maxOrder + 1,
        ]));
    }

    protected function updateReference(Request $request, Cv $cv, int $id): void
    {
        $validated = $request->validate([
            'full_name' => ['required', 'string', 'max:255'],
            'job_title' => ['nullable', 'string', 'max:255'],
            'company' => ['nullable', 'string', 'max:255'],
            'email' => ['nullable', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'],
            'relationship' => ['nullable', 'string', 'max:255'],
            'is_hidden' => ['nullable', 'boolean'],
        ]);

        $item = $cv->references()->where('id', $id)->firstOrFail();
        $item->update(array_merge($validated, [
            'is_hidden' => $request->boolean('is_hidden'),
        ]));
    }

    protected function storeCustomSection(Request $request, Cv $cv): void
    {
        $validated = $request->validate([
            'section_title' => ['required', 'string', 'max:255'],
            'title' => ['nullable', 'string', 'max:255'],
            'subtitle' => ['nullable', 'string', 'max:255'],
            'date_period' => ['nullable', 'string', 'max:100'],
            'content' => ['nullable', 'string'],
        ]);

        $maxOrder = $cv->customSections()->max('sort_order') ?? 0;
        $cv->customSections()->create(array_merge($validated, [
            'sort_order' => $maxOrder + 1,
        ]));
    }

    protected function updateCustomSection(Request $request, Cv $cv, int $id): void
    {
        $validated = $request->validate([
            'section_title' => ['required', 'string', 'max:255'],
            'title' => ['nullable', 'string', 'max:255'],
            'subtitle' => ['nullable', 'string', 'max:255'],
            'date_period' => ['nullable', 'string', 'max:100'],
            'content' => ['nullable', 'string'],
        ]);

        $item = $cv->customSections()->where('id', $id)->firstOrFail();
        $item->update($validated);
    }
}
