<?php

namespace App\Services\Ats;

use App\Models\Cv;

class KeywordMatcherService
{
    /**
     * Synonym mappings to normalize equivalent skill expressions.
     */
    protected array $synonyms = [
        'react' => ['react', 'react.js', 'reactjs'],
        'vue' => ['vue', 'vue.js', 'vuejs'],
        'node' => ['node', 'node.js', 'nodejs'],
        'javascript' => ['javascript', 'js', 'es6', 'ecmascript'],
        'typescript' => ['typescript', 'ts'],
        'golang' => ['golang', 'go'],
        'postgresql' => ['postgresql', 'postgres', 'pgsql'],
        'kubernetes' => ['kubernetes', 'k8s'],
        'aws' => ['aws', 'amazon web services'],
        'gcp' => ['gcp', 'google cloud', 'google cloud platform'],
        'ci/cd' => ['ci/cd', 'cicd', 'continuous integration', 'continuous deployment'],
        'rest' => ['rest', 'restful', 'restful api', 'rest api', 'rest apis'],
        'oop' => ['oop', 'object oriented', 'object-oriented programming', 'object oriented programming'],
        'tdd' => ['tdd', 'test driven development', 'test-driven development'],
    ];

    /**
     * Match target keywords against a CV's structured contents.
     *
     * @param Cv $cv
     * @param array $targetKeywords
     * @param array $keywordFrequencies
     * @return array
     */
    public function match(Cv $cv, array $targetKeywords, array $keywordFrequencies = []): array
    {
        if (empty($targetKeywords)) {
            return [
                'match_percentage' => 100,
                'matched' => [],
                'missing' => [],
                'matched_count' => 0,
                'total_count' => 0,
                'suggestions' => [],
            ];
        }

        $sectionTexts = $this->extractSectionTexts($cv);
        $matched = [];
        $missing = [];

        foreach ($targetKeywords as $keyword) {
            $keyword = trim($keyword);
            if (empty($keyword)) {
                continue;
            }

            $foundSections = [];
            $totalFoundCount = 0;

            foreach ($sectionTexts as $sectionName => $text) {
                $count = $this->findKeywordMatches($text, $keyword);
                if ($count > 0) {
                    $foundSections[] = $sectionName;
                    $totalFoundCount += $count;
                }
            }

            if (!empty($foundSections)) {
                $matched[] = [
                    'keyword' => $keyword,
                    'found_in' => $foundSections,
                    'occurrences' => $totalFoundCount,
                ];
            } else {
                $freq = $keywordFrequencies[$keyword] ?? 1;
                $priority = $freq >= 2 ? 'High' : 'Medium';

                $missing[] = [
                    'keyword' => $keyword,
                    'priority' => $priority,
                    'occurrences_in_job' => $freq,
                    'placement_hint' => $this->generatePlacementHint($keyword, $cv),
                ];
            }
        }

        $total = count($targetKeywords);
        $matchedCount = count($matched);
        $matchPercentage = $total > 0 ? (int) round(($matchedCount / $total) * 100) : 100;

        // Generate placement suggestions for missing keywords
        $suggestions = $this->generateSuggestions($missing);

        return [
            'match_percentage' => min(100, max(0, $matchPercentage)),
            'matched' => $matched,
            'missing' => $missing,
            'matched_count' => $matchedCount,
            'total_count' => $total,
            'suggestions' => $suggestions,
        ];
    }

