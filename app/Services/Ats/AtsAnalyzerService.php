<?php

namespace App\Services\Ats;

use App\Models\Cv;
use App\Models\AtsAnalysis;

class AtsAnalyzerService
{
    protected JobDescriptionParserService $jobParser;
    protected KeywordMatcherService $keywordMatcher;

    /**
     * Action verbs list commonly favored by ATS and executive recruiters.
     */
    protected array $actionVerbs = [
        'achieved', 'accelerated', 'administered', 'analyzed', 'architected', 'automated',
        'budgeted', 'built', 'centralized', 'championed', 'collaborated', 'composed',
        'constructed', 'created', 'customized', 'decreased', 'delivered', 'deployed',
        'designed', 'developed', 'devised', 'directed', 'doubled', 'drafted',
        'eliminated', 'engineered', 'enhanced', 'established', 'evaluated', 'executed',
        'expanded', 'expedited', 'formulated', 'generated', 'guided', 'headed',
        'implemented', 'improved', 'increased', 'initiated', 'inspected', 'instituted',
        'integrated', 'introduced', 'invented', 'launched', 'lead', 'led', 'managed',
        'maximized', 'mentored', 'migrated', 'minimized', 'modernized', 'negotiated',
        'optimized', 'orchestrated', 'organized', 'overhauled', 'oversaw', 'performed',
        'pioneered', 'planned', 'produced', 'programmed', 'projected', 'promoted',
        'reduced', 'refactored', 'reorganized', 'resolved', 'restructured', 'revamped',
        'scaled', 'secured', 'simplified', 'spearheaded', 'standardized', 'streamlined',
        'strengthened', 'supervised', 'surpassed', 'trained', 'transformed', 'upgraded',
    ];

    /**
     * Passive or weak phrases to avoid in high-impact resumes.
     */
    protected array $passivePhrases = [
        'responsible for', 'duties included', 'worked on', 'helped with', 'assisted with',
        'tasked with', 'handled', 'participated in', 'attempted to', 'served as',
    ];

    public function __construct(
        JobDescriptionParserService $jobParser,
        KeywordMatcherService $keywordMatcher
    ) {
        $this->jobParser = $jobParser;
        $this->keywordMatcher = $keywordMatcher;
    }

