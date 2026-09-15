@extends('layouts.admin')

@section('title', 'Template Categories - Admin')
@section('page-title', 'Template Categories')

@section('content')
<div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3 mb-4">
    <div>
        <a href="{{ route('admin.templates.index') }}" class="small text-muted text-decoration-none">
            <i class="bi bi-arrow-left me-1"></i> Back to Templates
        </a>
        <h2 class="h4 fw-bold mt-2 mb-1">Template Categories</h2>
        <p class="text-muted small mb-0">Organize and group templates into distinct professional catalog categories.</p>
    </div>
    <button type="button" class="btn-saas-primary" data-bs-toggle="modal" data-bs-target="#createCategoryModal">
        <i class="bi bi-plus-lg"></i> Add Category
    </button>
</div>

<!-- Categories Table -->
<div class="card card-saas overflow-hidden border">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="bg-surface-subtle border-bottom">
                <tr>
                    <th class="ps-4">Category Name</th>
                    <th>Slug</th>
                    <th>Description</th>
                    <th>Sort Order</th>
                    <th>Status</th>
                    <th>Templates Count</th>
                    <th class="text-end pe-4">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($categories as $category)
                    <tr>
                        <td class="ps-4">
                            <div class="fw-bold text-dark">{{ $category->name }}</div>
                        </td>
                        <td>
                            <code class="text-dark bg-light px-2 py-1 rounded small border">{{ $category->slug }}</code>
                        </td>
                        <td>
                            <span class="small text-muted text-truncate d-inline-block" style="max-width: 250px;">
                                {{ $category->description ?? 'No description' }}
                            </span>
                        </td>
                        <td>
                            <span class="badge bg-light text-dark border">{{ $category->sort_order }}</span>
                        </td>
                        <td>
                            @if($category->is_active)
                                <span class="badge bg-success-subtle text-success border border-success-subtle">
                                    <i class="bi bi-check-circle me-1"></i> Active
                                </span>
                            @else
                                <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle">
                                    <i class="bi bi-dash-circle me-1"></i> Inactive
                                </span>
                            @endif
                        </td>
                        <td>
                            <a href="{{ route('admin.templates.index', ['category_id' => $category->id]) }}" class="badge bg-light text-secondary border text-decoration-none">
                                <i class="bi bi-palette me-1"></i> {{ $category->templates_count }} template{{ $category->templates_count === 1 ? '' : 's' }}
                            </a>
                        </td>
                        <td class="text-end pe-4">
                            <div class="d-flex justify-content-end gap-1">
                                <button type="button" class="btn btn-sm btn-saas-secondary py-1 px-2" data-bs-toggle="modal" data-bs-target="#editCategoryModal{{ $category->id }}" title="Edit Category">
                                    <i class="bi bi-pencil"></i>
                                </button>
                                <button type="button" class="btn btn-sm btn-outline-danger py-1 px-2" data-bs-toggle="modal" data-bs-target="#deleteCategoryModal{{ $category->id }}" title="Delete Category">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </div>
                        </td>
                    </tr>

                    <!-- Edit Category Modal -->
                    <div class="modal fade" id="editCategoryModal{{ $category->id }}" tabindex="-1" aria-labelledby="editCategoryModalLabel{{ $category->id }}" aria-hidden="true">
                        <div class="modal-dialog modal-dialog-centered">
                            <div class="modal-content border card-saas">
                                <form method="POST" action="{{ route('admin.templates.categories.update', $category) }}">
                                    @csrf
                                    @method('PUT')
                                    <div class="modal-header border-bottom">
                                        <h5 class="modal-title fw-bold fs-6" id="editCategoryModalLabel{{ $category->id }}">Edit Category</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                    </div>
                                    <div class="modal-body py-4">
                                        <div class="mb-3">
                                            <label for="name{{ $category->id }}" class="form-label fw-semibold">Category Name <span class="text-danger">*</span></label>
                                            <input type="text" class="form-control" id="name{{ $category->id }}" name="name" value="{{ $category->name }}" required>
                                        </div>
                                        <div class="mb-3">
                                            <label for="slug{{ $category->id }}" class="form-label fw-semibold">Slug</label>
                                            <input type="text" class="form-control" id="slug{{ $category->id }}" name="slug" value="{{ $category->slug }}">
                                        </div>
                                        <div class="mb-3">
                                            <label for="description{{ $category->id }}" class="form-label fw-semibold">Description</label>
                                            <textarea class="form-control" id="description{{ $category->id }}" name="description" rows="2">{{ $category->description }}</textarea>
                                        </div>
                                        <div class="row g-2 mb-3">
                                            <div class="col-6">
                                                <label for="sort_order{{ $category->id }}" class="form-label fw-semibold">Sort Order</label>
                                                <input type="number" class="form-control" id="sort_order{{ $category->id }}" name="sort_order" value="{{ $category->sort_order }}" min="0">
                                            </div>
                                            <div class="col-6 d-flex align-items-end">
                                                <div class="form-check form-switch mb-2">
                                                    <input class="form-check-input" type="checkbox" role="switch" id="is_active{{ $category->id }}" name="is_active" value="1" {{ $category->is_active ? 'checked' : '' }}>
                                                    <label class="form-check-label fw-semibold" for="is_active{{ $category->id }}">Active</label>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="modal-footer border-top">
                                        <button type="button" class="btn-saas-secondary" data-bs-dismiss="modal">Cancel</button>
                                        <button type="submit" class="btn-saas-primary">Save Changes</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>

                    <!-- Delete Category Modal -->
                    <div class="modal fade" id="deleteCategoryModal{{ $category->id }}" tabindex="-1" aria-labelledby="deleteCategoryModalLabel{{ $category->id }}" aria-hidden="true">
                        <div class="modal-dialog modal-dialog-centered">
                            <div class="modal-content border card-saas">
                                <div class="modal-header border-bottom">
                                    <h5 class="modal-title fw-bold fs-6" id="deleteCategoryModalLabel{{ $category->id }}">Delete Category</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                </div>
                                <div class="modal-body py-4">
                                    @if($category->templates_count > 0)
                                        <div class="alert alert-warning mb-0">
                                            <i class="bi bi-exclamation-triangle-fill me-1"></i>
                                            <strong>Safe Deletion Check:</strong> Cannot delete category containing <strong>{{ $category->templates_count }} template(s)</strong>. Please reassign or delete the templates first.
                                        </div>
                                    @else
                                        <p class="mb-1">Are you sure you want to delete category <strong>"{{ $category->name }}"</strong>?</p>
                                        <p class="small text-muted mb-0">This action will remove the category from the platform.</p>
                                    @endif
                                </div>
                                <div class="modal-footer border-top">
                                    <button type="button" class="btn-saas-secondary" data-bs-dismiss="modal">Cancel</button>
                                    @if($category->templates_count === 0)
                                        <form action="{{ route('admin.templates.categories.destroy', $category) }}" method="POST">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-danger btn-sm px-3 py-2">
                                                <i class="bi bi-trash me-1"></i> Confirm Delete
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                @empty
                    <tr>
                        <td colspan="7" class="text-center py-5 text-muted">
                            <i class="bi bi-tags fs-1 d-block mb-2"></i>
                            No categories found.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($categories->hasPages())
        <div class="card-footer bg-white border-top p-3 d-flex justify-content-center">
            {{ $categories->links('pagination::bootstrap-5') }}
        </div>
    @endif
