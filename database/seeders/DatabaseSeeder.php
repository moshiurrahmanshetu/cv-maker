<?php

namespace Database\Seeders;

use App\Models\Cv;
use App\Models\CvAward;
use App\Models\CvCertification;
use App\Models\CvEducation;
use App\Models\CvExperience;
use App\Models\CvLanguage;
use App\Models\CvPersonalInfo;
use App\Models\CvProject;
use App\Models\CvReference;
use App\Models\CvSkill;
use App\Models\CvTemplate;
use App\Models\DocumentLetterDetail;
use App\Models\DocumentType;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database idempotently and non-destructively.
     */
    public function run(): void
    {
        // 1. Seed Document Types, Template Categories, and Templates
        $this->call(TemplateSeeder::class);

        $classicTemplate = CvTemplate::where('key', 'classic-executive')->first();
        $modernTemplate = CvTemplate::where('key', 'modern-minimal')->first();
        $techTemplate = CvTemplate::where('key', 'technical-split')->first();
        $atsTemplate = CvTemplate::where('key', 'ats-clean')->first();
        $coverClassicTemplate = CvTemplate::where('key', 'cover-letter-classic')->first();
        $motivationAcademicTemplate = CvTemplate::where('key', 'motivation-academic')->first();

        $stdCvType = DocumentType::where('slug', 'standard-cv')->first();
        $atsCvType = DocumentType::where('slug', 'ats-cv')->first();
        $coverLetterType = DocumentType::where('slug', 'cover-letter')->first();
        $motivationLetterType = DocumentType::where('slug', 'motivation-letter')->first();

        // 2. Admin User
        $admin = User::firstOrCreate(
            ['email' => 'admin@cvmaker.local'],
            [
                'name' => 'System Admin',
                'password' => Hash::make('password123'),
                'role' => 'admin',
                'email_verified_at' => now(),
            ]
        );

        // 3. Demo Normal User
        $user = User::firstOrCreate(
            ['email' => 'user@cvmaker.local'],
            [
                'name' => 'Alex Morgan',
                'password' => Hash::make('password123'),
                'role' => 'user',
                'email_verified_at' => now(),
            ]
        );

        // 4. Second Normal User (for authorization boundary testing)
        $user2 = User::firstOrCreate(
            ['email' => 'jane@cvmaker.local'],
            [
                'name' => 'Jane Doe',
                'password' => Hash::make('password123'),
                'role' => 'user',
                'email_verified_at' => now(),
            ]
        );

        // Sample 1: Published Full-Stack Standard CV for Alex Morgan
        $cv1 = Cv::firstOrCreate(
            ['slug' => 'senior-full-stack-engineer-resume'],
            [
                'user_id' => $user->id,
                'document_type_id' => $stdCvType?->id,
                'template_id' => $classicTemplate?->id,
                'title' => 'Senior Full Stack Engineer Resume',
                'summary' => 'Passionate Full Stack Software Engineer with 7+ years of experience building scalable web applications, RESTful APIs, and distributed microservices. Specialized in Laravel, MySQL, and modern frontend architectures.',
                'status' => 'published',
                'template_key' => 'classic-executive',
                'primary_color' => '#1b2a4a',
                'font_family' => 'Inter',
                'completion_percentage' => 95,
            ]
        );

        CvPersonalInfo::updateOrCreate(
            ['cv_id' => $cv1->id],
            [
                'full_name' => 'Alex Morgan',
                'job_title' => 'Senior Full Stack Engineer',
                'email' => 'alex.morgan@example.com',
                'phone' => '+1 (555) 234-5678',
                'address' => '742 Evergreen Terrace',
                'city' => 'San Francisco',
                'country' => 'United States',
                'postal_code' => '94107',
                'website' => 'https://alexmorgan.dev',
                'linkedin' => 'https://linkedin.com/in/alexmorgan',
                'github' => 'https://github.com/alexmorgan',
            ]
        );

        CvExperience::firstOrCreate(
            ['cv_id' => $cv1->id, 'job_title' => 'Lead Software Engineer', 'employer' => 'Apex Cloud Solutions'],
            [
                'city' => 'San Francisco',
                'country' => 'USA',
                'start_date' => '2021-03',
                'end_date' => null,
                'is_current' => true,
                'description' => 'Architected high-throughput cloud microservices serving 2M+ active daily requests with 99.99% uptime. Led a cross-functional team of 8 engineers in agile sprints.',
                'sort_order' => 1,
            ]
        );

        CvExperience::firstOrCreate(
            ['cv_id' => $cv1->id, 'job_title' => 'Senior Backend Developer', 'employer' => 'Nexus Software Labs'],
            [
                'city' => 'San Jose',
                'country' => 'USA',
                'start_date' => '2018-06',
                'end_date' => '2021-02',
                'is_current' => false,
                'description' => 'Engineered scalable relational database schemas and automated CI/CD pipelines reducing deployment friction by 40%.',
                'sort_order' => 2,
            ]
        );

        CvEducation::firstOrCreate(
            ['cv_id' => $cv1->id, 'institution' => 'University of California, Berkeley', 'degree' => 'Bachelor of Science'],
            [
                'field_of_study' => 'Computer Science',
                'city' => 'Berkeley',
                'country' => 'USA',
                'start_date' => '2014-09',
                'end_date' => '2018-05',
                'is_current' => false,
                'grade_or_gpa' => '3.85 GPA',
                'description' => 'Dean\'s Honor List, focus on Distributed Systems and Database Management.',
                'sort_order' => 1,
            ]
        );

        CvSkill::firstOrCreate(['cv_id' => $cv1->id, 'name' => 'Laravel / PHP'], ['level' => 'Expert', 'category' => 'Backend', 'rating' => 95, 'sort_order' => 1]);
        CvSkill::firstOrCreate(['cv_id' => $cv1->id, 'name' => 'MySQL & Database Optimization'], ['level' => 'Expert', 'category' => 'Database', 'rating' => 90, 'sort_order' => 2]);
        CvSkill::firstOrCreate(['cv_id' => $cv1->id, 'name' => 'REST APIs & Architecture'], ['level' => 'Advanced', 'category' => 'Architecture', 'rating' => 85, 'sort_order' => 3]);
        CvSkill::firstOrCreate(['cv_id' => $cv1->id, 'name' => 'JavaScript & Bootstrap 5'], ['level' => 'Advanced', 'category' => 'Frontend', 'rating' => 80, 'sort_order' => 4]);
        CvSkill::firstOrCreate(['cv_id' => $cv1->id, 'name' => 'Docker & Cloud Deployment'], ['level' => 'Intermediate', 'category' => 'DevOps', 'rating' => 75, 'sort_order' => 5]);

        CvLanguage::firstOrCreate(['cv_id' => $cv1->id, 'language' => 'English'], ['proficiency' => 'Native', 'sort_order' => 1]);
        CvLanguage::firstOrCreate(['cv_id' => $cv1->id, 'language' => 'German'], ['proficiency' => 'Professional', 'sort_order' => 2]);

        CvProject::firstOrCreate(
            ['cv_id' => $cv1->id, 'title' => 'Enterprise Document Engine'],
            [
                'role' => 'Lead Architect',
                'project_url' => 'https://github.com/alexmorgan/doc-engine',
                'start_date' => '2022-01',
                'end_date' => '2023-04',
                'description' => 'Engineered high-performance PDF and document rendering service processing over 50,000 files daily.',
                'sort_order' => 1,
            ]
        );

        CvCertification::firstOrCreate(
            ['cv_id' => $cv1->id, 'name' => 'AWS Certified Solutions Architect'],
            [
                'issuing_organization' => 'Amazon Web Services',
                'issue_date' => '2022-08',
                'credential_id' => 'AWS-PSA-994821',
                'sort_order' => 1,
            ]
        );

        // Sample 2: Incomplete Draft CV for Alex Morgan
        $cv2 = Cv::firstOrCreate(
            ['slug' => 'product-strategy-specialist-draft'],
            [
                'user_id' => $user->id,
                'document_type_id' => $stdCvType?->id,
                'template_id' => $modernTemplate?->id,
                'title' => 'Product Strategy Specialist (Draft)',
                'summary' => 'Transitioning technical background into customer-centric product management.',
                'status' => 'draft',
                'template_key' => 'modern-minimal',
                'primary_color' => '#2c3e50',
                'font_family' => 'Inter',
                'completion_percentage' => 35,
            ]
        );

        CvPersonalInfo::updateOrCreate(
            ['cv_id' => $cv2->id],
            [
                'full_name' => 'Alex Morgan',
                'job_title' => 'Product Strategy Lead',
                'email' => 'alex.pm@example.com',
                'phone' => '+1 (555) 234-5678',
            ]
        );

        // Sample 3: Cover Letter for Alex Morgan
        $coverDoc = Cv::firstOrCreate(
            ['slug' => 'google-senior-cloud-architect-cover-letter'],
            [
                'user_id' => $user->id,
                'document_type_id' => $coverLetterType?->id,
                'template_id' => $coverClassicTemplate?->id,
                'title' => 'Google - Senior Cloud Architect Cover Letter',
                'status' => 'published',
                'template_key' => 'cover-letter-classic',
                'primary_color' => '#1e293b',
                'font_family' => 'Inter',
                'completion_percentage' => 100,
            ]
        );

        CvPersonalInfo::updateOrCreate(
            ['cv_id' => $coverDoc->id],
            [
                'full_name' => 'Alex Morgan',
                'job_title' => 'Senior Full Stack Engineer',
                'email' => 'alex.morgan@example.com',
                'phone' => '+1 (555) 234-5678',
                'city' => 'San Francisco',
                'country' => 'USA',
                'linkedin' => 'https://linkedin.com/in/alexmorgan',
            ]
        );

        DocumentLetterDetail::updateOrCreate(
            ['cv_id' => $coverDoc->id],
            [
                'recipient_name' => 'Dr. Elizabeth Vance',
                'recipient_title' => 'Director of Engineering Talent',
                'company_name' => 'Google LLC',
                'company_address' => "1600 Amphitheatre Parkway\nMountain View, CA 94043",
                'letter_date' => date('F j, Y'),
                'subject' => 'Application for Senior Cloud Architect (Req #84920)',
                'salutation' => 'Dear Dr. Vance,',
                'opening' => 'I am writing to express my enthusiastic interest in the Senior Cloud Architect position at Google. With more than 7 years of background architecting resilient distributed systems and driving high-throughput cloud migrations, I am confident in my capacity to deliver meaningful impact to your engineering organization.',
                'body' => "Throughout my tenure at Apex Cloud Solutions, I led the architectural overhaul of our core streaming data pipeline, improving system uptime to 99.99% while reducing compute overhead by 35%. My leadership philosophy centers on engineering excellence, data-driven system design, and fostering inclusive, high-velocity developer teams.\n\nI have followed Google's recent advancements in distributed computing and hybrid cloud orchestration with great admiration. The opportunity to contribute to infrastructure that powers global scale technologies strongly aligns with my professional aspirations.",
                'call_to_action' => 'Thank you for your time and consideration. I would welcome the opportunity to discuss how my architectural experience and technical leadership align with the needs of your team.',
                'closing' => 'Sincerely,',
                'sender_signature' => 'Alex Morgan',
            ]
        );

        // Sample 4: Motivation Letter for Alex Morgan
        $motivationDoc = Cv::firstOrCreate(
            ['slug' => 'stanford-university-ms-ai-motivation-statement'],
            [
                'user_id' => $user->id,
                'document_type_id' => $motivationLetterType?->id,
                'template_id' => $motivationAcademicTemplate?->id,
                'title' => 'Stanford University - M.S. AI Motivation Statement',
                'status' => 'draft',
                'template_key' => 'motivation-academic',
                'primary_color' => '#8c1d40',
                'font_family' => 'Georgia',
                'completion_percentage' => 85,
            ]
        );

        CvPersonalInfo::updateOrCreate(
            ['cv_id' => $motivationDoc->id],
            [
                'full_name' => 'Alex Morgan',
                'email' => 'alex.morgan@example.com',
                'phone' => '+1 (555) 234-5678',
                'city' => 'San Francisco',
                'country' => 'USA',
            ]
        );

        DocumentLetterDetail::updateOrCreate(
            ['cv_id' => $motivationDoc->id],
            [
                'recipient_name' => 'Graduate Admissions Committee',
                'recipient_title' => 'Department of Computer Science',
                'company_name' => 'Stanford University',
                'company_address' => "450 Jane Stanford Way\nStanford, CA 94305",
                'letter_date' => date('F j, Y'),
                'subject' => 'Statement of Purpose – Master of Science in Artificial Intelligence',
                'salutation' => 'Dear Members of the Admissions Committee,',
                'opening' => 'I am applying for admission to the Master of Science in Computer Science (Artificial Intelligence specialization) program at Stanford University for the upcoming academic year.',
                'body' => "My professional journey over the past 7 years in cloud systems architecture has demonstrated to me the critical necessity for intelligent, autonomous optimization within complex distributed topologies. I aim to conduct rigorous research at Stanford focusing on neural reinforcement learning for large-scale systems optimization.\n\nHaving graduated from UC Berkeley with Honors in Computer Science (3.85 GPA), I possess the foundational rigor required for graduate research at Stanford. Studying under your esteemed faculty will enable me to bridge production engineering practice with deep research.",
                'call_to_action' => 'I look forward to the possibility of joining Stanford University and contributing meaningfully to your academic community.',
                'closing' => 'Respectfully submitted,',
                'sender_signature' => 'Alex Morgan',
            ]
        );

        // Sample 5: CV belonging to Jane Doe (for ownership isolation check)
        $cv3 = Cv::firstOrCreate(
            ['slug' => 'jane-doe-marketing-director'],
            [
                'user_id' => $user2->id,
                'document_type_id' => $stdCvType?->id,
                'template_id' => $techTemplate?->id,
                'title' => 'Jane Doe - Marketing Director',
                'summary' => 'Experienced marketing leader specializing in B2B SaaS growth and brand positioning.',
                'status' => 'published',
                'template_key' => 'technical-split',
                'completion_percentage' => 80,
            ]
        );

        CvPersonalInfo::updateOrCreate(
            ['cv_id' => $cv3->id],
            [
                'full_name' => 'Jane Doe',
                'job_title' => 'Marketing Director',
                'email' => 'jane.doe@example.com',
                'phone' => '+1 (555) 888-9999',
            ]
        );
    }
}
