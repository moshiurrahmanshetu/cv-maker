<?php

namespace App\Services;

use App\Models\Cv;
use App\Models\CvTemplate;
use App\Models\DocumentType;
use App\Models\TemplateCategory;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\View;

class TemplateService
{
    /**
     * Default template key fallback.
     */
    public const DEFAULT_TEMPLATE_KEY = 'classic-executive';

    /**
     * Get all active categories with their active templates.
     */
    public function getCategoriesWithTemplates(): Collection
    {
        return TemplateCategory::where('is_active', true)
            ->with(['activeTemplates'])
            ->orderBy('sort_order')
            ->get();
    }

    /**
     * Get all active templates with optional category filter.
     */
    public function getActiveTemplates(?string $categorySlug = null): Collection
    {
        $query = CvTemplate::where('is_active', true)
            ->with(['category', 'documentTypes'])
            ->orderBy('sort_order');

        if ($categorySlug && $categorySlug !== 'all') {
            $query->whereHas('category', function ($q) use ($categorySlug) {
                $q->where('slug', $categorySlug);
            });
        }

        return $query->get();
    }

    /**
     * Get all active templates compatible with a given document type.
     */
    public function getActiveTemplatesForDocumentType(int|string|null $documentTypeId): Collection
    {
        if (empty($documentTypeId)) {
            return $this->getActiveTemplates();
        }

        $query = CvTemplate::where('is_active', true)
            ->with(['category', 'documentTypes'])
            ->orderBy('sort_order');

        $query->whereHas('documentTypes', function ($q) use ($documentTypeId) {
            if (is_numeric($documentTypeId)) {
                $q->where('document_types.id', $documentTypeId);
            } else {
                $q->where('document_types.slug', $documentTypeId);
            }
        });

        $results = $query->get();

        // If no specifically mapped templates found, fallback to active templates
        return $results->isNotEmpty() ? $results : $this->getActiveTemplates();
    }

    /**
     * Find template by ID, slug, or key.
     */
    public function findTemplate(int|string|null $identifier): ?CvTemplate
    {
        if (empty($identifier)) {
            return $this->getDefaultTemplate();
        }

        if (is_numeric($identifier)) {
            $template = CvTemplate::find($identifier);
            if ($template) return $template;
        }

        return CvTemplate::where('slug', $identifier)
            ->orWhere('key', $identifier)
            ->first() ?? $this->getDefaultTemplate();
    }

    /**
     * Get default fallback template model for a specific document type.
     */
    public function getDefaultTemplate(?DocumentType $documentType = null): ?CvTemplate
    {
        if ($documentType && !empty($documentType->configuration['default_template'])) {
            $matched = CvTemplate::where('key', $documentType->configuration['default_template'])->first();
            if ($matched) return $matched;
        }

        if ($documentType) {
            $compatible = $documentType->activeTemplates()->first();
            if ($compatible) return $compatible;
        }

        return CvTemplate::where('key', self::DEFAULT_TEMPLATE_KEY)->first()
            ?? CvTemplate::where('is_active', true)->orderBy('sort_order')->first();
    }

    /**
     * Safely resolve the Blade view path for a given template.
     * Prevents path traversal and guarantees fallback.
     */
    public function resolveViewPath(CvTemplate|string|null $template): string
    {
        $key = self::DEFAULT_TEMPLATE_KEY;

        if ($template instanceof CvTemplate) {
            $key = $template->key;
        } elseif (is_string($template) && !empty($template)) {
            // Strip any directory traversal characters
            $sanitizedKey = preg_replace('/[^a-zA-Z0-9_\-]/', '', $template);
            $found = CvTemplate::where('key', $sanitizedKey)->orWhere('slug', $sanitizedKey)->first();
            if ($found) {
                $key = $found->key;
            } else {
                $key = $sanitizedKey;
            }
        }

        $viewPath = "cv-templates.{$key}.template";

        if (View::exists($viewPath)) {
            return $viewPath;
        }

        // Fallback default view
        $defaultView = "cv-templates." . self::DEFAULT_TEMPLATE_KEY . ".template";
        if (View::exists($defaultView)) {
            return $defaultView;
        }

        // Ultimate fallback to existing cvs.show preview format
        return 'cvs.show';
    }

