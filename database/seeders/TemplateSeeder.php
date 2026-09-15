<?php

namespace Database\Seeders;

use App\Models\CvTemplate;
use App\Models\TemplateCategory;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class TemplateSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
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
                'name' => 'Simple & Academic',
                'slug' => 'simple',
                'description' => 'Minimalist single-column layouts perfect for researchers, scholars, and direct clarity.',
                'sort_order' => 4,
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

        // 2. Initial Templates
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
            ],
        ];

        foreach ($templates as $tmpl) {
            CvTemplate::updateOrCreate(
                ['key' => $tmpl['key']],
                $tmpl
            );
        }
    }
}
