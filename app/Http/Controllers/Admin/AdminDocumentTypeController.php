<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DocumentType;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class AdminDocumentTypeController extends Controller
{
    /**
     * Display a listing of document types.
     */
    public function index(Request $request)
    {
        $query = DocumentType::withCount(['documents', 'templates'])->orderBy('sort_order');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('slug', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        if ($request->filled('status')) {
            $query->where('is_active', $request->status === 'active');
        }

        $documentTypes = $query->paginate(10)->withQueryString();

        return view('admin.document-types.index', compact('documentTypes'));
    }

    /**
     * Show the form for creating a new document type.
     */
    public function create()
    {
        return view('admin.document-types.create');
    }

    /**
     * Store a newly created document type.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255', 'unique:document_types,slug'],
            'description' => ['nullable', 'string'],
            'icon' => ['nullable', 'string', 'max:50'],
            'is_active' => ['nullable', 'boolean'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
        ]);

        $slug = !empty($validated['slug']) ? Str::slug($validated['slug']) : Str::slug($validated['name']);
        $originalSlug = $slug;
        $counter = 1;
        while (DocumentType::where('slug', $slug)->exists()) {
            $slug = "{$originalSlug}-{$counter}";
            $counter++;
        }

        DocumentType::create([
            'name' => $validated['name'],
            'slug' => $slug,
            'description' => $validated['description'] ?? null,
            'icon' => $validated['icon'] ?: 'bi-file-earmark-text',
            'is_active' => $request->boolean('is_active', true),
            'sort_order' => $validated['sort_order'] ?? 0,
        ]);

        return redirect()->route('admin.document-types.index')->with('success', "Document Type '{$validated['name']}' created successfully.");
    }

    /**
     * Show the form for editing the specified document type.
     */
    public function edit(DocumentType $documentType)
    {
        return view('admin.document-types.edit', compact('documentType'));
    }

    /**
     * Update the specified document type.
     */
    public function update(Request $request, DocumentType $documentType)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255', 'unique:document_types,slug,' . $documentType->id],
            'description' => ['nullable', 'string'],
            'icon' => ['nullable', 'string', 'max:50'],
            'is_active' => ['nullable', 'boolean'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
        ]);

        $documentType->update([
            'name' => $validated['name'],
            'slug' => !empty($validated['slug']) ? Str::slug($validated['slug']) : $documentType->slug,
            'description' => $validated['description'] ?? null,
            'icon' => $validated['icon'] ?: $documentType->icon,
            'is_active' => $request->boolean('is_active'),
            'sort_order' => $validated['sort_order'] ?? $documentType->sort_order,
        ]);

        return redirect()->route('admin.document-types.index')->with('success', "Document Type '{$documentType->name}' updated successfully.");
    }

    /**
     * Quick toggle for Active / Inactive status.
     */
    public function toggleStatus(DocumentType $documentType)
    {
        $documentType->is_active = !$documentType->is_active;
        $documentType->save();

        $status = $documentType->is_active ? 'Active' : 'Inactive';
        return back()->with('success', "Document Type '{$documentType->name}' marked as {$status}.");
    }

    /**
     * Safe delete document type (prevent deletion if referenced by existing documents).
     */
    public function destroy(DocumentType $documentType)
    {
        $docsCount = $documentType->documents()->count();

        if ($docsCount > 0) {
            return back()->with('error', "Cannot delete Document Type '{$documentType->name}' because it is actively used by {$docsCount} document(s).");
        }

        $name = $documentType->name;
        $documentType->delete();

        return redirect()->route('admin.document-types.index')->with('success', "Document Type '{$name}' deleted successfully.");
    }
}
