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
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class CvBuilderController extends Controller
{
    /**
     * Display the CV Builder interface for a specific section.
     */
    public function show(Request $request, Cv $cv)
    {
        $this->authorize('update', $cv);

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

        $activeSection = $request->get('section', 'personal-info');
        $validSections = [
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
        ];

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

        return view('cvs.builder.layout', compact('cv', 'activeSection', 'checklist', 'editItem'));
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
