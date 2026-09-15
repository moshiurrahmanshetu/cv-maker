<?php

namespace App\Http\Controllers;

use App\Models\CvTemplate;
use App\Services\TemplateService;
use Illuminate\Http\Request;

class TemplateController extends Controller
{
    public function __construct(
        protected TemplateService $templateService
    ) {}

    /**
     * Display a public gallery / listing of templates (optional standalone or API).
     */
    public function index(Request $request)
    {
        $categories = $this->templateService->getCategoriesWithTemplates();
        $selectedCategory = $request->query('category', 'all');
        $templates = $this->templateService->getActiveTemplates($selectedCategory);

        return view('templates.index', compact('categories', 'templates', 'selectedCategory'));
    }

    /**
     * Render live sample preview for a given template (for interactive preview modal / fullscreen).
     */
    public function preview(CvTemplate $template)
    {
        $cvData = $this->templateService->getSampleCvData($template);
        $templateView = $this->templateService->resolveViewPath($template);

        if (request()->ajax() || request()->wantsJson() || request()->has('modal')) {
            return view('templates.preview_modal_content', [
                'template' => $template,
                'cvData' => $cvData,
                'templateView' => $templateView,
            ]);
        }

        return view('templates.preview', [
            'template' => $template,
            'cvData' => $cvData,
            'templateView' => $templateView,
        ]);
    }
}
