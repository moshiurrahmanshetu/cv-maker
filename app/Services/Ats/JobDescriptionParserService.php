<?php

namespace App\Services\Ats;

class JobDescriptionParserService
{
    /**
     * Common technical keywords and frameworks library.
     */
    protected array $technicalDictionary = [
        // Languages
        'PHP', 'JavaScript', 'TypeScript', 'Python', 'Java', 'C#', 'C++', 'Ruby', 'Go', 'Golang',
        'Rust', 'Swift', 'Kotlin', 'Dart', 'SQL', 'HTML', 'HTML5', 'CSS', 'CSS3', 'Bash', 'Shell', 'R', 'Scala',

        // Backend & Frameworks
        'Laravel', 'Symfony', 'CodeIgniter', 'Django', 'Flask', 'FastAPI', 'Spring', 'Spring Boot',
        'ASP.NET', 'Express', 'NestJS', 'Ruby on Rails', 'Node.js', 'Node', 'GraphQL', 'REST', 'RESTful API',
        'gRPC', 'SOAP', 'WebSockets', 'Microservices', 'OOP', 'MVC', 'Design Patterns',

        // Frontend
        'React', 'React.js', 'Vue', 'Vue.js', 'Angular', 'Svelte', 'Next.js', 'Nuxt.js', 'Vite',
        'Webpack', 'Redux', 'Tailwind', 'Tailwind CSS', 'Bootstrap', 'Sass', 'SCSS', 'jQuery',
        'Responsive Design', 'Accessibility', 'WCAG',

        // Databases & Storage
        'MySQL', 'PostgreSQL', 'Postgres', 'MongoDB', 'Redis', 'MariaDB', 'SQLite', 'Oracle',
        'Elasticsearch', 'DynamoDB', 'Firebase', 'Cassandra', 'Supabase', 'Database Optimization',
        'Data Modeling', 'ORM', 'Eloquent',

        // Cloud & DevOps
        'AWS', 'Amazon Web Services', 'Azure', 'Google Cloud', 'GCP', 'Docker', 'Kubernetes', 'K8s',
        'CI/CD', 'Jenkins', 'GitHub Actions', 'GitLab CI', 'Terraform', 'Linux', 'Ubuntu', 'Nginx',
        'Apache', 'Ansible', 'Helm', 'Serverless', 'Lambda', 'Monitoring', 'Prometheus', 'Grafana',

        // Testing & Tooling
        'PHPUnit', 'Pest', 'Jest', 'Mocha', 'Cypress', 'Playwright', 'Selenium', 'Postman',
        'Swagger', 'OpenAPI', 'Git', 'GitHub', 'GitLab', 'Bitbucket', 'Jira', 'Confluence', 'Figma',

        // Architecture & Practices
        'Agile', 'Scrum', 'Kanban', 'TDD', 'BDD', 'Unit Testing', 'Integration Testing',
        'Clean Code', 'SOLID', 'Refactoring', 'Code Review', 'System Design', 'Scalability',
        'Performance Optimization', 'Security', 'Authentication', 'OAuth', 'JWT',
    ];

    /**
     * Common soft skills and managerial terms.
     */
    protected array $softSkillsDictionary = [
        'Leadership', 'Communication', 'Collaboration', 'Problem Solving', 'Critical Thinking',
        'Teamwork', 'Project Management', 'Mentorship', 'Time Management', 'Adaptability',
        'Analytical Skills', 'Attention to Detail', 'Stakeholder Management', 'Cross-functional',
        'Customer Focus', 'Creativity', 'Conflict Resolution', 'Negotiation', 'Self-motivated',
    ];

    /**
     * Parse raw job description text.
     *
     * @param string $jobDescription
     * @return array
     */
    public function parse(string $jobDescription): array
    {
        $text = trim($jobDescription);
        if (empty($text)) {
            return [
                'raw_length' => 0,
                'keywords' => [],
                'categorized' => [
                    'technical' => [],
                    'soft' => [],
                    'other' => [],
                ],
                'frequencies' => [],
                'estimated_title' => null,
            ];
        }

        $frequencies = [];
        $categorized = [
            'technical' => [],
            'soft' => [],
            'other' => [],
        ];

        // 1. Scan for known technical terms
        foreach ($this->technicalDictionary as $tech) {
            $count = $this->countOccurrences($text, $tech);
            if ($count > 0) {
                $frequencies[$tech] = $count;
                $categorized['technical'][] = $tech;
            }
        }

        // 2. Scan for known soft skills
        foreach ($this->softSkillsDictionary as $soft) {
            $count = $this->countOccurrences($text, $soft);
            if ($count > 0) {
                $frequencies[$soft] = $count;
                $categorized['soft'][] = $soft;
            }
        }

        // 3. Extract frequent domain-specific multi-word phrases or capitalized keywords
        $extraKeywords = $this->extractDomainKeywords($text, array_keys($frequencies));
        foreach ($extraKeywords as $kw => $count) {
            $frequencies[$kw] = $count;
            $categorized['other'][] = $kw;
        }

        // Sort frequencies descending
        arsort($frequencies);

        $allKeywords = array_keys($frequencies);

        return [
            'raw_length' => strlen($text),
            'keywords' => $allKeywords,
            'categorized' => $categorized,
            'frequencies' => $frequencies,
            'estimated_title' => $this->guessJobTitle($text),
        ];
    }