    /**
     * Analyze a CV model against ATS rules and optional Job Description.
     *
     * @param Cv $cv
     * @param string|null $jobDescription
     * @return AtsAnalysis
     */
    public function analyze(Cv $cv, ?string $jobDescription = null): AtsAnalysis
    {
        // Eager load relationships if not loaded
        $cv->load([
            'documentType',
            'personalInfo',
            'experiences',
            'educations',
            'skills',
            'certifications',
            'projects',
            'languages',
            'awards',
            'customSections',
            'letterDetail',
        ]);

        $rawDocType = $cv->documentType?->slug ?? ($cv->isLetter() ? 'cover-letter' : 'standard-cv');
        if ($cv->isLetter()) {
            $docType = 'cover_letter';
        } else {
            $docType = match ($rawDocType) {
                'cover-letter', 'cover_letter' => 'cover_letter',
                'motivation-letter', 'motivation_letter' => 'motivation_letter',
                'usa-resume', 'resume-usa', 'resume_usa' => 'resume_usa',
                'ats-cv', 'cv-ats', 'cv_ats' => 'cv_ats',
                default => 'cv_standard',
            };
        }

        // 1. Structure Score (Max 20)
        $structure = $this->evaluateStructure($cv, $docType);

        // 2. Contact Information Score (Max 15)
        $contact = $this->evaluateContactInfo($cv);

        // 3. Content Impact Score (Max 25)
        $content = $this->evaluateContentImpact($cv);

        // 4. Skills Score (Max 20)
        $skills = $this->evaluateSkills($cv, $docType);

        // 5. Completeness Score (Max 20)
        $completeness = $this->evaluateCompleteness($cv, $docType);

        // Aggregate overall score (0 - 100)
        $overallScore = $structure['score'] + $contact['score'] + $content['score'] + $skills['score'] + $completeness['score'];
        $overallScore = min(100, max(0, (int) round($overallScore)));

        // Combine Strengths, Issues, and Suggestions
        $strengths = array_merge(
            $structure['strengths'],
            $contact['strengths'],
            $content['strengths'],
            $skills['strengths'],
            $completeness['strengths']
        );

        $issues = array_merge(
            $structure['issues'],
            $contact['issues'],
            $content['issues'],
            $skills['issues'],
            $completeness['issues']
        );

        $suggestions = array_merge(
            $structure['suggestions'],
            $contact['suggestions'],
            $content['suggestions'],
            $skills['suggestions'],
            $completeness['suggestions']
        );

        // Calculate detailed metrics
        $metrics = $this->calculateMetrics($cv, $content, $skills);

        // Optional Job Matching
        $jobMatchScore = null;
        $matchedKeywords = [];
        $missingKeywords = [];

        if (!empty($jobDescription) && strlen(trim($jobDescription)) >= 10) {
            $parsedJob = $this->jobParser->parse($jobDescription);
            $matchResult = $this->keywordMatcher->match($cv, $parsedJob['keywords'], $parsedJob['frequencies']);

            $jobMatchScore = $matchResult['match_percentage'];
            $matchedKeywords = $matchResult['matched'];
            $missingKeywords = $matchResult['missing'];
            $suggestions = array_merge($suggestions, $matchResult['suggestions']);
        }

        // Create and persist the ATS Analysis record
        $analysis = AtsAnalysis::create([
            'cv_id' => $cv->id,
            'user_id' => $cv->user_id,
            'overall_score' => $overallScore,
            'structure_score' => $structure['score'],
            'content_score' => $content['score'],
            'skills_score' => $skills['score'],
            'completeness_score' => $completeness['score'],
            'formatting_score' => $contact['score'],
            'match_score' => $jobMatchScore,
            'job_description' => $jobDescription,
            'strengths' => array_values(array_unique($strengths)),
            'issues' => $issues,
            'suggestions' => array_values(array_unique($suggestions)),
            'matched_keywords' => $matchedKeywords,
            'missing_keywords' => $missingKeywords,
            'metrics' => $metrics,
        ]);

        return $analysis;
    }

    /**
     * 1. Evaluate Structure & ATS Formatting (Max 20 pts).
     */
    protected function evaluateStructure(Cv $cv, string $docType): array
    {
        $score = 20;
        $strengths = [];
        $issues = [];
        $suggestions = [];

        $hasSummary = !empty($cv->summary) || !empty($cv->personalInfo?->bio);
        $hasLetterBody = $hasSummary || !empty($cv->letterDetail?->body) || !empty($cv->letterDetail?->opening);
        $hasExperience = $cv->experiences->isNotEmpty();
        $hasEducation = $cv->educations->isNotEmpty();
        $hasSkills = $cv->skills->isNotEmpty();

        if ($docType === 'cover_letter' || $docType === 'motivation_letter') {
            if ($hasLetterBody) {
                $strengths[] = 'Includes clear letter body and purpose statement.';
            } else {
                $score -= 8;
                $issues[] = ['severity' => 'High', 'category' => 'Structure', 'message' => 'Letter body content is empty.'];
                $suggestions[] = 'Add opening, background narrative, and closing paragraphs to your letter.';
            }
            return [
                'score' => max(0, min(20, $score)),
                'strengths' => $strengths,
                'issues' => $issues,
                'suggestions' => $suggestions,
            ];
        }

        // Section headings checks for CVs/Resumes
        $missingCoreSections = [];
        if (!$hasExperience) $missingCoreSections[] = 'Work Experience';
        if (!$hasEducation) $missingCoreSections[] = 'Education';
        if (!$hasSkills) $missingCoreSections[] = 'Skills';

        if (empty($missingCoreSections)) {
            $strengths[] = 'Clear, standard section organization (Experience, Education, and Skills present).';
        } else {
            $score -= (count($missingCoreSections) * 4);
            $issues[] = [
                'severity' => 'High',
                'category' => 'Structure',
                'message' => 'Missing standard sections: ' . implode(', ', $missingCoreSections) . '.',
            ];
            $suggestions[] = 'Add standard headings for ' . implode(', ', $missingCoreSections) . ' to ensure ATS parsers can categorize your data.';
        }

        if ($hasSummary) {
            $strengths[] = 'Professional profile summary section is present.';
        } else {
            $score -= 3;
            $issues[] = ['severity' => 'Medium', 'category' => 'Structure', 'message' => 'Missing professional summary / bio statement.'];
            $suggestions[] = 'Add a concise 3-4 sentence summary highlighting your core expertise and value proposition.';
        }

        // Template ATS check
        if (in_array($docType, ['cv_ats', 'resume_usa'])) {
            $strengths[] = 'Document is configured with a high-compatibility ATS-optimized layout.';
        }

        return [
            'score' => max(0, min(20, $score)),
            'strengths' => $strengths,
            'issues' => $issues,
            'suggestions' => $suggestions,
        ];
    }

