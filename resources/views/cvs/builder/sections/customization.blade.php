@php
    $settings = is_array($cv->settings) ? $cv->settings : [];
    $accentColor = $settings['accent_color'] ?? $cv->primary_color ?? '#1e293b';
    $fontFamily = $settings['font_family'] ?? $cv->font_family ?? 'Inter';
    $fontSize = $settings['font_size'] ?? 'normal';
    $headingSize = $settings['heading_size'] ?? 'normal';
    $lineSpacing = $settings['line_spacing'] ?? 'normal';
    $sectionSpacing = $settings['section_spacing'] ?? 'normal';
    $photoSize = $settings['photo_size'] ?? 'medium';
    $showIcons = isset($settings['show_icons']) ? (bool)$settings['show_icons'] : true;
@endphp

<div class="card card-saas">
    <div class="card-header bg-white border-bottom py-3 px-4 d-flex align-items-center justify-content-between">
        <div>
            <h2 class="h6 fw-bold mb-0 text-dark">
                <i class="bi bi-palette me-2 text-muted"></i> Design & Customization
            </h2>
            <p class="text-muted small mb-0 mt-1">Control typography, color palette, dimensions, and layout density.</p>
        </div>
    </div>
    <div class="card-body p-4">
        <form method="POST" action="{{ route('cvs.builder.settings', $cv) }}" id="customizationForm">
            @csrf

            <!-- Color Palette -->
            <div class="mb-4 pb-3 border-bottom">
                <label class="form-label fw-bold text-dark mb-2">Accent / Primary Color</label>
                <div class="d-flex align-items-center gap-3 flex-wrap">
                    @php
                        $presetColors = [
                            '#1e293b' => 'Slate Charcoal',
                            '#0f172a' => 'Executive Dark',
                            '#0e4194' => 'Europass Blue',
                            '#1b3b36' => 'Australian Forest',
                            '#8c1d40' => 'Academic Maroon',
                            '#334155' => 'Steel Gray',
                            '#2563eb' => 'Royal Blue',
                            '#059669' => 'Emerald Green',
                        ];
                    @endphp
                    @foreach($presetColors as $colorCode => $colorName)
                        <button type="button" class="color-preset-btn rounded-circle border shadow-sm" 
                                style="width: 32px; height: 32px; background-color: {{ $colorCode }}; cursor: pointer; outline: {{ $accentColor === $colorCode ? '3px solid #000' : 'none' }};"
                                title="{{ $colorName }}"
                                onclick="document.getElementById('accentColorInput').value='{{ $colorCode }}'; triggerLiveStyleUpdate();">
                        </button>
                    @endforeach
                    <div class="d-flex align-items-center gap-2 ms-2">
                        <input type="color" name="accent_color" id="accentColorInput" class="form-control form-control-color p-1" value="{{ $accentColor }}" onchange="triggerLiveStyleUpdate();" style="width: 44px; height: 34px;">
                        <span class="small text-muted font-monospace" id="accentColorHex">{{ $accentColor }}</span>
                    </div>
                </div>
            </div>

            <!-- Typography & Font Family -->
            <div class="mb-4 pb-3 border-bottom">
                <label class="form-label fw-bold text-dark mb-2">Typography (Font Family)</label>
                <div class="row g-3">
                    <div class="col-md-6">
                        <select name="font_family" id="fontFamilySelect" class="form-select" onchange="triggerLiveStyleUpdate();">
                            <optgroup label="Modern Sans-Serif">
                                <option value="Inter" {{ $fontFamily === 'Inter' ? 'selected' : '' }}>Inter (Clean & Professional)</option>
                                <option value="Roboto" {{ $fontFamily === 'Roboto' ? 'selected' : '' }}>Roboto (Crisp Geometric)</option>
                                <option value="-apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif" {{ str_contains($fontFamily, 'Segoe') ? 'selected' : '' }}>System UI (Native)</option>
                            </optgroup>
                            <optgroup label="Executive Serif">
                                <option value="'Times New Roman', Times, 'Georgia', serif" {{ str_contains($fontFamily, 'Times') ? 'selected' : '' }}>Times New Roman (Traditional Executive)</option>
                                <option value="'Georgia', 'Times New Roman', serif" {{ str_contains($fontFamily, 'Georgia') ? 'selected' : '' }}>Georgia (Warm Editorial)</option>
                                <option value="'Merriweather', 'Georgia', serif" {{ str_contains($fontFamily, 'Merriweather') ? 'selected' : '' }}>Merriweather (Academic)</option>
                            </optgroup>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <div class="p-2 border rounded bg-surface-subtle small font-monospace text-muted d-flex align-items-center justify-content-center h-100" id="fontPreviewSample" style="font-family: {{ $fontFamily }};">
                            The quick brown fox jumps over the lazy dog.
                        </div>
                    </div>
                </div>
            </div>

            <!-- Font Sizes & Scaling -->
            <div class="mb-4 pb-3 border-bottom">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label fw-semibold text-dark">Body Text Size</label>
                        <select name="font_size" class="form-select form-select-sm" onchange="triggerLiveStyleUpdate();">
                            <option value="small" {{ $fontSize === 'small' ? 'selected' : '' }}>Small (Compact fit)</option>
                            <option value="normal" {{ $fontSize === 'normal' ? 'selected' : '' }}>Normal (Standard standard)</option>
                            <option value="large" {{ $fontSize === 'large' ? 'selected' : '' }}>Large (High legibility)</option>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold text-dark">Heading & Name Scale</label>
                        <select name="heading_size" class="form-select form-select-sm" onchange="triggerLiveStyleUpdate();">
                            <option value="compact" {{ $headingSize === 'compact' ? 'selected' : '' }}>Compact</option>
                            <option value="normal" {{ $headingSize === 'normal' ? 'selected' : '' }}>Normal</option>
                            <option value="large" {{ $headingSize === 'large' ? 'selected' : '' }}>Prominent / Bold</option>
                        </select>
                    </div>
                </div>
            </div>

            <!-- Line Height & Section Spacing -->
            <div class="mb-4 pb-3 border-bottom">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label fw-semibold text-dark">Line Spacing (Leading)</label>
                        <select name="line_spacing" class="form-select form-select-sm" onchange="triggerLiveStyleUpdate();">
                            <option value="compact" {{ $lineSpacing === 'compact' ? 'selected' : '' }}>Compact (Fit on 1 page)</option>
                            <option value="normal" {{ $lineSpacing === 'normal' ? 'selected' : '' }}>Normal (Balanced)</option>
                            <option value="relaxed" {{ $lineSpacing === 'relaxed' ? 'selected' : '' }}>Relaxed (Spacious)</option>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold text-dark">Section Spacing</label>
                        <select name="section_spacing" class="form-select form-select-sm" onchange="triggerLiveStyleUpdate();">
                            <option value="compact" {{ $sectionSpacing === 'compact' ? 'selected' : '' }}>Compact</option>
                            <option value="normal" {{ $sectionSpacing === 'normal' ? 'selected' : '' }}>Normal</option>
                            <option value="spacious" {{ $sectionSpacing === 'spacious' ? 'selected' : '' }}>Spacious</option>
                        </select>
                    </div>
                </div>
            </div>

            <!-- Photo & Icons Display (For CVs) -->
            @if(!$cv->isLetter())
                <div class="mb-4">
                    <div class="row g-3 align-items-center">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold text-dark">Profile Photo Sizing</label>
                            <select name="photo_size" class="form-select form-select-sm" onchange="triggerLiveStyleUpdate();">
                                <option value="small" {{ $photoSize === 'small' ? 'selected' : '' }}>Small (70px)</option>
                                <option value="medium" {{ $photoSize === 'medium' ? 'selected' : '' }}>Medium (90px)</option>
                                <option value="large" {{ $photoSize === 'large' ? 'selected' : '' }}>Large (110px)</option>
                                <option value="hidden" {{ $photoSize === 'hidden' ? 'selected' : '' }}>Hide Photo (USA/UK standard)</option>
                            </select>
                        </div>
                        <div class="col-md-6 pt-md-4">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" name="show_icons" value="1" id="showIconsSwitch" {{ $showIcons ? 'checked' : '' }} onchange="triggerLiveStyleUpdate();">
                                <label class="form-check-label fw-semibold text-dark" for="showIconsSwitch">
                                    Display Section & Contact Icons
                                </label>
                            </div>
                        </div>
                    </div>
                </div>
            @endif

            <div class="d-flex align-items-center justify-content-between pt-4 border-top">
                <span class="small text-muted"><i class="bi bi-magic me-1"></i> Live preview updates automatically</span>
                <button type="submit" class="btn-saas-primary px-4">
                    <i class="bi bi-check2-circle me-1"></i> Save Design Settings
                </button>
            </div>
        </form>
    </div>
</div>
