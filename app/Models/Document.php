<?php

namespace App\Models;

/**
 * Class Document
 *
 * Domain model representing any Career Document (CV, Resume, Cover Letter, Motivation Letter).
 * Uses the underlying 'cvs' table with document-level architecture.
 */
class Document extends Cv
{
    // Inherits all relationships, helpers, and behavior from Cv
}