    /**
     * 2. Evaluate Contact Information Completeness (Max 15 pts).
     */
    protected function evaluateContactInfo(Cv $cv): array
    {
        $score = 0;
        $strengths = [];
        $issues = [];
        $suggestions = [];

        $personal = $cv->personalInfo;

        if (!$personal) {
            return [
                'score' => 0,
                'strengths' => [],
                'issues' => [['severity' => 'High', 'category' => 'Contact Info', 'message' => 'No personal or contact information provided.']],
                'suggestions' => ['Add your full name, email address, phone number, and location.'],
            ];
        }

        // Candidate Name (+3)
        $name = trim($personal->full_name ?? ($personal->first_name . ' ' . $personal->last_name));
        if (!empty($name) && strlen($name) >= 3) {
            $score += 3;
            $strengths[] = 'Full candidate name provided.';
        } else {
            $issues[] = ['severity' => 'High', 'category' => 'Contact Info', 'message' => 'Incomplete candidate name.'];
            $suggestions[] = 'Ensure your full name is specified.';
        }

        // Professional Email (+4)
        if (!empty($personal->email) && filter_var($personal->email, FILTER_VALIDATE_EMAIL)) {
            $score += 4;
            $strengths[] = 'Valid email address detected.';
        } else {
            $issues[] = ['severity' => 'High', 'category' => 'Contact Info', 'message' => 'Missing or invalid email address.'];
            $suggestions[] = 'Provide a valid, professional email address for recruiter outreach.';
        }

        // Phone Number (+3)
        if (!empty($personal->phone) && strlen(trim($personal->phone)) >= 7) {
            $score += 3;
            $strengths[] = 'Phone number is present.';
        } else {
            $issues[] = ['severity' => 'Medium', 'category' => 'Contact Info', 'message' => 'Missing phone number.'];
            $suggestions[] = 'Add a direct phone number with country/area code.';
        }

        // Location / City / Country (+3)
        if (!empty($personal->city) || !empty($personal->country) || !empty($personal->address)) {
            $score += 3;
            $strengths[] = 'Location information (City/Country) is provided.';
        } else {
            $issues[] = ['severity' => 'Medium', 'category' => 'Contact Info', 'message' => 'Missing geographic location (City / State / Country).'];
            $suggestions[] = 'Specify your city and country so location filters do not filter out your profile.';
        }

        // Professional Link (LinkedIn, GitHub, Website) (+2)
        if (!empty($personal->linkedin) || !empty($personal->github) || !empty($personal->website)) {
            $score += 2;
            $strengths[] = 'Includes professional web / social profile links.';
        } else {
            $issues[] = ['severity' => 'Low', 'category' => 'Contact Info', 'message' => 'No LinkedIn, GitHub, or portfolio website URL included.'];
            $suggestions[] = 'Add your LinkedIn profile URL or portfolio link to build recruiter trust.';
        }

        return [
            'score' => min(15, $score),
            'strengths' => $strengths,
            'issues' => $issues,
            'suggestions' => $suggestions,
        ];
    }

