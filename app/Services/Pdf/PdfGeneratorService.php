<?php

namespace App\Services\Pdf;

use App\Models\Cv;
use App\Services\TemplateService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class PdfGeneratorService
{
    public function __construct(
        protected TemplateService $templateService
    ) {}

    /**
     * Generate an HTTP response streaming or downloading the generated PDF.
     *
     * @param Cv $cv
     * @param array $options ['download' => bool, 'paper' => string, 'orientation' => string]
     * @return Response
     */
    public function generate(Cv $cv, array $options = []): Response
    {
        try {
            $isDownload = $options['download'] ?? true;
            $filename = $this->getSafeFilename($cv);
            $html = $this->renderHtml($cv);

            $paperSize = $options['paper'] ?? config('pdf.paper_size', 'a4');
            $orientation = $options['orientation'] ?? config('pdf.orientation', 'portrait');
            $dpi = (int) config('pdf.dpi', 150);

            $pdf = Pdf::loadHTML($html)
                ->setPaper($paperSize, $orientation)
                ->setOption([
                    'dpi' => $dpi,
                    'isHtml5ParserEnabled' => (bool) config('pdf.enable_html5_parser', true),
                    'isFontSubsettingEnabled' => (bool) config('pdf.enable_font_subsetting', true),
                    'isRemoteEnabled' => false, // Base64 data URIs used for secure local image loading
                    'isPhpEnabled' => false,
                ]);

            $pdfContent = $pdf->output();

            $disposition = $isDownload ? 'attachment' : 'inline';

            return response($pdfContent, 200, [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => "{$disposition}; filename=\"{$filename}\"",
                'Content-Length' => strlen($pdfContent),
                'Cache-Control' => 'private, no-cache, no-store, must-revalidate',
                'Pragma' => 'no-cache',
                'Expires' => '0',
            ]);
        } catch (\Throwable $e) {
            Log::error('PDF generation failed', [
                'cv_id' => $cv->id,
                'cv_title' => $cv->title,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            throw $e;
        }
    }

    /**
     * Generate the raw binary PDF string.
     */
    public function output(Cv $cv, array $options = []): string
    {
        $html = $this->renderHtml($cv);
        $paperSize = $options['paper'] ?? config('pdf.paper_size', 'a4');
        $orientation = $options['orientation'] ?? config('pdf.orientation', 'portrait');

        $pdf = Pdf::loadHTML($html)
            ->setPaper($paperSize, $orientation)
            ->setOption([
                'dpi' => (int) config('pdf.dpi', 150),
                'isHtml5ParserEnabled' => true,
                'isFontSubsettingEnabled' => true,
                'isRemoteEnabled' => false,
                'isPhpEnabled' => false,
            ]);

        return $pdf->output();
    }

    /**
     * Render the standalone HTML markup that will be compiled into the PDF.
     */
    public function renderHtml(Cv $cv, ?array $cvData = null): string
    {
        $cvData = $cvData ?? $this->prepareCvDataForPdf($cv);
        $templateModel = $cv->template ?? $this->templateService->findTemplate($cv->template_key);
        $templateView = $this->templateService->resolveViewPath($templateModel);

        $fontFamilyKey = $cvData['fontFamily'] ?? 'Inter';
        $fontMap = config('pdf.font_map', []);
        $fontFamilyFallback = $fontMap[$fontFamilyKey] ?? 'DejaVu Sans, Helvetica, Arial, sans-serif';
        $safeTitle = $this->getSafeFilename($cv);

        return view('pdf.layout', compact(
            'cv',
            'cvData',
            'templateView',
            'fontFamilyFallback',
            'safeTitle'
        ))->render();
    }

    /**
     * Prepares structured view-model tailored specifically for PDF rendering.
     * Safely embeds local images as base64 data URIs and handles font mappings.
     */
    public function prepareCvDataForPdf(Cv $cv): array
    {
        $cvData = $this->templateService->prepareCvData($cv);

        // Secure Base64 Profile Photo Embedding for DomPDF
        $photoPath = $cv->personalInfo?->photo_path;
        $base64Photo = null;

        if (!empty($photoPath)) {
            try {
                if (Storage::disk('public')->exists($photoPath)) {
                    $rawBytes = Storage::disk('public')->get($photoPath);
                    $mime = Storage::disk('public')->mimeType($photoPath) ?: 'image/jpeg';
                    $base64Photo = 'data:' . $mime . ';base64,' . base64_encode($rawBytes);
                } elseif (file_exists(public_path('storage/' . $photoPath))) {
                    $fullPath = public_path('storage/' . $photoPath);
                    $rawBytes = file_get_contents($fullPath);
                    $mime = mime_content_type($fullPath) ?: 'image/jpeg';
                    $base64Photo = 'data:' . $mime . ';base64,' . base64_encode($rawBytes);
                }
            } catch (\Throwable $e) {
                Log::warning('Could not encode profile photo for PDF', [
                    'cv_id' => $cv->id,
                    'photo_path' => $photoPath,
                    'error' => $e->getMessage(),
                ]);
                $base64Photo = null;
            }
        }

        $cvData['photoUrl'] = $base64Photo;
        $cvData['is_pdf'] = true;

        return $cvData;
    }

    /**
     * Generate a safe, standardized filename for the document.
     * e.g. "Moshiur_Rahman_Software_Engineer_CV.pdf" or "Alexander_Vance_Cover_Letter.pdf"
     */
    public function getSafeFilename(Cv $cv): string
    {
        $namePart = $cv->personalInfo?->full_name 
            ? Str::slug($cv->personalInfo->full_name, '_')
            : 'Document';

        $typePart = $cv->documentType?->name
            ? Str::slug($cv->documentType->name, '_')
            : ($cv->isLetter() ? 'Cover_Letter' : 'CV');

        $titlePart = !empty($cv->title) && $cv->title !== $cv->documentType?->name
            ? Str::slug($cv->title, '_')
            : '';

        $parts = array_filter([$namePart, $titlePart ?: $typePart]);
        $base = implode('_', $parts);

        // Sanitize to only alphanumeric and underscore/dash
        $sanitized = preg_replace('/[^A-Za-z0-9_\-]/', '', $base);
        if (empty($sanitized)) {
            $sanitized = 'Career_Document';
        }

        return $sanitized . '.pdf';
    }
}
