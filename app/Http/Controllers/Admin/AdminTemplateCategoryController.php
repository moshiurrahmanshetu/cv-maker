<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\TemplateCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class AdminTemplateCategoryController extends Controller
{
    /**
     * Display a listing of template categories.
     */
    public function index(Request $request)
    {
        $query = TemplateCategory::withCount('templates');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where('name', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
        }

        $categories = $query->orderBy('sort_order')->paginate(10)->withQueryString();

        return view('admin.templates.categories.index', compact('categories'));
    }

    /**
     * Store a newly created category.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255', 'unique:template_categories,slug'],
            'description' => ['nullable', 'string'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $slug = !empty($validated['slug']) ? Str::slug($validated['slug']) : Str::slug($validated['name']);
        
        // Ensure uniqueness if auto-generated
        $originalSlug = $slug;
        $counter = 1;
        while (TemplateCategory::where('slug', $slug)->exists()) {
            $slug = "{$originalSlug}-{$counter}";
            $counter++;
        }

        TemplateCategory::create([
            'name' => $validated['name'],
            'slug' => $slug,
            'description' => $validated['description'] ?? null,
            'sort_order' => $validated['sort_order'] ?? 0,
            'is_active' => $request->boolean('is_active', true),
        ]);

        return redirect()->route('admin.templates.categories.index')->with('success', "Category '{$validated['name']}' created successfully.");
    }

    /**
     * Update the specified category.
     */
    public function update(Request $request, TemplateCategory $category)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255', 'unique:template_categories,slug,' . $category->id],
            'description' => ['nullable', 'string'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $category->update([
            'name' => $validated['name'],
            'slug' => !empty($validated['slug']) ? Str::slug($validated['slug']) : $category->slug,
            'description' => $validated['description'] ?? null,
            'sort_order' => $validated['sort_order'] ?? $category->sort_order,
            'is_active' => $request->boolean('is_active'),
        ]);

        return redirect()->route('admin.templates.categories.index')->with('success', "Category '{$category->name}' updated successfully.");
    }

    /**
     * Remove the specified category (safe deletion).
     */
    public function destroy(TemplateCategory $category)
    {
        if ($category->templates()->count() > 0) {
            return back()->with('error', "Cannot delete category '{$category->name}' because it contains {$category->templates()->count()} template(s). Reassign or delete the templates first.");
        }

        $name = $category->name;
        $category->delete();

        return redirect()->route('admin.templates.categories.index')->with('success', "Category '{$name}' deleted successfully.");
    }
}
