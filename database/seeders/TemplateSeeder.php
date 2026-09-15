<?php

namespace Database\Seeders;

use App\Models\CvTemplate;
use App\Models\DocumentType;
use App\Models\TemplateCategory;
use Illuminate\Database\Seeder;

class TemplateSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // First ensure Document Types exist
        $this->call(DocumentTypeSeeder::class);

        $docTypes = DocumentType::all()->keyBy('slug');

        // 1. Categories
        $categories = [
            [
                'name' => 'Executive & Leadership',
                'slug' => 'executive',
                'description' => 'Clean, authoritative layouts designed for senior leaders, managers, and corporate professionals.',
                'sort_order' => 1,
                'is_active' => true,
            ],
            [
                'name' => 'Modern & Creative',
                'slug' => 'modern',
                'description' => 'Sleek multi-column designs emphasizing skills, personality, and contemporary visual hierarchy.',
                'sort_order' => 2,
                'is_active' => true,
            ],
            [
                'name' => 'Technical & Engineering',
                'slug' => 'technical',
                'description' => 'Structured, metric-driven formats tailored for developers, architects, and technical specialists.',
                'sort_order' => 3,
                'is_active' => true,
            ],
            [
                'name' => 'ATS & Compliance',
                'slug' => 'ats',
                'description' => 'Single-column, high-clarity linear formats optimized for robotic parsers and ATS screening algorithms.',
                'sort_order' => 4,
                'is_active' => true,
            ],
            [
                'name' => 'International & Regional',
                'slug' => 'international',
                'description' => 'Tailored formats complying with regional norms across European and Australian markets.',
                'sort_order' => 5,
                'is_active' => true,
            ],
            [
                'name' => 'Letters & Statements',
                'slug' => 'letters',
                'description' => 'Formal cover letters and academic motivation statements with refined typography.',
                'sort_order' => 6,
                'is_active' => true,
            ],
        ];

        $categoryModels = [];
        foreach ($categories as $catData) {
            $categoryModels[$catData['slug']] = TemplateCategory::updateOrCreate(
                ['slug' => $catData['slug']],
                $catData
            );
        }

        // 2. Templates with compatible document type mappings
        $templates = [
            [
                'category_id' => $categoryModels['executive']->id,
                'name' => 'Classic Executive',
                'slug' => 'classic-executive',
                'key' => 'classic-executive',
                'description' => 'Refined serif typography with traditional top header, horizontal dividers, and an authoritative corporate structure.',
                'preview_image' => 'images/templates/classic-executive.svg',
                'is_premium' => false,
                'is_active' => true,
                'sort_order' => 1,
                'compatible_types' => ['standard-cv', 'usa-resume', 'australia-cv', 'europe-cv'],
            ],
            [
                'category_id' => $categoryModels['modern']->id,
                'name' => 'Modern Minimal',
                'slug' => 'modern-minimal',
                'key' => 'modern-minimal',
                'description' => 'Clean 2-column sidebar design with structured skill proficiency bars, prominent contact information, and modern sans-serif typography.',
                'preview_image' => 'images/templates/modern-minimal.svg',
                'is_premium' => false,
                'is_active' => true,
                'sort_order' => 2,
                'compatible_types' => ['standard-cv', 'usa-resume', 'australia-cv'],
            ],
            [
                'category_id' => $categoryModels['technical']->id,
                'name' => 'Technical Split',
                'slug' => 'technical-split',
                'key' => 'technical-split',
                'description' => 'Compact developer-oriented layout with structured skill tags, project highlights, monospace timeline dates, and dual-column technical metrics.',
                'preview_image' => 'images/templates/technical-split.svg',
                'is_premium' => true,
                'is_active' => true,
                'sort_order' => 3,
                'compatible_types' => ['standard-cv', 'usa-resume'],
            ],
            [
                'category_id' => $categoryModels['ats']->id,
                'name' => 'ATS Clean Standard',
                'slug' => 'ats-clean',
                'key' => 'ats-clean',
                'description' => 'Strictly formatted single-column layout using standard headings and plain typography optimized for 100% ATS readability.',
                'preview_image' => 'images/templates/ats-clean.svg',
                'is_premium' => false,
                'is_active' => true,
                'sort_order' => 4,
                'compatible_types' => ['ats-cv', 'standard-cv', 'usa-resume'],
            ],
            [
                'category_id' => $categoryModels['international']->id,
                'name' => 'Europe Europass Style',
                'slug' => 'europe-europass',
                'key' => 'europe-europass',
                'description' => 'Standard European two-tone layout featuring clear language proficiency ratings and standardized chronological sections.',
                'preview_image' => 'images/templates/europe-europass.svg',
                'is_premium' => false,
                'is_active' => true,
                'sort_order' => 5,
                'compatible_types' => ['europe-cv', 'standard-cv'],
            ],
            [
                'category_id' => $categoryModels['international']->id,
                'name' => 'Australia Compact',
                'slug' => 'australia-compact',
                'key' => 'australia-compact',
                'description' => 'Comprehensive Australian layout with extended career history, prominent key skills, and formal referee credentials.',
                'preview_image' => 'images/templates/australia-compact.svg',
                'is_premium' => true,
                'is_active' => true,
                'sort_order' => 6,
                'compatible_types' => ['australia-cv', 'standard-cv'],
            ],
            [
                'category_id' => $categoryModels['letters']->id,
                'name' => 'Executive Cover Letter',
                'slug' => 'cover-letter-classic',
                'key' => 'cover-letter-classic',
                'description' => 'Authoritative, beautifully aligned executive letterhead and typography crafted for senior professional job applications.',
                'preview_image' => 'images/templates/cover-letter-classic.svg',
                'is_premium' => false,
                'is_active' => true,
                'sort_order' => 7,
                'compatible_types' => ['cover-letter', 'motivation-letter'],
            ],
            [
                'category_id' => $categoryModels['letters']->id,
                'name' => 'Modern Minimalist Letter',
                'slug' => 'cover-letter-modern',
                'key' => 'cover-letter-modern',
                'description' => 'Contemporary left-accent letterhead with elegant spacing and sans-serif typography for clean modern applications.',
                'preview_image' => 'images/templates/cover-letter-modern.svg',
                'is_premium' => false,
                'is_active' => true,
                'sort_order' => 8,
                'compatible_types' => ['cover-letter', 'motivation-letter'],
            ],
            [
                'category_id' => $categoryModels['letters']->id,
                'name' => 'Academic Motivation Letter',
                'slug' => 'motivation-academic',
                'key' => 'motivation-academic',
                'description' => 'Formal institutional layout structured for university admissions, scholarship statements, and research grants.',
                'preview_image' => 'images/templates/motivation-academic.svg',
                'is_premium' => true,
                'is_active' => true,
                'sort_order' => 9,
                'compatible_types' => ['motivation-letter', 'cover-letter'],
            ],
        ];

        foreach ($templates as $tmplData) {
            $compatibleTypes = $tmplData['compatible_types'];
            unset($tmplData['compatible_types']);

            $template = CvTemplate::updateOrCreate(
                ['key' => $tmplData['key']],
                $tmplData
            );

            // Sync compatible document types
            $typeIds = [];
            foreach ($compatibleTypes as $typeSlug) {
                if (isset($docTypes[$typeSlug])) {
                    $typeIds[] = $docTypes[$typeSlug]->id;
                }
            }
            $template->documentTypes()->sync($typeIds);
        }
    }
}