</div>

<!-- Create Category Modal -->
<div class="modal fade" id="createCategoryModal" tabindex="-1" aria-labelledby="createCategoryModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border card-saas">
            <form method="POST" action="{{ route('admin.templates.categories.store') }}">
                @csrf
                <div class="modal-header border-bottom">
                    <h5 class="modal-title fw-bold fs-6" id="createCategoryModalLabel">Add Template Category</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body py-4">
                    <div class="mb-3">
                        <label for="new_name" class="form-label fw-semibold">Category Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="new_name" name="name" placeholder="e.g. Executive & Leadership" required autofocus>
                    </div>
                    <div class="mb-3">
                        <label for="new_slug" class="form-label fw-semibold">Slug (Optional)</label>
                        <input type="text" class="form-control" id="new_slug" name="slug" placeholder="Auto-generated if empty">
                    </div>
                    <div class="mb-3">
                        <label for="new_description" class="form-label fw-semibold">Description</label>
                        <textarea class="form-control" id="new_description" name="description" rows="2" placeholder="Brief description of the category focus..."></textarea>
                    </div>
                    <div class="row g-2 mb-3">
                        <div class="col-6">
                            <label for="new_sort_order" class="form-label fw-semibold">Sort Order</label>
                            <input type="number" class="form-control" id="new_sort_order" name="sort_order" value="0" min="0">
                        </div>
                        <div class="col-6 d-flex align-items-end">
                            <div class="form-check form-switch mb-2">
                                <input class="form-check-input" type="checkbox" role="switch" id="new_is_active" name="is_active" value="1" checked>
                                <label class="form-check-label fw-semibold" for="new_is_active">Active</label>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-top">
                    <button type="button" class="btn-saas-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn-saas-primary">Create Category</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
