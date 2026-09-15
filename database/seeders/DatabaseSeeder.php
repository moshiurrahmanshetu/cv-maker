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
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // 1. Admin User
        $admin = User::firstOrCreate(
            ['email' => 'admin@cvmaker.local'],
            [
                'name' => 'System Admin',
                'password' => Hash::make('password123'),
                'role' => 'admin',
                'email_verified_at' => now(),
            ]
        );

        // 2. Demo Normal User
        $user = User::firstOrCreate(
            ['email' => 'user@cvmaker.local'],
            [
                'name' => 'Alex Morgan',
                'password' => Hash::make('password123'),
                'role' => 'user',
                'email_verified_at' => now(),
            ]
        );

        // 3. Second Normal User (for authorization boundary testing)
        $user2 = User::firstOrCreate(
            ['email' => 'jane@cvmaker.local'],
            [
                'name' => 'Jane Doe',
                'password' => Hash::make('password123'),
                'role' => 'user',
                'email_verified_at' => now(),
            ]
        );

        // Sample 1: Published Full-Stack CV for Alex Morgan
        $cv1 = Cv::create([
            'user_id' => $user->id,
            'title' => 'Senior Full Stack Engineer Resume',
            'slug' => 'senior-full-stack-engineer-resume',
            'summary' => 'Passionate Full Stack Software Engineer with 7+ years of experience building scalable web applications, RESTful APIs, and distributed microservices. Specialized in Laravel, MySQL, and modern frontend architectures.',
            'status' => 'published',
            'template_key' => 'classic',
            'primary_color' => '#1b2a4a',
            'font_family' => 'Inter',
            'completion_percentage' => 95,
        ]);

        CvPersonalInfo::create([
            'cv_id' => $cv1->id,
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
        ]);

        CvExperience::create([
            'cv_id' => $cv1->id,
            'job_title' => 'Lead Software Engineer',
            'employer' => 'Apex Cloud Solutions',
            'city' => 'San Francisco',
            'country' => 'USA',
            'start_date' => '2021-03',
            'end_date' => null,
            'is_current' => true,
            'description' => 'Architected high-throughput cloud microservices serving 2M+ active daily requests with 99.99% uptime. Led a cross-functional team of 8 engineers in agile sprints.',
            'sort_order' => 1,
        ]);

        CvExperience::create([
            'cv_id' => $cv1->id,
            'job_title' => 'Senior Backend Developer',
            'employer' => 'Nexus Software Labs',
            'city' => 'San Jose',
            'country' => 'USA',
            'start_date' => '2018-06',
            'end_date' => '2021-02',
            'is_current' => false,
            'description' => 'Engineered scalable relational database schemas and automated CI/CD pipelines reducing deployment friction by 40%.',
            'sort_order' => 2,
        ]);

        CvEducation::create([
            'cv_id' => $cv1->id,
            'institution' => 'University of California, Berkeley',
            'degree' => 'Bachelor of Science',
            'field_of_study' => 'Computer Science',
            'city' => 'Berkeley',
            'country' => 'USA',
            'start_date' => '2014-09',
            'end_date' => '2018-05',
            'is_current' => false,
            'grade_or_gpa' => '3.85 GPA',
            'description' => 'Dean\'s Honor List, focus on Distributed Systems and Database Management.',
            'sort_order' => 1,
        ]);

        CvSkill::create(['cv_id' => $cv1->id, 'name' => 'Laravel / PHP', 'level' => 'Expert', 'category' => 'Backend', 'sort_order' => 1]);
        CvSkill::create(['cv_id' => $cv1->id, 'name' => 'MySQL & Database Optimization', 'level' => 'Expert', 'category' => 'Database', 'sort_order' => 2]);
        CvSkill::create(['cv_id' => $cv1->id, 'name' => 'REST APIs & Architecture', 'level' => 'Advanced', 'category' => 'Architecture', 'sort_order' => 3]);
        CvSkill::create(['cv_id' => $cv1->id, 'name' => 'JavaScript & Bootstrap 5', 'level' => 'Advanced', 'category' => 'Frontend', 'sort_order' => 4]);
        CvSkill::create(['cv_id' => $cv1->id, 'name' => 'Docker & Cloud Deployment', 'level' => 'Intermediate', 'category' => 'DevOps', 'sort_order' => 5]);

        CvLanguage::create(['cv_id' => $cv1->id, 'language' => 'English', 'proficiency' => 'Native', 'sort_order' => 1]);
        CvLanguage::create(['cv_id' => $cv1->id, 'language' => 'German', 'proficiency' => 'Professional', 'sort_order' => 2]);

        CvProject::create([
            'cv_id' => $cv1->id,
            'title' => 'Enterprise Document Engine',
            'role' => 'Lead Architect',
            'project_url' => 'https://github.com/alexmorgan/doc-engine',
            'start_date' => '2022-01',
            'end_date' => '2023-04',
            'description' => 'Engineered high-performance PDF and document rendering service processing over 50,000 files daily.',
            'sort_order' => 1,
        ]);

        CvCertification::create([
            'cv_id' => $cv1->id,
            'name' => 'AWS Certified Solutions Architect',
            'issuing_organization' => 'Amazon Web Services',
            'issue_date' => '2022-08',
            'credential_id' => 'AWS-PSA-994821',
            'sort_order' => 1,
        ]);

        // Sample 2: Incomplete Draft CV for Alex Morgan
        $cv2 = Cv::create([
            'user_id' => $user->id,
            'title' => 'Product Strategy Specialist (Draft)',
            'slug' => 'product-strategy-specialist-draft',
            'summary' => 'Transitioning technical background into customer-centric product management.',
            'status' => 'draft',
            'template_key' => 'modern',
            'primary_color' => '#2c3e50',
            'font_family' => 'Inter',
            'completion_percentage' => 35,
        ]);

        CvPersonalInfo::create([
            'cv_id' => $cv2->id,
            'full_name' => 'Alex Morgan',
            'job_title' => 'Product Strategy Lead',
            'email' => 'alex.pm@example.com',
            'phone' => '+1 (555) 234-5678',
        ]);

        // Sample 3: CV belonging to Jane Doe (for ownership isolation check)
        $cv3 = Cv::create([
            'user_id' => $user2->id,
            'title' => 'Jane Doe - Marketing Director',
            'slug' => 'jane-doe-marketing-director',
            'summary' => 'Experienced marketing leader specializing in B2B SaaS growth and brand positioning.',
            'status' => 'published',
            'template_key' => 'classic',
            'completion_percentage' => 80,
        ]);

        CvPersonalInfo::create([
            'cv_id' => $cv3->id,
            'full_name' => 'Jane Doe',
            'job_title' => 'Marketing Director',
            'email' => 'jane.doe@example.com',
            'phone' => '+1 (555) 888-9999',
        ]);
    }
}