    /**
     * 3. Evaluate Content Impact, Action Verbs & Metrics (Max 25 pts).
     */
    protected function evaluateContentImpact(Cv $cv): array
    {
        $score = 25;
        $strengths = [];
        $issues = [];
        $suggestions = [];

        // Collect all descriptive text blocks
        $textBlocks = [];
        if (!empty($cv->summary)) {
            $textBlocks[] = $cv->summary;
        }
        if ($cv->letterDetail) {
            if (!empty($cv->letterDetail->opening)) $textBlocks[] = $cv->letterDetail->opening;
            if (!empty($cv->letterDetail->body)) $textBlocks[] = $cv->letterDetail->body;
            if (!empty($cv->letterDetail->call_to_action)) $textBlocks[] = $cv->letterDetail->call_to_action;
        }
        foreach ($cv->experiences as $exp) {
            if (!empty($exp->description)) {
                $textBlocks[] = $exp->description;
            }
        }
        foreach ($cv->projects as $proj) {
            if (!empty($proj->description)) {
                $textBlocks[] = $proj->description;
            }
        }

        $allText = implode(' ', $textBlocks);
        $totalWords = str_word_count($allText);

        if ($totalWords < 5) {
            return [
                'score' => 0,
                'strengths' => [],
                'issues' => [['severity' => 'High', 'category' => 'Content Impact', 'message' => 'Very little descriptive content found in work experience or summary.']],
                'suggestions' => ['Write detailed bullet points explaining your responsibilities, achievements, and impact.'],
                'action_verbs_found' => [],
                'metrics_found' => [],
                'passive_found' => [],
            ];
        }

        // Count Action Verbs
        $foundActionVerbs = [];
        foreach ($this->actionVerbs as $verb) {
            if (preg_match('/\b' . preg_quote($verb, '/') . '\b/i', $allText)) {
                $foundActionVerbs[] = $verb;
            }
        }

        $verbCount = count($foundActionVerbs);
        if ($verbCount >= 6) {
            $strengths[] = "Strong action-oriented phrasing used ({$verbCount} distinct action verbs detected).";
        } elseif ($verbCount >= 3) {
            $strengths[] = "Contains {$verbCount} strong action verbs.";
            $score -= 3;
            $suggestions[] = 'Start more bullet points with impactful verbs like Spearheaded, Engineered, Orchestrated, or Reduced.';
        } elseif ($verbCount >= 1) {
            $score -= 6;
            $suggestions[] = 'Replace generic sentences with action-driven accomplishment bullets.';
        } else {
            $score -= 10;
            $issues[] = ['severity' => 'Medium', 'category' => 'Content Impact', 'message' => 'Limited use of action verbs in experience statements.'];
            $suggestions[] = 'Replace generic sentences with action-driven accomplishment bullets.';
        }

        // Count Quantifiable Metrics (Percentages, Dollars, Numbers with multipliers, e.g. 20%, $100k, 5x, 500+)
        $metricMatches = [];
        preg_match_all('/(\$\d+(?:\.\d+)?(?:k|m|b)?|\d+(?:\.\d+)?%|\b\d+x\b|\b\d{2,}\+?\b(?:\s+(?:users|clients|projects|team members|requests|ms|seconds|revenue|savings|downloads))?)/i', $allText, $metricMatches);
        $foundMetrics = array_unique($metricMatches[0] ?? []);
        $metricCount = count($foundMetrics);

        if ($metricCount >= 4) {
            $strengths[] = "Excellent quantifiable proof of impact ({$metricCount} metrics / figures detected).";
        } elseif ($metricCount >= 1) {
            $strengths[] = "Contains quantifiable data points ({$metricCount} figures detected).";
            $score -= 3;
            $suggestions[] = 'Add more measurable results (e.g., "% reduction in loading time", "$ revenue increased", "team size managed").';
        } else {
            $score -= 7;
            $issues[] = ['severity' => 'Medium', 'category' => 'Content Impact', 'message' => 'No quantifiable metrics or numerical results detected.'];
            $suggestions[] = 'Back up your accomplishments with concrete figures (e.g., percentages, project scale, performance improvements).';
        }

        // Check for passive / weak phrases
        $foundPassive = [];
        foreach ($this->passivePhrases as $passive) {
            if (preg_match('/\b' . preg_quote($passive, '/') . '\b/i', $allText)) {
                $foundPassive[] = $passive;
            }
        }

        if (!empty($foundPassive)) {
            $score -= min(6, count($foundPassive) * 2);
            $issues[] = [
                'severity' => 'Low',
                'category' => 'Content Impact',
                'message' => 'Found passive or generic phrasing: "' . implode('", "', $foundPassive) . '".',
            ];
            $suggestions[] = 'Replace passive phrases like "Responsible for" with direct achievement verbs like "Led", "Executed", or "Designed".';
        }

        return [
            'score' => max(0, min(25, $score)),
            'strengths' => $strengths,
            'issues' => $issues,
            'suggestions' => $suggestions,
            'action_verbs_found' => $foundActionVerbs,
            'metrics_found' => $foundMetrics,
            'passive_found' => $foundPassive,
        ];
    }

