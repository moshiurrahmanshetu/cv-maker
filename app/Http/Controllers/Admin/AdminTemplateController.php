<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CvTemplate;
use App\Models\TemplateCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class AdminTemplateController extends Controller
{
    /**
     * Display a listing of templates with filters and usage counts.
     */
    public function index(Request $request)
    {
        $query = CvTemplate::with('category')->withCount('cvs');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('key', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        if ($request->filled('status')) {
            if ($request->status === 'active') {
                $query->where('is_active', true);
            } elseif ($request->status === 'inactive') {
                $query->where('is_active', false);
            }
        }

        if ($request->filled('type')) {
            if ($request->type === 'premium') {
                $query->where('is_premium', true);
            } elseif ($request->type === 'free') {
                $query->where('is_premium', false);
            }
        }

        $templates = $query->orderBy('sort_order')->paginate(10)->withQueryString();
        $categories = TemplateCategory::orderBy('sort_order')->get();

        return view('admin.templates.index', compact('templates', 'categories'));
    }

    /**
     * Show the form for creating a new template.
     */
    public function create()
    {
        $categories = TemplateCategory::where('is_active', true)->orderBy('sort_order')->get();
        return view('admin.templates.create', compact('categories'));
    }

    /**
     * Store a newly created template in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'category_id' => ['required', 'exists:template_categories,id'],
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255', 'unique:cv_templates,slug'],
            'key' => ['required', 'string', 'max:50', 'unique:cv_templates,key', 'regex:/^[a-zA-Z0-9_\-]+$/'],
            'description' => ['nullable', 'string'],
            'preview_image' => ['nullable', 'file', 'image', 'mimes:jpeg,png,jpg,webp,svg', 'max:2048'],
            'is_premium' => ['nullable', 'boolean'],
            'is_active' => ['nullable', 'boolean'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
        ]);

        $previewPath = null;
        if ($request->hasFile('preview_image')) {
            $file = $request->file('preview_image');
            $filename = 'template_' . Str::slug($validated['key']) . '_' . time() . '.' . $file->getClientOriginalExtension();
            $previewPath = $file->storeAs('templates', $filename, 'public');
        }

        $slug = !empty($validated['slug']) ? Str::slug($validated['slug']) : Str::slug($validated['name']);
        $originalSlug = $slug;
        $counter = 1;
        while (CvTemplate::where('slug', $slug)->exists()) {
            $slug = "{$originalSlug}-{$counter}";
            $counter++;
        }

        CvTemplate::create([
            'category_id' => $validated['category_id'],
            'name' => $validated['name'],
            'slug' => $slug,
            'key' => $validated['key'],
            'description' => $validated['description'] ?? null,
            'preview_image' => $previewPath ?? 'images/templates/' . $validated['key'] . '.svg',
            'is_premium' => $request->boolean('is_premium'),
            'is_active' => $request->boolean('is_active', true),
            'sort_order' => $validated['sort_order'] ?? 0,
        ]);

        return redirect()->route('admin.templates.index')->with('success', "Template '{$validated['name']}' created successfully.");
    }

    /**
     * Show the form for editing the specified template.
     */
    public function edit(CvTemplate $template)
    {
        $categories = TemplateCategory::orderBy('sort_order')->get();
        return view('admin.templates.edit', compact('template', 'categories'));
    }

    /**
     * Update the specified template in storage.
     */
    public function update(Request $request, CvTemplate $template)
    {
        $validated = $request->validate([
            'category_id' => ['required', 'exists:template_categories,id'],
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255', 'unique:cv_templates,slug,' . $template->id],
            'key' => ['required', 'string', 'max:50', 'unique:cv_templates,key,' . $template->id, 'regex:/^[a-zA-Z0-9_\-]+$/'],
            'description' => ['nullable', 'string'],
            'preview_image' => ['nullable', 'file', 'image', 'mimes:jpeg,png,jpg,webp,svg', 'max:2048'],
            'is_premium' => ['nullable', 'boolean'],
            'is_active' => ['nullable', 'boolean'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
        ]);

        $previewPath = $template->preview_image;
        if ($request->hasFile('preview_image')) {
            // Delete old uploaded image if stored on public disk
            if ($previewPath && str_starts_with($previewPath, 'templates/') && Storage::disk('public')->exists($previewPath)) {
                Storage::disk('public')->delete($previewPath);
            }

            $file = $request->file('preview_image');
            $filename = 'template_' . Str::slug($validated['key']) . '_' . time() . '.' . $file->getClientOriginalExtension();
            $previewPath = $file->storeAs('templates', $filename, 'public');
        }

        $template->update([
            'category_id' => $validated['category_id'],
            'name' => $validated['name'],
            'slug' => !empty($validated['slug']) ? Str::slug($validated['slug']) : $template->slug,
            'key' => $validated['key'],
            'description' => $validated['description'] ?? null,
            'preview_image' => $previewPath,
            'is_premium' => $request->boolean('is_premium'),
            'is_active' => $request->boolean('is_active'),
            'sort_order' => $validated['sort_order'] ?? $template->sort_order,
        ]);

        return redirect()->route('admin.templates.index')->with('success', "Template '{$template->name}' updated successfully.");
    }

    /**
     * Quick toggle for Free / Premium status.
     */
    public function togglePremium(CvTemplate $template)
    {
        $template->is_premium = !$template->is_premium;
        $template->save();

        $status = $template->is_premium ? 'Premium' : 'Free';
        return back()->with('success', "Template '{$template->name}' marked as {$status}.");
    }

    /**
     * Quick toggle for Active / Inactive status.
     */
    public function toggleStatus(CvTemplate $template)
    {
        $template->is_active = !$template->is_active;
        $template->save();

        $status = $template->is_active ? 'Active' : 'Inactive';
        return back()->with('success', "Template '{$template->name}' marked as {$status}.");
    }

    /**
     * Safe delete template (prevent deletion if referenced by any CV).
     */
    public function destroy(CvTemplate $template)
    {
        $cvsCount = $template->cvs()->count();

        if ($cvsCount > 0) {
            return back()->with('error', "Cannot delete template '{$template->name}' because it is actively used by {$cvsCount} CV(s). Please reassign those CVs first.");
        }

        // Delete uploaded image if on disk
        if ($template->preview_image && str_starts_with($template->preview_image, 'templates/') && Storage::disk('public')->exists($template->preview_image)) {
            Storage::disk('public')->delete($template->preview_image);
        }

        $name = $template->name;
        $template->delete();

        return redirect()->route('admin.templates.index')->with('success', "Template '{$name}' deleted successfully.");
    }
}
