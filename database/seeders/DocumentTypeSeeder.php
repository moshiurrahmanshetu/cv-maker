<?php

namespace Database\Seeders;

use App\Models\Cv;
use App\Models\CvTemplate;
use App\Models\DocumentType;
use App\Models\TemplateCategory;
use Illuminate\Database\Seeder;

class DocumentTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $documentTypes = [
            [
                'name' => 'Standard CV',
                'slug' => 'standard-cv',
                'description' => 'Comprehensive, professional curriculum vitae suitable for international applications, executive roles, and diverse career paths.',
                'icon' => 'bi-file-earmark-person',
                'is_active' => true,
                'sort_order' => 1,
                'configuration' => [
                    'category' => 'cv',
                    'default_template' => 'classic-executive',
                    'supports_photo' => true,
                    'sections' => ['personal-info', 'summary', 'experience', 'education', 'skills', 'languages', 'certifications', 'projects', 'awards', 'references', 'custom'],
                ],
            ],
            [
                'name' => 'ATS-Friendly CV',
                'slug' => 'ats-cv',
                'description' => 'Single-column, clean linear format optimized for Applicant Tracking Systems with machine-readable headings and high parsability.',
                'icon' => 'bi-file-earmark-check',
                'is_active' => true,
                'sort_order' => 2,
                'configuration' => [
                    'category' => 'cv',
                    'default_template' => 'ats-clean',
                    'supports_photo' => false,
                    'sections' => ['personal-info', 'summary', 'experience', 'education', 'skills', 'languages', 'certifications', 'projects', 'awards', 'references', 'custom'],
                ],
            ],
            [
                'name' => 'USA Resume',
                'slug' => 'usa-resume',
                'description' => 'Concise, metric-driven American-style resume focusing on quantifiable achievements and key technical competencies.',
                'icon' => 'bi-file-earmark-bar-graph',
                'is_active' => true,
                'sort_order' => 3,
                'configuration' => [
                    'category' => 'cv',
                    'default_template' => 'modern-minimal',
                    'supports_photo' => false,
                    'sections' => ['personal-info', 'summary', 'experience', 'education', 'skills', 'certifications', 'projects', 'awards'],
                ],
            ],
            [
                'name' => 'Australia-style CV',
                'slug' => 'australia-cv',
                'description' => 'Structured Australian format emphasizing comprehensive career history, educational background, and detailed referee information.',
                'icon' => 'bi-file-earmark-ruled',
                'is_active' => true,
                'sort_order' => 4,
                'configuration' => [
                    'category' => 'cv',
                    'default_template' => 'australia-compact',
                    'supports_photo' => true,
                    'sections' => ['personal-info', 'summary', 'experience', 'education', 'skills', 'languages', 'certifications', 'references', 'custom'],
                ],
            ],
            [
                'name' => 'Europe-style CV',
                'slug' => 'europe-cv',
                'description' => 'European standard format structured with clear section demarcations, CEFR language levels, and standardized layout aesthetics.',
                'icon' => 'bi-file-earmark-spreadsheet',
                'is_active' => true,
                'sort_order' => 5,
                'configuration' => [
                    'category' => 'cv',
                    'default_template' => 'europe-europass',
                    'supports_photo' => true,
                    'sections' => ['personal-info', 'summary', 'experience', 'education', 'skills', 'languages', 'certifications', 'projects', 'custom'],
                ],
            ],
            [
                'name' => 'Cover Letter',
                'slug' => 'cover-letter',
                'description' => 'Formal, persuasive cover letter designed to introduce yourself, highlight your top qualifications, and complement your CV.',
                'icon' => 'bi-envelope-paper',
                'is_active' => true,
                'sort_order' => 6,
                'configuration' => [
                    'category' => 'letter',
                    'default_template' => 'cover-letter-classic',
                    'supports_photo' => false,
                    'sections' => ['personal-info', 'letter-details', 'letter-content', 'letter-closing'],
                ],
            ],
            [
                'name' => 'Motivation Letter',
                'slug' => 'motivation-letter',
                'description' => 'Targeted statement of purpose and motivation letter for universities, academic programs, scholarships, and research grants.',
                'icon' => 'bi-mortarboard',
                'is_active' => true,
                'sort_order' => 7,
                'configuration' => [
                    'category' => 'letter',
                    'default_template' => 'motivation-academic',
                    'supports_photo' => false,
                    'sections' => ['personal-info', 'letter-details', 'letter-content', 'letter-closing'],
                ],
            ],
        ];

        $docTypeModels = [];
        foreach ($documentTypes as $typeData) {
            $docTypeModels[$typeData['slug']] = DocumentType::updateOrCreate(
                ['slug' => $typeData['slug']],
                $typeData
            );
        }

        // Assign any existing CVs without a document_type_id to 'standard-cv'
        $standardCvType = $docTypeModels['standard-cv'] ?? DocumentType::first();
        if ($standardCvType) {
            Cv::whereNull('document_type_id')->update(['document_type_id' => $standardCvType->id]);
        }
    }
}