    /**
     * 4. Evaluate Skills Density & Categorization (Max 20 pts).
     */
    protected function evaluateSkills(Cv $cv, string $docType): array
    {
        $score = 20;
        $strengths = [];
        $issues = [];
        $suggestions = [];

        if ($docType === 'cover_letter' || $docType === 'motivation_letter') {
            return [
                'score' => 20,
                'strengths' => ['Skills evaluated within cover letter context.'],
                'issues' => [],
                'suggestions' => [],
            ];
        }

        $skillCount = $cv->skills->count();

        if ($skillCount === 0) {
            return [
                'score' => 0,
                'strengths' => [],
                'issues' => [['severity' => 'High', 'category' => 'Skills', 'message' => 'No skills listed in the document.']],
                'suggestions' => ['Add 6-15 core technical, tool, and industry skills relevant to your target role.'],
            ];
        }

        if ($skillCount >= 6 && $skillCount <= 25) {
            $strengths[] = "Healthy skill density ({$skillCount} skills specified).";
        } elseif ($skillCount < 6) {
            $score -= (6 - $skillCount) * 2;
            $issues[] = ['severity' => 'Medium', 'category' => 'Skills', 'message' => "Only {$skillCount} skills listed. Most ATS systems look for 8-20 relevant keywords."];
            $suggestions[] = 'List additional technologies, frameworks, libraries, and tools you have worked with.';
        } else {
            // Over 25 skills
            $score -= 3;
            $issues[] = ['severity' => 'Low', 'category' => 'Skills', 'message' => "High number of skills listed ({$skillCount}). May dilute key strengths."];
            $suggestions[] = 'Focus your skills list on the most relevant technologies for your target position.';
        }

        // Check if categorized
        $categories = $cv->skills->pluck('category')->filter()->unique();
        if ($categories->count() >= 2) {
            $strengths[] = 'Skills are categorized into structured groups.';
        }

        return [
            'score' => max(0, min(20, $score)),
            'strengths' => $strengths,
            'issues' => $issues,
            'suggestions' => $suggestions,
        ];
    }

