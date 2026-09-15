<?php

namespace App\Services\Ai\Providers;

use App\Services\Ai\AiResponse;
use App\Services\Ai\Contracts\AiProviderInterface;

class MockAiProvider implements AiProviderInterface
{
    protected string $providerName = 'mock';
    protected string $model = 'mock-career-v1';

    public function getProviderName(): string
    {
        return $this->providerName;
    }

    public function getModel(): string
    {
        return $this->model;
    }

    public function generate(string $prompt, array $options = []): AiResponse
    {
        $feature = $options['feature'] ?? 'content_improve';
        $params = $options['params'] ?? [];
        $documentData = $options['document_data'] ?? [];

        $role = $params['target_position'] ?? $params['job_title'] ?? $documentData['job_title'] ?? 'Software Professional';
        $years = $params['years_of_experience'] ?? $params['experience_years'] ?? '5+';
        $skills = !empty($params['key_skills']) ? $params['key_skills'] : (!empty($documentData['skills']) ? implode(', ', array_slice($documentData['skills'], 0, 4)) : 'software architecture, cloud engineering, agile delivery');
        $industry = $params['industry'] ?? 'Technology';
        $specialization = $params['specialization'] ?? 'Distributed Systems';
        $userDraft = $params['draft'] ?? $params['text'] ?? $params['description'] ?? '';

        switch ($feature) {
            case 'profile_summary':
                $variants = [
                    'professional' => "Results-driven {$role} with over {$years} years of dedicated experience in {$industry}, specializing in {$specialization}. Proven track record of architecting resilient systems, leading cross-functional engineering teams, and optimizing production workflows. Adept at leveraging {$skills} to deliver scalable solutions that drive measurable business growth.",
                    'concise' => "Strategic {$role} with {$years} years of experience in {$specialization} and {$industry}. Strong proficiency across {$skills} with a focus on high availability, operational efficiency, and rapid product iteration.",
                    'modern' => "Passionate {$role} focused on building next-generation digital experiences. Combining deep expertise in {$skills} with a collaborative mindset to bridge business requirements and modern technical execution across {$industry}.",
                ];
                return AiResponse::success($variants['professional'], $variants, 180, 240, $this->providerName, $this->model, null, $feature);

            case 'career_objective':
                $variants = [
                    'professional' => "Dedicated {$role} seeking to leverage {$years} years of expertise in {$specialization} and strong proficiency in {$skills} to contribute directly to high-impact technical initiatives in {$industry}.",
                    'concise' => "Motivated professional aiming to secure a challenging {$role} position where comprehensive skills in {$skills} can be applied to foster team excellence and drive organizational milestones.",
                    'modern' => "To apply verified technical capabilities in {$skills} within a forward-thinking engineering culture, delivering robust, user-centric solutions as a {$role}.",
                ];
                return AiResponse::success($variants['professional'], $variants, 120, 150, $this->providerName, $this->model, null, $feature);

            case 'experience_rewrite':
                $mode = $params['mode'] ?? 'bullets';
                $company = $params['company'] ?? 'Engineering Team';
                $position = $params['position'] ?? $role;

                if ($mode === 'bullets') {
                    $cleaned = "• Spearheaded core feature development for {$position} deliverables, improving delivery throughput and team velocity.\n• Architected and maintained scalable services utilizing {$skills}, achieving 99.9% uptime across production environments.\n• Collaborated closely with cross-functional product stakeholders to translate requirements into robust technical specifications.\n• Mentored junior team members and championed code quality standards through comprehensive peer code reviews.";
                } elseif ($mode === 'concise') {
                    $cleaned = "Led {$position} initiatives utilizing {$skills}. Streamlined development cycles, ensured high system stability, and collaborated with cross-functional teams to deliver critical product milestones.";
                } elseif ($mode === 'professional') {
                    $baseDraft = !empty($userDraft) ? trim($userDraft) : "Worked on {$company} projects and maintained application infrastructure.";
                    $cleaned = "Spearheaded core technical initiatives at {$company} as {$position}. Enhanced platform reliability and system performance utilizing {$skills}, ensuring seamless delivery across all product cycles: {$baseDraft}";
                } else {
                    $baseDraft = !empty($userDraft) ? trim($userDraft) : "Developed and deployed critical web applications.";
                    $cleaned = "Engineered, deployed, and maintained mission-critical applications utilizing modern practices and {$skills}. Accelerated operational workflows and elevated code quality across active team repositories: {$baseDraft}";
                }

                $bullets = array_values(array_filter(array_map('trim', explode("\n", str_replace('• ', '', $cleaned)))));

                return AiResponse::success([
                    'bullets' => $bullets,
                    'text' => $cleaned,
                ], null, 140, 180, $this->providerName, $this->model, null, $feature);

            case 'project_rewrite':
                $mode = $params['mode'] ?? 'describe';
                $projectName = $params['project_name'] ?? 'Flagship Application';
                $tech = $params['technologies'] ?? $skills;

                if ($mode === 'bullets') {
                    $cleaned = "• Architected and engineered {$projectName} leveraging {$tech} to solve critical domain workflow bottlenecks.\n• Implemented secure authentication, responsive interface components, and optimized database queries.\n• Deployed automated CI/CD build pipelines, reducing regression test duration and release overhead.";
                } elseif ($mode === 'concise') {
                    $cleaned = "Developed {$projectName}, a high-performance application built with {$tech}, featuring responsive UI design and optimized database workflows.";
                } else {
                    $baseDraft = !empty($userDraft) ? trim($userDraft) : "An end-to-end full-stack web application.";
                    $cleaned = "Full-lifecycle development of {$projectName} utilizing {$tech}. Designed to deliver a responsive, performant user experience with clean architecture, robust security standards, and scalable backend infrastructure. {$baseDraft}";
                }

                $bullets = array_values(array_filter(array_map('trim', explode("\n", str_replace('• ', '', $cleaned)))));

                return AiResponse::success([
                    'bullets' => $bullets,
                    'text' => $cleaned,
                ], null, 130, 160, $this->providerName, $this->model, null, $feature);

            case 'skills_suggestion':
                $roleLower = strtolower($role);
                $technicalSkills = ['Laravel', 'Vue.js', 'MySQL', 'RESTful API Architecture', 'Git & GitHub Workflows', 'Docker', 'Redis Caching', 'TypeScript', 'Automated Testing'];
                $softSkills = ['Technical Problem Solving', 'Cross-Functional Collaboration', 'Agile / Scrum Methodologies', 'System Architecture Design', 'Code Quality & Peer Review', 'Technical Mentorship'];
                $tools = ['Linux / Unix CLI', 'CI/CD Pipelines', 'Postman / API Tools', 'Cloud Platforms (AWS / GCP)'];

                if (str_contains($roleLower, 'design') || str_contains($roleLower, 'product')) {
                    $technicalSkills = ['UI/UX Design', 'Figma & Prototyping', 'Design Systems', 'Responsive Web Design', 'HTML5 & Modern CSS3', 'User Research & Wireframing'];
                    $softSkills = ['Design Thinking', 'Stakeholder Communication', 'Usability Testing', 'Visual Hierarchy'];
                }

                $categorized = [
                    'technical' => $technicalSkills,
                    'soft' => $softSkills,
                    'tools' => $tools,
                ];

                $allSkills = array_merge($technicalSkills, $softSkills, $tools);

                return AiResponse::success([
                    'skills' => $allSkills,
                    'categorized' => $categorized,
                ], null, 90, 120, $this->providerName, $this->model, null, $feature);

            case 'content_improve':
                $tone = $params['tone'] ?? 'professional';
                $original = !empty($userDraft) ? trim($userDraft) : 'Professional with extensive experience in the industry.';

                if ($tone === 'concise') {
                    $improved = preg_replace('/\s+/', ' ', "Accomplished {$role} with expertise in {$skills}. Focused on delivering robust technical solutions and driving measurable product outcomes.");
                } elseif ($tone === 'impactful') {
                    $improved = "High-impact {$role} with a proven track record in {$skills}. Successfully engineered scalable platforms, accelerated product delivery, and elevated team standards.";
                } elseif ($tone === 'ats') {
                    $improved = "Qualified {$role} proficient in {$skills}. Experienced in full software lifecycle development, relational database optimization, agile methodologies, and cross-functional team delivery.";
                } elseif ($tone === 'grammar') {
                    $improved = ucfirst(trim($original));
                    if (!str_ends_with($improved, '.')) $improved .= '.';
                } else {
                    $improved = "Dedicated {$role} with verified hands-on expertise in {$skills}. Proven ability to design maintainable solutions, solve complex technical challenges, and collaborate effectively across modern development environments.";
                }

                return AiResponse::success([
                    'text' => $improved,
                ], null, 110, 140, $this->providerName, $this->model, null, $feature);

            case 'cover_letter':
                $recipientName = $params['recipient_name'] ?? 'Hiring Team';
                $company = $params['company_name'] ?? 'Target Organization';
                $targetRole = $params['job_title'] ?? $role;

                $salutation = "Dear {$recipientName},";
                $opening = "I am writing to express my enthusiastic interest in the {$targetRole} position at {$company}. With over {$years} years of professional experience in {$specialization} and a proven track record in {$skills}, I am excited by the opportunity to contribute directly to {$company}'s ongoing mission and technical deliverables.";
                $body = "Throughout my career, I have consistently focused on building scalable, resilient solutions and collaborating closely with cross-functional teams to exceed operational milestones. My background in {$skills} has enabled me to streamline development workflows, enhance product stability, and architect robust features that align with strategic objectives.\n\nI admire {$company}'s innovative trajectory and believe my practical experience, combined with my commitment to engineering excellence, makes me a strong fit for your team.";
                $cta = "Thank you for reviewing my application. I welcome the opportunity to discuss how my background and skill set can support {$company}'s strategic goals.";
                $closing = 'Sincerely,';
                $sig = $documentData['full_name'] ?? 'Candidate';

                $fullText = "{$salutation}\n\n{$opening}\n\n{$body}\n\n{$cta}\n\n{$closing}\n{$sig}";

                $structuredLetter = [
                    'recipient_name' => $recipientName,
                    'company_name' => $company,
                    'salutation' => $salutation,
                    'opening' => $opening,
                    'opening_paragraph' => $opening,
                    'body' => $body,
                    'body_paragraph' => $body,
                    'call_to_action' => $cta,
                    'closing' => $closing,
                    'signature' => $sig,
                    'sender_signature' => $sig,
                    'full_text' => $fullText,
                ];

                return AiResponse::success($structuredLetter, null, 250, 380, $this->providerName, $this->model, null, $feature);

            case 'motivation_letter':
                $institution = $params['company_name'] ?? 'Graduate Admissions Committee';
                $program = $params['job_title'] ?? "Master's Program";

                $salutation = 'Dear Members of the Admissions Committee,';
                $opening = "I am writing to submit my formal application for the {$program} at {$institution}. Having established a rigorous foundation in {$specialization} over {$years} years of study and practical application, I am eager to pursue advanced research and academic excellence under your esteemed faculty.";
                $body = "My academic and professional background in {$skills} has provided me with both the analytical rigor and hands-on preparation required for intensive graduate coursework. I am particularly drawn to {$institution} because of its pioneering research contributions, collaborative academic environment, and distinguished faculty leadership in {$specialization}.\n\nDuring my previous work, I have focused on solving intricate technical challenges, developing disciplined research methodologies, and engaging in collaborative problem-solving.";
                $cta = "Thank you for your time and consideration of my candidacy. I look forward to the prospect of contributing meaningfully to the academic community at {$institution}.";
                $closing = 'Respectfully submitted,';
                $sig = $documentData['full_name'] ?? 'Candidate';

                $fullText = "{$salutation}\n\n{$opening}\n\n{$body}\n\n{$cta}\n\n{$closing}\n{$sig}";

                $structuredMotivation = [
                    'recipient_name' => 'Admissions Committee',
                    'company_name' => $institution,
                    'salutation' => $salutation,
                    'opening' => $opening,
                    'opening_paragraph' => $opening,
                    'body' => $body,
                    'body_paragraph' => $body,
                    'call_to_action' => $cta,
                    'closing' => $closing,
                    'signature' => $sig,
                    'sender_signature' => $sig,
                    'full_text' => $fullText,
                ];

                return AiResponse::success($structuredMotivation, null, 250, 380, $this->providerName, $this->model, null, $feature);

            default:
                return AiResponse::success([
                    'text' => "Content tailored for {$role} utilizing {$skills}.",
                ], null, 50, 70, $this->providerName, $this->model, null, $feature);
        }
    }
}