    /**
     * Extract full concatenated text per section from the CV model.
     */
    public function extractSectionTexts(Cv $cv): array
    {
        $texts = [];

        // 1. Personal Info & Summary
        $personal = $cv->personalInfo;
        $personalText = '';
        if ($personal) {
            $personalText .= ($personal->first_name . ' ' . $personal->last_name . ' ');
            $personalText .= ($personal->job_title ?? '') . ' ';
            $personalText .= ($personal->bio ?? '') . ' ';
        }
        $texts['Personal Profile'] = $personalText;

        // 2. Letter Details (for Cover Letters / Motivation Letters)
        if ($cv->relationLoaded('letterDetail') && $cv->letterDetail) {
            $letterText = ($cv->letterDetail->subject ?? '') . ' ' .
                          ($cv->letterDetail->opening ?? '') . ' ' .
                          ($cv->letterDetail->body ?? '') . ' ' .
                          ($cv->letterDetail->call_to_action ?? '');
            if (!empty(trim($letterText))) {
                $texts['Letter Content'] = $letterText;
            }
        }

        // 3. Skills
        $skillsText = '';
        if ($cv->relationLoaded('skills') || $cv->skills()->exists()) {
            foreach ($cv->skills as $skill) {
                $skillsText .= ($skill->name ?? '') . ' ' . ($skill->category ?? '') . ' ';
            }
        }
        $texts['Skills'] = $skillsText;

        // 4. Work Experience
        $experienceText = '';
        if ($cv->relationLoaded('experiences') || $cv->experiences()->exists()) {
            foreach ($cv->experiences as $exp) {
                $experienceText .= ($exp->job_title ?? '') . ' ';
                $experienceText .= ($exp->company ?? '') . ' ';
                $experienceText .= ($exp->description ?? '') . ' ';
            }
        }
        $texts['Work Experience'] = $experienceText;

        // 5. Education
        $eduText = '';
        if ($cv->relationLoaded('educations') || $cv->educations()->exists()) {
            foreach ($cv->educations as $edu) {
                $eduText .= ($edu->degree ?? '') . ' ';
                $eduText .= ($edu->institution ?? '') . ' ';
                $eduText .= ($edu->field_of_study ?? '') . ' ';
                $eduText .= ($edu->description ?? '') . ' ';
            }
        }
        $texts['Education'] = $eduText;

        // 6. Certifications
        $certText = '';
        if ($cv->relationLoaded('certifications') || $cv->certifications()->exists()) {
            foreach ($cv->certifications as $cert) {
                $certText .= ($cert->name ?? '') . ' ' . ($cert->issuer ?? '') . ' ' . ($cert->description ?? '') . ' ';
            }
        }
        $texts['Certifications'] = $certText;

        // 7. Projects
        $projText = '';
        if ($cv->relationLoaded('projects') || $cv->projects()->exists()) {
            foreach ($cv->projects as $proj) {
                $projText .= ($proj->title ?? '') . ' ';
                $projText .= ($proj->technologies ?? '') . ' ';
                $projText .= ($proj->description ?? '') . ' ';
            }
        }
        $texts['Projects'] = $projText;

        // 8. Custom Sections
        $customText = '';
        if ($cv->relationLoaded('customSections') || $cv->customSections()->exists()) {
            foreach ($cv->customSections as $cs) {
                $customText .= ($cs->title ?? '') . ' ';
                if (is_array($cs->items)) {
                    $customText .= json_encode($cs->items) . ' ';
                }
            }
        }
        $texts['Custom Sections'] = $customText;

        return $texts;
    }

    /**
     * Find keyword matches taking synonyms into account.
     */
    protected function findKeywordMatches(string $text, string $keyword): int
    {
        $lookupList = [$keyword];
        $lower = strtolower($keyword);

        // Check if keyword has defined synonyms
        foreach ($this->synonyms as $groupKey => $variants) {
            if ($lower === $groupKey || in_array($lower, $variants)) {
                $lookupList = array_unique(array_merge($lookupList, $variants));
                break;
            }
        }

        $totalMatches = 0;
        foreach ($lookupList as $item) {
            $escaped = preg_quote($item, '/');
            $pattern = '/(?:\b|(?<=[^a-zA-Z0-9]))' . $escaped . '(?:\b|(?=[^a-zA-Z0-9]))/i';
            if (preg_match_all($pattern, $text, $matches)) {
                $totalMatches += count($matches[0]);
            }
        }

        return $totalMatches;
    }

    /**
     * Generate a contextual placement hint for a single missing keyword.
     */
    protected function generatePlacementHint(string $keyword, Cv $cv): string
    {
        return "If you possess genuine experience with {$keyword}, consider adding it to your Skills section or demonstrating it in your Work Experience bullet points.";
    }

    /**
     * Generate list of structured suggestions.
     */
    protected function generateSuggestions(array $missingKeywords): array
    {
        if (empty($missingKeywords)) {
            return [
                'Excellent keyword alignment with the target job description. All key identified competencies are represented in your document.',
            ];
        }

        $highPriority = [];
        $other = [];

        foreach ($missingKeywords as $item) {
            if ($item['priority'] === 'High') {
                $highPriority[] = $item['keyword'];
            } else {
                $other[] = $item['keyword'];
            }
        }

        $suggestions = [];

        if (!empty($highPriority)) {
            $suggestions[] = "High Priority Keywords: The job description frequently emphasizes " . implode(', ', array_slice($highPriority, 0, 5)) . ". If applicable to your background, integrate these into your Skills or Professional Summary.";
        }

        if (!empty($other)) {
            $suggestions[] = "Additional Relevant Keywords: Consider including " . implode(', ', array_slice($other, 0, 6)) . " where relevant in your project descriptions or work bullet points.";
        }

        $suggestions[] = "Ensure keywords are woven naturally into contextual accomplishment statements rather than simply listed in isolation.";

        return $suggestions;
    }
}