    /**
     * 5. Evaluate Document Completeness & Optimal Length (Max 20 pts).
     */
    protected function evaluateCompleteness(Cv $cv, string $docType): array
    {
        $score = 20;
        $strengths = [];
        $issues = [];
        $suggestions = [];

        // Calculate total word count
        $fullText = $this->collectAllText($cv);
        $wordCount = str_word_count($fullText);

        if ($docType === 'resume_usa') {
            // USA Resume: optimal 250 - 750 words (1 page standard)
            if ($wordCount >= 250 && $wordCount <= 800) {
                $strengths[] = "Optimal concise length for a USA 1-page Resume ({$wordCount} words).";
            } elseif ($wordCount < 250) {
                $score -= 6;
                $issues[] = ['severity' => 'Medium', 'category' => 'Completeness', 'message' => "Document is very brief ({$wordCount} words)."];
                $suggestions[] = 'Expand your experience descriptions with more detail regarding your key contributions.';
            } else {
                $score -= 4;
                $issues[] = ['severity' => 'Low', 'category' => 'Completeness', 'message' => "Word count ({$wordCount} words) may exceed standard 1-page resume length."];
                $suggestions[] = 'Tighten phrasing to ensure content fits cleanly onto 1-2 pages.';
            }
        } elseif ($docType === 'cover_letter' || $docType === 'motivation_letter') {
            if ($wordCount >= 50 && $wordCount <= 500) {
                $strengths[] = "Ideal letter length ({$wordCount} words).";
            } elseif ($wordCount < 50) {
                $score -= 8;
                $issues[] = ['severity' => 'High', 'category' => 'Completeness', 'message' => 'Cover letter is too short to convey full qualifications.'];
                $suggestions[] = 'Add 1-2 paragraphs elaborating on specific project experiences matching the target role.';
            } else {
                $score -= 4;
                $issues[] = ['severity' => 'Low', 'category' => 'Completeness', 'message' => 'Cover letter is unusually long. Recruiters prefer concise 1-page letters.'];
            }
        } else {
            // Standard / ATS CV: 300 - 1400 words
            if ($wordCount >= 300 && $wordCount <= 1400) {
                $strengths[] = "Optimal document length for standard ATS parsing ({$wordCount} words).";
            } elseif ($wordCount < 300) {
                $score -= 7;
                $issues[] = ['severity' => 'Medium', 'category' => 'Completeness', 'message' => "CV is relatively short ({$wordCount} words)."];
                $suggestions[] = 'Add more detailed bullet points across your work experiences and project entries.';
            }
        }

        // Check Work Experience quality for CVs
        if (!in_array($docType, ['cover_letter', 'motivation_letter'])) {
            foreach ($cv->experiences as $index => $exp) {
                if (empty($exp->job_title) || (empty($exp->company) && empty($exp->employer))) {
                    $score -= 3;
                    $issues[] = ['severity' => 'High', 'category' => 'Completeness', 'message' => 'Work experience entry is missing job title or company name.'];
                    $suggestions[] = 'Ensure every experience entry has both job title and employer name clearly stated.';
                    break;
                }
                if (empty($exp->start_date)) {
                    $score -= 2;
                    $issues[] = ['severity' => 'Medium', 'category' => 'Completeness', 'message' => 'Work experience entry is missing start date.'];
                    $suggestions[] = 'Include start and end dates for all employment history to establish timeline credibility.';
                    break;
                }
            }

            // Check Education quality for CVs
            foreach ($cv->educations as $edu) {
                if (empty($edu->degree) || empty($edu->institution)) {
                    $score -= 2;
                    $issues[] = ['severity' => 'Medium', 'category' => 'Completeness', 'message' => 'Education entry is missing degree or institution name.'];
                    break;
                }
            }
        }

        return [
            'score' => max(0, min(20, $score)),
            'strengths' => $strengths,
            'issues' => $issues,
            'suggestions' => $suggestions,
        ];
    }

    /**
     * Calculate comprehensive quantitative metrics for the document.
     */
    protected function calculateMetrics(Cv $cv, array $contentEval, array $skillsEval): array
    {
        $allText = $this->collectAllText($cv);
        $wordCount = str_word_count($allText);
        $charCount = strlen($allText);
        $readingTime = max(1, (int) ceil($wordCount / 200));

        return [
            'word_count' => $wordCount,
            'character_count' => $charCount,
            'reading_time_minutes' => $readingTime,
            'action_verbs_count' => count($contentEval['action_verbs_found'] ?? []),
            'metrics_count' => count($contentEval['metrics_found'] ?? []),
            'passive_phrases_count' => count($contentEval['passive_found'] ?? []),
            'skills_count' => $cv->skills->count(),
            'experience_count' => $cv->experiences->count(),
            'education_count' => $cv->educations->count(),
            'projects_count' => $cv->projects->count(),
            'certifications_count' => $cv->certifications->count(),
        ];
    }

    /**
     * Collect all textual content from the CV.
     */
    protected function collectAllText(Cv $cv): string
    {
        $sections = $this->keywordMatcher->extractSectionTexts($cv);
        return implode(' ', $sections);
    }
}