    /**
     * Prepares normalized structured View-Model from a Cv/Document model.
     * Guarantees all sections exist with clean collections, customization styles, and letter data.
     */
    public function prepareCvData(Cv $cv): array
    {
        // Ensure relationships are loaded
        $cv->loadMissing([
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

        $visibleReferences = $cv->references->where('is_hidden', false);
        $groupedCustomSections = $cv->customSections->groupBy('section_title');
        $skillsByCategory = $cv->skills->groupBy(function ($skill) {
            return !empty($skill->category) ? $skill->category : 'General Skills';
        });

        // Settings / Design Customization resolution
        $settings = is_array($cv->settings) ? $cv->settings : [];
        $accentColor = $settings['accent_color'] ?? $cv->primary_color ?? '#1e293b';
        $fontFamily = $settings['font_family'] ?? $cv->font_family ?? 'Inter';
        $fontSizeScale = $settings['font_size'] ?? 'normal'; // small, normal, large
        $headingScale = $settings['heading_size'] ?? 'normal'; // compact, normal, large
        $lineSpacing = $settings['line_spacing'] ?? 'normal'; // compact, normal, relaxed
        $sectionSpacing = $settings['section_spacing'] ?? 'normal'; // compact, normal, spacious
        $photoSize = $settings['photo_size'] ?? 'medium'; // small, medium, large, hidden
        $showIcons = isset($settings['show_icons']) ? (bool)$settings['show_icons'] : true;

        $docTypeSlug = $cv->documentType?->slug ?? 'standard-cv';
        $isLetter = $cv->isLetter();

        return [
            'id' => $cv->id,
            'title' => $cv->title,
            'summary' => $cv->summary,
            'status' => $cv->status,
            'document_type' => $cv->documentType,
            'document_type_slug' => $docTypeSlug,
            'document_type_name' => $cv->documentType?->name ?? 'Standard CV',
            'is_letter' => $isLetter,
            'template_key' => $cv->template?->key ?? $cv->template_key ?? self::DEFAULT_TEMPLATE_KEY,
            'template_name' => $cv->template?->name ?? 'Standard Template',
            'completion_percentage' => $cv->completion_percentage,
            
            // Customization & Styles
            'settings' => $settings,
            'accentColor' => $accentColor,
            'fontFamily' => $fontFamily,
            'fontSizeScale' => $fontSizeScale,
            'headingScale' => $headingScale,
            'lineSpacing' => $lineSpacing,
            'sectionSpacing' => $sectionSpacing,
            'photoSize' => $photoSize,
            'showIcons' => $showIcons,

            // Personal Info / Sender Info
            'personalInfo' => $cv->personalInfo,
            'fullName' => $cv->personalInfo?->full_name ?? 'Your Name',
            'jobTitle' => $cv->personalInfo?->job_title ?? 'Professional Title',
            'email' => $cv->personalInfo?->email,
            'phone' => $cv->personalInfo?->phone,
            'address' => $cv->personalInfo?->address,
            'city' => $cv->personalInfo?->city,
            'country' => $cv->personalInfo?->country,
            'location' => implode(', ', array_filter([$cv->personalInfo?->city, $cv->personalInfo?->country])),
            'postalCode' => $cv->personalInfo?->postal_code,
            'website' => $cv->personalInfo?->website,
            'linkedin' => $cv->personalInfo?->linkedin,
            'github' => $cv->personalInfo?->github,
            'otherUrl' => $cv->personalInfo?->other_url,
            'photoUrl' => $cv->personalInfo?->photo_url,

            // Letter Details (Cover Letters & Motivation Letters)
            'letterDetail' => $cv->letterDetail,
            'recipientName' => $cv->letterDetail?->recipient_name ?? 'Hiring Team / Committee',
            'recipientTitle' => $cv->letterDetail?->recipient_title ?? '',
            'companyName' => $cv->letterDetail?->company_name ?? 'Target Organization',
            'companyAddress' => $cv->letterDetail?->company_address ?? '',
            'letterDate' => $cv->letterDetail?->letter_date ?? date('F j, Y'),
            'subject' => $cv->letterDetail?->subject ?? '',
            'salutation' => $cv->letterDetail?->salutation ?? 'Dear Hiring Manager,',
            'opening' => $cv->letterDetail?->opening ?? '',
            'body' => $cv->letterDetail?->body ?? '',
            'callToAction' => $cv->letterDetail?->call_to_action ?? '',
            'closing' => $cv->letterDetail?->closing ?? 'Sincerely,',
            'senderSignature' => $cv->letterDetail?->sender_signature ?? ($cv->personalInfo?->full_name ?? 'Your Name'),

            // CV Collections
            'experiences' => $cv->experiences,
            'educations' => $cv->educations,
            'skills' => $cv->skills,
            'skillsByCategory' => $skillsByCategory,
            'languages' => $cv->languages,
            'certifications' => $cv->certifications,
            'projects' => $cv->projects,
            'awards' => $cv->awards,
            'references' => $visibleReferences,
            'customSectionsGrouped' => $groupedCustomSections,

            // Booleans for clean omission in templates
            'hasSummary' => !empty(trim($cv->summary ?? '')),
            'hasPersonalInfo' => (bool)$cv->personalInfo,
            'hasLetterDetail' => (bool)$cv->letterDetail,
            'hasExperiences' => $cv->experiences->isNotEmpty(),
            'hasEducations' => $cv->educations->isNotEmpty(),
            'hasSkills' => $cv->skills->isNotEmpty(),
            'hasLanguages' => $cv->languages->isNotEmpty(),
            'hasCertifications' => $cv->certifications->isNotEmpty(),
            'hasProjects' => $cv->projects->isNotEmpty(),
            'hasAwards' => $cv->awards->isNotEmpty(),
            'hasReferences' => $visibleReferences->isNotEmpty(),
            'hasCustomSections' => $groupedCustomSections->isNotEmpty(),
        ];
    }

    /**
     * Generate structured sample data for template previews.
     * Automatically differentiates between CV templates and Letter templates.
     */
    public function getSampleCvData(?CvTemplate $template = null): array
    {
        $key = $template?->key ?? self::DEFAULT_TEMPLATE_KEY;
        $isLetter = str_starts_with($key, 'cover-letter') || str_starts_with($key, 'motivation-');

        if ($isLetter) {
            $isMotivation = str_starts_with($key, 'motivation-');
            return [
                'id' => 0,
                'title' => $isMotivation ? 'Sample Motivation Statement' : 'Sample Executive Cover Letter',
                'summary' => null,
                'status' => 'published',
                'document_type' => null,
                'document_type_slug' => $isMotivation ? 'motivation-letter' : 'cover-letter',
                'document_type_name' => $isMotivation ? 'Motivation Letter' : 'Cover Letter',
                'is_letter' => true,
                'template_key' => $key,
                'template_name' => $template?->name ?? ($isMotivation ? 'Academic Motivation' : 'Executive Cover Letter'),
                'completion_percentage' => 100,

                // Customization
                'settings' => [],
                'accentColor' => $isMotivation ? '#8c1d40' : '#1e293b',
                'fontFamily' => $isMotivation ? 'Georgia' : 'Inter',
                'fontSizeScale' => 'normal',
                'headingScale' => 'normal',
                'lineSpacing' => 'normal',
                'sectionSpacing' => 'normal',
                'photoSize' => 'medium',
                'showIcons' => true,

                // Sender Info
                'personalInfo' => (object)[
                    'full_name' => 'Alexander Vance',
                    'job_title' => 'Principal Software Architect',
                    'email' => 'alexander.vance@example.com',
                    'phone' => '+1 (555) 432-8765',
                    'city' => 'San Francisco',
                    'country' => 'United States',
                    'location' => 'San Francisco, United States',
                    'website' => 'https://alexvance.dev',
                    'linkedin' => 'https://linkedin.com/in/alexvance',
                    'photo_url' => null,
                ],
                'fullName' => 'Alexander Vance',
                'jobTitle' => 'Principal Software Architect',
                'email' => 'alexander.vance@example.com',
                'phone' => '+1 (555) 432-8765',
                'location' => 'San Francisco, United States',
                'city' => 'San Francisco',
                'country' => 'United States',
                'website' => 'https://alexvance.dev',
                'linkedin' => 'https://linkedin.com/in/alexvance',
                'github' => 'https://github.com/alexvance',
                'otherUrl' => null,
                'photoUrl' => null,

                // Letter Details
                'recipientName' => $isMotivation ? 'Graduate Admissions Committee' : 'Dr. Elizabeth Vance',
                'recipientTitle' => $isMotivation ? 'Department of Computer Science' : 'VP of Engineering Talent',
                'companyName' => $isMotivation ? 'Stanford University' : 'Apex Cloud Systems',
                'companyAddress' => $isMotivation ? "450 Jane Stanford Way\nStanford, CA 94305" : "100 Market Street, Suite 400\nSan Francisco, CA 94105",
                'letterDate' => date('F j, Y'),
                'subject' => $isMotivation ? 'Statement of Purpose – M.S. in Computer Science (Distributed Systems)' : 'Application for Principal Infrastructure Architect (Req #8421)',
                'salutation' => $isMotivation ? 'Dear Members of the Admissions Committee,' : 'Dear Dr. Vance,',
                'opening' => $isMotivation
                    ? "I am writing to express my earnest application for admission to the Master of Science in Computer Science program at Stanford University. With over eight years of engineering leadership architecting distributed cloud platforms, I aspire to conduct advanced research under your esteemed faculty in autonomous distributed systems optimization."
                    : "I am writing to submit my application for the Principal Infrastructure Architect position at Apex Cloud Systems. Having followed Apex's impressive engineering milestones in cloud scaling, I am eager to leverage my 8+ years of distributed systems architecture experience to accelerate your technological roadmap.",
                'body' => $isMotivation
                    ? "Throughout my tenure leading engineering teams, I observed firsthand the fundamental performance bottlenecks of heterogeneous microservice networks. At Apex Cloud Systems, I led the architectural redesign of our multi-region streaming pipelines, achieving 99.99% availability across 15 million daily transactions while decreasing infrastructure compute latency by 45%.\n\nGraduating with Honors in Computer Science from UC Berkeley (3.92 GPA), I developed a rigorous empirical foundation. Stanford's pioneering work in decentralized consensus and resilient fault tolerance represents the ideal academic setting to advance my research goals."
                    : "During my recent tenure as Lead Architect, I spearheaded the complete modernization of our containerized microservice topology, reducing deployment downtime by 55% and mentoring a distributed team of 12 senior engineers.\n\nMy technical expertise spans distributed cache consistency, resilient event-driven architectures, and high-throughput SQL query optimization. I pride myself on aligning complex engineering deliverables directly with strategic business objectives.",
                'callToAction' => $isMotivation
                    ? "Thank you for your review of my application and credentials. I welcome the opportunity to discuss my academic preparation and contribute meaningfully to the Stanford research community."
                    : "Thank you for your consideration. I look forward to the opportunity to discuss how my systems design background and leadership philosophy align with Apex's high-growth vision.",
                'closing' => $isMotivation ? 'Respectfully submitted,' : 'Sincerely,',
                'senderSignature' => 'Alexander Vance',

                'experiences' => collect([]),
                'educations' => collect([]),
                'skills' => collect([]),
                'skillsByCategory' => collect([]),
                'languages' => collect([]),
                'certifications' => collect([]),
                'projects' => collect([]),
                'awards' => collect([]),
                'references' => collect([]),
                'customSectionsGrouped' => collect([]),

                'hasSummary' => false,
                'hasPersonalInfo' => true,
                'hasLetterDetail' => true,
                'hasExperiences' => false,
                'hasEducations' => false,
                'hasSkills' => false,
                'hasLanguages' => false,
                'hasCertifications' => false,
                'hasProjects' => false,
                'hasAwards' => false,
                'hasReferences' => false,
                'hasCustomSections' => false,
            ];
        }

        // CV Template Sample Data
        $skills = collect([
            (object)['name' => 'Laravel & PHP', 'level' => 'Expert', 'rating' => 95, 'category' => 'Backend'],
            (object)['name' => 'MySQL & Relational Design', 'level' => 'Expert', 'rating' => 90, 'category' => 'Database'],
            (object)['name' => 'System Architecture', 'level' => 'Advanced', 'rating' => 88, 'category' => 'Backend'],
            (object)['name' => 'HTML5 / CSS3 / Bootstrap', 'level' => 'Advanced', 'rating' => 85, 'category' => 'Frontend'],
            (object)['name' => 'Cloud & CI/CD Pipelines', 'level' => 'Intermediate', 'rating' => 80, 'category' => 'DevOps'],
            (object)['name' => 'REST APIs & Security', 'level' => 'Expert', 'rating' => 92, 'category' => 'Architecture'],
        ]);

        $experiences = collect([
            (object)[
                'job_title' => 'Principal Software Architect',
                'employer' => 'Apex Cloud Systems',
                'city' => 'San Francisco',
                'country' => 'USA',
                'start_date' => '2021-03',
                'end_date' => null,
                'is_current' => true,
                'description' => "• Designed and deployed high-throughput microservices handling 15M+ daily requests with 99.99% availability.\n• Mentored a global engineering team of 12 engineers across distributed systems and cloud security standards.",
            ],
            (object)[
                'job_title' => 'Senior Backend Developer',
                'employer' => 'Nexus Software Labs',
                'city' => 'Austin',
                'country' => 'USA',
                'start_date' => '2018-06',
                'end_date' => '2021-02',
                'is_current' => false,
                'description' => "• Engineered automated CI/CD deployment pipelines, cutting deployment downtime by 45%.\n• Optimized database query execution times by 60% through index analysis and relational normalization.",
            ],
        ]);

        $educations = collect([
            (object)[
                'degree' => 'B.S. in Computer Science',
                'institution' => 'University of California, Berkeley',
                'field_of_study' => 'Software Engineering',
                'city' => 'Berkeley',
                'country' => 'USA',
                'start_date' => '2014-09',
                'end_date' => '2018-05',
                'is_current' => false,
                'grade_or_gpa' => '3.92 GPA',
                'description' => 'Honors Graduate, President of the Association for Computing Machinery Student Chapter.',
            ],
        ]);

        $languages = collect([
            (object)['language' => 'English', 'proficiency' => 'Native / Bilingual'],
            (object)['language' => 'German', 'proficiency' => 'Professional Working'],
        ]);

        $certifications = collect([
            (object)[
                'name' => 'AWS Certified Solutions Architect (Professional)',
                'issuing_organization' => 'Amazon Web Services',
                'issue_date' => '2023-04',
                'credential_id' => 'AWS-PSA-88412',
                'credential_url' => 'https://aws.amazon.com/verification',
                'description' => 'Advanced cloud architecture, VPC security, multi-region failover design.',
            ],
        ]);

        $projects = collect([
            (object)[
                'title' => 'Enterprise Document Workflow Engine',
                'role' => 'Lead Developer',
                'technologies' => 'Laravel, MySQL, Redis, Bootstrap 5',
                'project_url' => 'https://github.com/example/workflow-engine',
                'start_date' => '2022-01',
                'end_date' => '2023-08',
                'description' => 'Engineered high-concurrency document processing engine serving over 100,000 monthly active users.',
            ],
        ]);

        $awards = collect([
            (object)[
                'title' => 'Engineering Excellence Award',
                'issuer' => 'Apex Cloud Systems',
                'issue_date' => '2023-11',
                'description' => 'Awarded for architectural redesign of core distributed streaming infrastructure.',
            ],
        ]);

        $references = collect([
            (object)[
                'full_name' => 'Sarah Jenkins',
                'job_title' => 'VP of Engineering',
                'company' => 'Apex Cloud Systems',
                'email' => 's.jenkins@apexcloud.example',
                'phone' => '+1 (555) 019-2834',
                'relationship' => 'Former Manager',
                'is_hidden' => false,
            ],
        ]);

        $customSectionsGrouped = collect([
            'Publications & Speaking' => collect([
                (object)[
                    'section_title' => 'Publications & Speaking',
                    'title' => 'Keynote: Scaling Monoliths to Microservices',
                    'subtitle' => 'Global Cloud Summit 2023',
                    'date_period' => '2023-09',
                    'content' => 'Presented architectural strategies for zero-downtime database migration to 2,000+ attendees.',
                ],
            ]),
        ]);

        return [
            'id' => 0,
            'title' => 'Sample Professional CV',
            'summary' => 'Accomplished Software Architect with 8+ years of experience leading high-impact engineering teams, designing resilient distributed systems, and architecting scalable enterprise web applications.',
            'status' => 'published',
            'document_type' => null,
            'document_type_slug' => 'standard-cv',
            'document_type_name' => 'Standard CV',
            'is_letter' => false,
            'template_key' => $template?->key ?? self::DEFAULT_TEMPLATE_KEY,
            'template_name' => $template?->name ?? 'Classic Executive',
            'completion_percentage' => 100,

            // Customization
            'settings' => [],
            'accentColor' => '#1e293b',
            'fontFamily' => 'Inter',
            'fontSizeScale' => 'normal',
            'headingScale' => 'normal',
            'lineSpacing' => 'normal',
            'sectionSpacing' => 'normal',
            'photoSize' => 'medium',
            'showIcons' => true,
            
            // Personal Info
            'personalInfo' => (object)[
                'full_name' => 'Alexander Vance',
                'job_title' => 'Principal Software Architect',
                'email' => 'alexander.vance@example.com',
                'phone' => '+1 (555) 432-8765',
                'city' => 'San Francisco',
                'country' => 'United States',
                'location' => 'San Francisco, United States',
                'website' => 'https://alexvance.dev',
                'linkedin' => 'https://linkedin.com/in/alexvance',
                'github' => 'https://github.com/alexvance',
                'other_url' => null,
                'photo_url' => null,
            ],
            'fullName' => 'Alexander Vance',
            'jobTitle' => 'Principal Software Architect',
            'email' => 'alexander.vance@example.com',
            'phone' => '+1 (555) 432-8765',
            'location' => 'San Francisco, United States',
            'city' => 'San Francisco',
            'country' => 'United States',
            'website' => 'https://alexvance.dev',
            'linkedin' => 'https://linkedin.com/in/alexvance',
            'github' => 'https://github.com/alexvance',
            'otherUrl' => null,
            'photoUrl' => null,

            // Letter defaults
            'letterDetail' => null,
            'recipientName' => '',
            'recipientTitle' => '',
            'companyName' => '',
            'companyAddress' => '',
            'letterDate' => '',
            'subject' => '',
            'salutation' => '',
            'opening' => '',
            'body' => '',
            'callToAction' => '',
            'closing' => '',
            'senderSignature' => '',

            // Collections
            'experiences' => $experiences,
            'educations' => $educations,
            'skills' => $skills,
            'skillsByCategory' => $skills->groupBy('category'),
            'languages' => $languages,
            'certifications' => $certifications,
            'projects' => $projects,
            'awards' => $awards,
            'references' => $references,
            'customSectionsGrouped' => $customSectionsGrouped,

            // Booleans
            'hasSummary' => true,
            'hasPersonalInfo' => true,
            'hasLetterDetail' => false,
            'hasExperiences' => true,
            'hasEducations' => true,
            'hasSkills' => true,
            'hasLanguages' => true,
            'hasCertifications' => true,
            'hasProjects' => true,
            'hasAwards' => true,
            'hasReferences' => true,
            'hasCustomSections' => true,
        ];
    }
}
