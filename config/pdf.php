<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Default Paper & Orientation Settings
    |--------------------------------------------------------------------------
    | Standard document sizes: 'a4', 'letter', 'legal'
    | Orientations: 'portrait', 'landscape'
    */
    'paper_size' => env('PDF_PAPER_SIZE', 'a4'),
    'orientation' => env('PDF_ORIENTATION', 'portrait'),

    /*
    |--------------------------------------------------------------------------
    | Rendering Engine Options
    |--------------------------------------------------------------------------
    */
    'dpi' => (int) env('PDF_DPI', 150),
    'enable_font_subsetting' => true,
    'enable_html5_parser' => true,
    'enable_remote' => false, // Set to false for secure local base64/file loading
    'is_php_enabled' => false, // Disabled for security

    /*
    |--------------------------------------------------------------------------
    | Margins & Layout (in millimeters)
    |--------------------------------------------------------------------------
    */
    'margins' => [
        'top' => 10,
        'right' => 12,
        'bottom' => 10,
        'left' => 12,
    ],

    /*
    |--------------------------------------------------------------------------
    | Safe Font Family Fallbacks for Print
    |--------------------------------------------------------------------------
    */
    'font_map' => [
        'Inter' => 'DejaVu Sans, Helvetica, Arial, sans-serif',
        'Roboto' => 'DejaVu Sans, Helvetica, Arial, sans-serif',
        'Open Sans' => 'DejaVu Sans, Helvetica, Arial, sans-serif',
        'Helvetica' => 'Helvetica, Arial, sans-serif',
        'Arial' => 'Arial, Helvetica, sans-serif',
        'Georgia' => "DejaVu Serif, 'Times New Roman', Times, serif",
        'Merriweather' => "DejaVu Serif, 'Times New Roman', Times, serif",
        'Times New Roman' => "'Times New Roman', Times, serif",
        'Fira Code' => 'DejaVu Sans Mono, Courier, monospace',
        'JetBrains Mono' => 'DejaVu Sans Mono, Courier, monospace',
    ],
];