    /**
     * Count exact or boundary-aware occurrences of a keyword in text.
     */
    protected function countOccurrences(string $text, string $keyword): int
    {
        // Quote for regex and handle special symbol boundary cases (e.g. C++, C#, .NET, Node.js)
        $escaped = preg_quote($keyword, '/');
        
        // If keyword ends with non-word character (like C++, C#), adjust word boundary
        $pattern = '/(?:\b|(?<=[^a-zA-Z0-9]))' . $escaped . '(?:\b|(?=[^a-zA-Z0-9]))/i';
        
        $matches = [];
        $found = preg_match_all($pattern, $text, $matches);
        
        return $found ? count($matches[0]) : 0;
    }

    /**
     * Extract significant recurring capitalized or technical domain terms not in base dictionary.
     */
    protected function extractDomainKeywords(string $text, array $alreadyFound): array
    {
        $extras = [];
        $stopWords = [
            'THE', 'AND', 'FOR', 'WITH', 'YOU', 'OUR', 'ARE', 'WILL', 'THAT', 'THIS', 'FROM', 'HAVE',
            'WORK', 'TEAM', 'MUST', 'EXPERIENCE', 'YEARS', 'ROLE', 'COMPANY', 'ABOUT', 'REQUIREMENTS',
            'RESPONSIBILITIES', 'QUALIFICATIONS', 'BENEFITS', 'JOIN', 'LOOKING', 'CANDIDATE', 'OPPORTUNITY',
            'FULL', 'TIME', 'PART', 'REMOTE', 'HYBRID', 'LOCATION', 'SALARY', 'DESCRIPTION', 'POSITION'
        ];

        // Match 2-word phrases or capitalized tokens (e.g., "Machine Learning", "DevOps", "Cybersecurity")
        $tokens = preg_split('/[\r\n\t,;:.]/', $text);
        foreach ($tokens as $fragment) {
            $fragment = trim($fragment);
            if (empty($fragment) || strlen($fragment) < 3) {
                continue;
            }

            // Look for capitalized phrases of 1-3 words
            if (preg_match_all('/\b([A-Z][a-zA-Z0-9\+\#\.\-]{2,}(?:\s+[A-Z][a-zA-Z0-9\+\#\.\-]+)?)\b/', $fragment, $matches)) {
                foreach ($matches[1] as $match) {
                    $upper = strtoupper($match);
                    if (in_array($upper, $stopWords) || strlen($match) < 3) {
                        continue;
                    }

                    // Check if already covered
                    $isAlready = false;
                    foreach ($alreadyFound as $existing) {
                        if (strcasecmp($existing, $match) === 0) {
                            $isAlready = true;
                            break;
                        }
                    }

                    if (!$isAlready) {
                        $cnt = $this->countOccurrences($text, $match);
                        if ($cnt >= 2) { // must appear at least twice to be a recurring key requirement
                            $extras[$match] = $cnt;
                        }
                    }
                }
            }
        }

        return array_slice($extras, 0, 10);
    }

    /**
     * Guess job title from the first lines of the text if obvious.
     */
    protected function guessJobTitle(string $text): ?string
    {
        $lines = preg_split('/[\r\n]+/', trim($text));
        if (empty($lines)) {
            return null;
        }

        $firstFew = array_slice($lines, 0, 3);
        foreach ($firstFew as $line) {
            $clean = trim($line, " \t\n\r\0\x0B#*:-");
            if (strlen($clean) >= 4 && strlen($clean) <= 60 && !preg_match('/^(job|about|company|overview|description|we are)/i', $clean)) {
                if (preg_match('/(developer|engineer|manager|architect|designer|analyst|specialist|lead|consultant|administrator|director|executive|coordinator|officer)/i', $clean)) {
                    return $clean;
                }
            }
        }

        return null;
    }
}
