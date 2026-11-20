<?php
/**
 * ToolsWallet Universal Tool Workspace & Runner
 * Domain: toolswallet.online
 */

require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/ads.php';
require_once __DIR__ . '/includes/seo.php';

$slug = trim($_GET['slug'] ?? 'json-formatter');

// SEO: 301 Permanent Redirect to particular standalone tool page
$particularPage = __DIR__ . '/tools/' . $slug . '.php';
if (file_exists($particularPage)) {
    header("Location: " . tool_url($slug), true, 301);
    exit;
}

$tool = Database::getTool($slug);

if (!$tool) {
    header("HTTP/1.0 404 Not Found");
    $pageSeo = ['title' => 'Tool Not Found | ToolsWallet'];
    require_once __DIR__ . '/includes/header.php';
    ?>
    <div class="container" style="padding: 5rem 1rem; text-align: center;">
        <i class="fa-solid fa-screwdriver-wrench" style="font-size: 3rem; color: #ef4444; margin-bottom: 1rem;"></i>
        <h2>Tool Not Found</h2>
        <p style="color: var(--text-secondary); margin-top: 0.5rem;">The utility you requested does not exist or has been updated.</p>
        <a href="<?= url() ?>" class="btn-primary-gradient" style="margin-top: 1.5rem; display: inline-flex;">Explore All Tools</a>
    </div>
    <?php
    require_once __DIR__ . '/includes/footer.php';
    exit;
}

$category = Database::getCategory($tool['category_slug'] ?? 'developer-tools') ?? [
    'name' => $tool['category_name'] ?? 'Utilities',
    'slug' => $tool['category_slug'] ?? 'developer-tools',
    'color' => '#6366f1'
];

$faqs = Database::getFaqs($slug, $tool['category_slug'] ?? null);
$relatedTools = array_filter(Database::getTools($tool['category_slug'] ?? null), fn($t) => $t['slug'] !== $slug);
$relatedTools = array_slice($relatedTools, 0, 4);

$pageSeo = [
    'title' => $tool['meta_title'] ?? ($tool['name'] . ' Online Free | ToolsWallet'),
    'description' => $tool['meta_description'] ?? $tool['short_description'],
    'keywords' => $tool['meta_keywords'] ?? ($tool['name'] . ', free online tool, toolswallet'),
    'canonical' => url('tool.php?slug=' . $tool['slug']),
    'tool' => $tool,
    'breadcrumbs' => [
        ['name' => 'Home', 'url' => url()],
        ['name' => $category['name'], 'url' => url('category.php?slug=' . $category['slug'])],
        ['name' => $tool['name'], 'url' => url('tool.php?slug=' . $tool['slug'])]
    ],
    'faqs' => $faqs
];

require_once __DIR__ . '/includes/header.php';
?>

<div class="container">
    <!-- Breadcrumbs -->
    <nav class="breadcrumbs" aria-label="Breadcrumb">
        <a href="<?= url() ?>"><i class="fa-solid fa-house"></i> Home</a>
        <span class="separator">/</span>
        <a href="<?= url('category.php?slug=' . $category['slug']) ?>"><?= e($category['name']) ?></a>
        <span class="separator">/</span>
        <span class="current"><?= e($tool['name']) ?></span>
    </nav>

    <!-- Tool Page Header -->
    <div class="tool-page-header">
        <div class="tool-header-left">
            <div class="tool-page-icon">
                <i class="fa-solid <?= e($tool['icon']) ?>"></i>
            </div>
            <div>
                <div style="display: flex; align-items: center; gap: 0.6rem; flex-wrap: wrap;">
                    <h1 class="tool-page-title"><?= e($tool['name']) ?></h1>
                    <?php if (!empty($tool['badge'])): ?>
                        <span class="tool-badge badge-<?= strtolower($tool['badge']) ?>"><?= e($tool['badge']) ?></span>
                    <?php endif; ?>
                </div>
                <p class="tool-page-tagline"><?= e($tool['short_description']) ?></p>
            </div>
        </div>

        <!-- Tool Actions & Rating -->
        <div class="tool-header-actions">
            <span class="tool-rating" style="font-size: 0.95rem; background: var(--bg-surface); padding: 0.5rem 0.8rem; border-radius: var(--radius-md); border: 1px solid var(--border-subtle);">
                <i class="fa-solid fa-star"></i> <?= e((string)($tool['rating'] ?? '4.9')) ?>
            </span>
            <button type="button" class="tool-action-btn" onclick="copyToClipboard(window.location.href, 'Tool URL copied to clipboard!')">
                <i class="fa-solid fa-share-nodes"></i> Share
            </button>
            <button type="button" class="tool-action-btn" onclick="showToast('Added to favorites!', 'success')">
                <i class="fa-regular fa-bookmark"></i> Save
            </button>
        </div>
    </div>

    <!-- ==============================================================
         Tool Interactive Workspace Card
         ============================================================== -->
    <div class="tool-workspace-card">

        <?php if ($slug === 'json-formatter'): ?>
            <!-- JSON Formatter & Validator -->
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem; flex-wrap: wrap; gap: 0.75rem;">
                <div style="display: flex; align-items: center; gap: 0.5rem;">
                    <label for="jsonIndent" style="font-size: 0.85rem; font-weight: 600;">Indent:</label>
                    <select id="jsonIndent" class="form-control" style="width: auto; padding: 0.35rem 0.75rem; font-size: 0.85rem;">
                        <option value="2">2 Spaces</option>
                        <option value="4">4 Spaces</option>
                    </select>
                </div>
                <div id="jsonStatus" style="font-size: 0.88rem; font-weight: 600;"></div>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.25rem;">
                <div class="form-group" style="margin-bottom: 0;">
                    <label class="form-label">Input JSON Payload:</label>
                    <textarea id="jsonInput" class="form-control font-mono" style="height: 320px;" placeholder='{"name":"ToolsWallet","status":"online","features":["free","fast","secure"]}'></textarea>
                </div>
                <div class="form-group" style="margin-bottom: 0;">
                    <label class="form-label">Formatted Result:</label>
                    <textarea id="jsonOutput" class="form-control font-mono" style="height: 320px;" placeholder="Formatted JSON will appear here..." readonly></textarea>
                </div>
            </div>

            <div style="display: flex; gap: 0.75rem; margin-top: 1.25rem; flex-wrap: wrap;">
                <button type="button" id="btnJsonBeautify" class="btn-primary-gradient"><i class="fa-solid fa-wand-magic-sparkles"></i> Beautify JSON</button>
                <button type="button" id="btnJsonMinify" class="btn-secondary"><i class="fa-solid fa-compress"></i> Minify JSON</button>
                <button type="button" id="btnCopyJson" class="btn-secondary"><i class="fa-regular fa-copy"></i> Copy Output</button>
                <button type="button" id="btnDownloadJson" class="btn-secondary"><i class="fa-solid fa-download"></i> Download .json</button>
                <button type="button" id="btnClearJson" class="btn-secondary" style="margin-left: auto;"><i class="fa-solid fa-trash-can"></i> Clear</button>
            </div>

        <?php elseif ($slug === 'word-counter'): ?>
            <!-- Word & Character Counter -->
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(130px, 1fr)); gap: 1rem; margin-bottom: 1.5rem;">
                <div style="background: var(--bg-body); padding: 1rem; border-radius: var(--radius-md); border: 1px solid var(--border-subtle); text-align: center;">
                    <div id="statWords" style="font-size: 1.8rem; font-weight: 800; color: var(--primary);">0</div>
                    <div style="font-size: 0.8rem; font-weight: 700; color: var(--text-muted); text-transform: uppercase;">Words</div>
                </div>
                <div style="background: var(--bg-body); padding: 1rem; border-radius: var(--radius-md); border: 1px solid var(--border-subtle); text-align: center;">
                    <div id="statChars" style="font-size: 1.8rem; font-weight: 800; color: var(--accent-violet);">0</div>
                    <div style="font-size: 0.8rem; font-weight: 700; color: var(--text-muted); text-transform: uppercase;">Characters</div>
                </div>
                <div style="background: var(--bg-body); padding: 1rem; border-radius: var(--radius-md); border: 1px solid var(--border-subtle); text-align: center;">
                    <div id="statCharsNoSpace" style="font-size: 1.8rem; font-weight: 800; color: var(--accent-cyan);">0</div>
                    <div style="font-size: 0.8rem; font-weight: 700; color: var(--text-muted); text-transform: uppercase;">No Spaces</div>
                </div>
                <div style="background: var(--bg-body); padding: 1rem; border-radius: var(--radius-md); border: 1px solid var(--border-subtle); text-align: center;">
                    <div id="statSentences" style="font-size: 1.8rem; font-weight: 800; color: #10b981;">0</div>
                    <div style="font-size: 0.8rem; font-weight: 700; color: var(--text-muted); text-transform: uppercase;">Sentences</div>
                </div>
                <div style="background: var(--bg-body); padding: 1rem; border-radius: var(--radius-md); border: 1px solid var(--border-subtle); text-align: center;">
                    <div id="statReadingTime" style="font-size: 1.8rem; font-weight: 800; color: #f59e0b;">0 sec</div>
                    <div style="font-size: 0.8rem; font-weight: 700; color: var(--text-muted); text-transform: uppercase;">Reading Time</div>
                </div>
                <div style="background: var(--bg-body); padding: 1rem; border-radius: var(--radius-md); border: 1px solid var(--border-subtle); text-align: center;">
                    <div id="statSpeakingTime" style="font-size: 1.8rem; font-weight: 800; color: #ec4899;">0 sec</div>
                    <div style="font-size: 0.8rem; font-weight: 700; color: var(--text-muted); text-transform: uppercase;">Speaking Time</div>
                </div>
            </div>

            <div class="form-group">
                <textarea id="wordCounterInput" class="form-control" style="height: 240px;" placeholder="Start typing or paste your content here to inspect real-time word count, character density, and reading duration..."></textarea>
            </div>

            <div style="display: flex; justify-content: space-between; align-items: flex-start; gap: 1.5rem; flex-wrap: wrap;">
                <div style="display: flex; gap: 0.75rem;">
                    <button type="button" id="btnCopyWordCounter" class="btn-primary-gradient btn-sm"><i class="fa-regular fa-copy"></i> Copy Text</button>
                    <button type="button" id="btnClearWordCounter" class="btn-secondary btn-sm"><i class="fa-solid fa-trash-can"></i> Clear Text</button>
                </div>
                <div style="flex: 1; max-width: 320px;">
                    <h4 style="font-size: 0.85rem; font-weight: 700; margin-bottom: 0.5rem; text-transform: uppercase; color: var(--text-muted);">Top Keyword Density</h4>
                    <div id="keywordDensityList"></div>
                </div>
            </div>

        <?php elseif ($slug === 'qr-code-generator'): ?>
            <!-- QR Code Generator -->
            <div style="display: grid; grid-template-columns: 1.2fr 1fr; gap: 2rem;">
                <div>
                    <div class="form-group">
                        <label class="form-label">QR Content (URL, Text, or Wi-Fi):</label>
                        <textarea id="qrText" class="form-control" style="height: 110px;" placeholder="https://toolswallet.online"></textarea>
                    </div>

                    <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 1rem; margin-bottom: 1.25rem;">
                        <div class="form-group">
                            <label class="form-label">Foreground:</label>
                            <input type="color" id="qrFgColor" value="#000000" class="form-control" style="height: 42px; padding: 2px;">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Background:</label>
                            <input type="color" id="qrBgColor" value="#ffffff" class="form-control" style="height: 42px; padding: 2px;">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Size:</label>
                            <select id="qrSize" class="form-control">
                                <option value="256">256 x 256 px</option>
                                <option value="512">512 x 512 px</option>
                                <option value="1024">1024 x 1024 px</option>
                            </select>
                        </div>
                    </div>

                    <div style="display: flex; gap: 0.75rem;">
                        <button type="button" id="btnGenerateQR" class="btn-primary-gradient"><i class="fa-solid fa-qrcode"></i> Generate QR Code</button>
                        <button type="button" id="btnDownloadQR" class="btn-secondary"><i class="fa-solid fa-download"></i> Download PNG</button>
                    </div>
                </div>

                <div style="display: flex; flex-direction: column; align-items: center; justify-content: center; background: var(--bg-body); border: 1px solid var(--border-subtle); border-radius: var(--radius-lg); padding: 2rem; min-height: 280px;">
                    <div id="qrPreview"></div>
                    <p style="font-size: 0.8rem; color: var(--text-muted); margin-top: 1rem;">Point your smartphone camera to test scan.</p>
                </div>
            </div>

        <?php elseif ($slug === 'meta-tag-generator'): ?>
            <!-- Meta Tag Generator -->
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 2rem;">
                <div>
                    <div class="form-group">
                        <div style="display: flex; justify-content: space-between;">
                            <label class="form-label">Page Title:</label>
                            <span id="titleCharCount" style="font-size: 0.78rem; color: var(--text-muted);">0 / 60 chars</span>
                        </div>
                        <input type="text" id="metaTitle" class="form-control" placeholder="e.g. Best Free Online Tools Suite | ToolsWallet" value="ToolsWallet - Free Online Tools Platform">
                    </div>

                    <div class="form-group">
                        <div style="display: flex; justify-content: space-between;">
                            <label class="form-label">Meta Description:</label>
                            <span id="descCharCount" style="font-size: 0.78rem; color: var(--text-muted);">0 / 160 chars</span>
                        </div>
                        <textarea id="metaDesc" class="form-control" style="height: 90px;" placeholder="Provide an engaging description that highlights your webpage content...">Access 50+ free online developer, PDF, and image optimization utilities with zero registration on ToolsWallet.</textarea>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Canonical Web Address:</label>
                        <input type="url" id="metaUrl" class="form-control" placeholder="https://example.com/page" value="https://toolswallet.online">
                    </div>

                    <div class="form-group">
                        <label class="form-label">Social Share Image URL (Open Graph):</label>
                        <input type="url" id="metaImage" class="form-control" placeholder="https://example.com/og-image.jpg" value="https://toolswallet.online/assets/images/og.png">
                    </div>
                </div>

                <div>
                    <label class="form-label">Live Google SERP Simulation:</label>
                    <div style="background: var(--bg-body); border: 1px solid var(--border-subtle); border-radius: var(--radius-md); padding: 1.25rem; margin-bottom: 1.25rem;">
                        <div id="serpPreviewUrl" style="font-size: 0.8rem; color: #1a0dab; word-break: break-all; margin-bottom: 0.2rem;">https://toolswallet.online</div>
                        <div id="serpPreviewTitle" style="font-size: 1.15rem; color: #1a0dab; font-weight: 600; text-decoration: underline; margin-bottom: 0.35rem; cursor: pointer;">ToolsWallet - Free Online Tools Platform</div>
                        <div id="serpPreviewDesc" style="font-size: 0.88rem; color: #4d5156; line-height: 1.4;">Access 50+ free online developer, PDF, and image optimization utilities with zero registration on ToolsWallet.</div>
                    </div>

                    <label class="form-label">Generated HTML Code:</label>
                    <textarea id="metaOutput" class="form-control font-mono" style="height: 150px;" readonly></textarea>
                    <button type="button" id="btnCopyMeta" class="btn-primary-gradient btn-sm" style="margin-top: 0.75rem;"><i class="fa-regular fa-copy"></i> Copy Meta Tags</button>
                </div>
            </div>

        <?php elseif ($slug === 'image-resizer'): ?>
            <!-- Image Resizer -->
            <div class="file-dropzone" id="resizerDropzone">
                <i class="fa-solid fa-cloud-arrow-up dropzone-icon"></i>
                <div class="dropzone-title">Drag & drop your image here or browse files</div>
                <div class="dropzone-subtitle">Supports JPG, PNG, WebP up to 25MB</div>
                <input type="file" id="imageFileInput" class="file-input-hidden" accept="image/*">
            </div>

            <div id="resizerWorkspace" style="display: none; margin-top: 1.5rem;">
                <div id="originalStats" style="font-weight: 700; color: var(--primary); margin-bottom: 1rem;"></div>
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem; align-items: center;">
                    <div>
                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-bottom: 1rem;">
                            <div class="form-group">
                                <label class="form-label">Width (pixels):</label>
                                <input type="number" id="imgWidth" class="form-control">
                            </div>
                            <div class="form-group">
                                <label class="form-label">Height (pixels):</label>
                                <input type="number" id="imgHeight" class="form-control">
                            </div>
                        </div>

                        <div style="margin-bottom: 1.25rem;">
                            <label style="display: inline-flex; align-items: center; gap: 0.5rem; cursor: pointer; font-size: 0.9rem; font-weight: 600;">
                                <input type="checkbox" id="lockAspectRatio" checked style="accent-color: var(--primary); width: 18px; height: 18px;">
                                Lock Aspect Ratio
                            </label>
                        </div>

                        <button type="button" id="btnDownloadResized" class="btn-primary-gradient"><i class="fa-solid fa-download"></i> Download Resized Image</button>
                    </div>

                    <div style="text-align: center; background: var(--bg-body); padding: 1rem; border-radius: var(--radius-md); border: 1px solid var(--border-subtle);">
                        <img id="imagePreview" alt="Resized Preview" style="max-width: 100%; max-height: 250px; border-radius: var(--radius-sm); object-fit: contain;">
                    </div>
                </div>
            </div>

        <?php elseif ($slug === 'image-compressor'): ?>
            <!-- Image Compressor -->
            <div class="file-dropzone" id="compressorDropzone">
                <i class="fa-solid fa-file-zipper dropzone-icon"></i>
                <div class="dropzone-title">Upload Image to Compress</div>
                <div class="dropzone-subtitle">Lossless & lossy compression for PNG, JPG, WebP</div>
                <input type="file" id="compressorFileInput" class="file-input-hidden" accept="image/*">
            </div>

            <div id="compressorWorkspace" style="display: none; margin-top: 1.5rem;">
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem; align-items: center;">
                    <div>
                        <div class="form-group">
                            <div style="display: flex; justify-content: space-between; margin-bottom: 0.5rem;">
                                <label class="form-label">Compression Quality:</label>
                                <span id="qualityValue" style="font-weight: 700; color: var(--primary);">75%</span>
                            </div>
                            <input type="range" id="compressQuality" min="10" max="95" value="75" style="width: 100%; accent-color: var(--primary);">
                        </div>

                        <div style="display: flex; gap: 1rem; margin: 1.25rem 0;">
                            <div style="background: var(--bg-body); padding: 0.75rem 1rem; border-radius: var(--radius-md); border: 1px solid var(--border-subtle); flex: 1;">
                                <div style="font-size: 0.75rem; color: var(--text-muted); text-transform: uppercase;">Original</div>
                                <div id="beforeSize" style="font-size: 1.15rem; font-weight: 800;">0 KB</div>
                            </div>
                            <div style="background: var(--bg-body); padding: 0.75rem 1rem; border-radius: var(--radius-md); border: 1px solid var(--border-subtle); flex: 1;">
                                <div style="font-size: 0.75rem; color: var(--text-muted); text-transform: uppercase;">Compressed</div>
                                <div id="afterSize" style="font-size: 1.15rem; font-weight: 800; color: #10b981;">0 KB</div>
                            </div>
                            <div style="background: rgba(16, 185, 129, 0.1); border: 1px solid rgba(16, 185, 129, 0.2); padding: 0.75rem 1rem; border-radius: var(--radius-md); display: flex; align-items: center; justify-content: center;">
                                <div id="savingsPct" style="font-size: 0.95rem; font-weight: 800; color: #10b981;">0% Saved</div>
                            </div>
                        </div>

                        <button type="button" id="btnDownloadCompressed" class="btn-primary-gradient"><i class="fa-solid fa-download"></i> Download Compressed Image</button>
                    </div>

                    <div style="text-align: center; background: var(--bg-body); padding: 1rem; border-radius: var(--radius-md); border: 1px solid var(--border-subtle);">
                        <img id="compressedPreview" alt="Compressed Preview" style="max-width: 100%; max-height: 250px; border-radius: var(--radius-sm); object-fit: contain;">
                    </div>
                </div>
            </div>

        <?php elseif ($slug === 'seo-analyzer'): ?>
            <!-- On-Page SEO Analyzer -->
            <div style="max-width: 680px; margin: 0 auto;">
                <div class="form-group">
                    <label class="form-label">Enter Public Webpage URL:</label>
                    <div style="display: flex; gap: 0.5rem;">
                        <input type="url" id="seoAuditUrl" class="form-control" placeholder="https://example.com" value="https://google.com">
                        <button type="button" id="btnRunAudit" class="btn-primary-gradient" style="white-space: nowrap;">
                            <i class="fa-solid fa-magnifying-glass-chart"></i> Run SEO Audit
                        </button>
                    </div>
                    <div class="form-hint">SSRF protected: private IP ranges and internal addresses are automatically blocked.</div>
                </div>

                <div id="auditResults" style="display: none; margin-top: 2rem;">
                    <div style="text-align: center; margin-bottom: 2rem;">
                        <div id="auditScore" style="font-size: 3.5rem; font-weight: 800; color: #10b981;">--</div>
                        <div style="font-weight: 700; color: var(--text-secondary);">Overall On-Page SEO Score</div>
                    </div>

                    <div style="display: flex; flex-direction: column; gap: 0.75rem;">
                        <div style="background: var(--bg-body); padding: 1rem; border-radius: var(--radius-md); border: 1px solid var(--border-subtle);">
                            <div style="font-size: 0.8rem; font-weight: 700; color: var(--text-muted); text-transform: uppercase;">Page Title</div>
                            <div id="auditTitle" style="font-weight: 600; margin-top: 0.2rem;"></div>
                        </div>
                        <div style="background: var(--bg-body); padding: 1rem; border-radius: var(--radius-md); border: 1px solid var(--border-subtle);">
                            <div style="font-size: 0.8rem; font-weight: 700; color: var(--text-muted); text-transform: uppercase;">Meta Description</div>
                            <div id="auditMeta" style="font-weight: 600; margin-top: 0.2rem;"></div>
                        </div>
                        <div style="background: var(--bg-body); padding: 1rem; border-radius: var(--radius-md); border: 1px solid var(--border-subtle);">
                            <div style="font-size: 0.8rem; font-weight: 700; color: var(--text-muted); text-transform: uppercase;">Headings Structure</div>
                            <div id="auditH1" style="font-weight: 600; margin-top: 0.2rem;"></div>
                        </div>
                        <div style="background: var(--bg-body); padding: 1rem; border-radius: var(--radius-md); border: 1px solid var(--border-subtle);">
                            <div style="font-size: 0.8rem; font-weight: 700; color: var(--text-muted); text-transform: uppercase;">Image Accessibility</div>
                            <div id="auditImages" style="font-weight: 600; margin-top: 0.2rem;"></div>
                        </div>
                    </div>
                </div>
            </div>

        <?php elseif ($slug === 'youtube-thumbnail-grabber'): ?>
            <!-- YouTube Thumbnail Grabber -->
            <div style="max-width: 680px; margin: 0 auto;">
                <div class="form-group">
                    <label class="form-label">Enter YouTube Video Link or Shorts URL:</label>
                    <div style="display: flex; gap: 0.5rem;">
                        <input type="url" id="ytUrlInput" class="form-control" placeholder="https://www.youtube.com/watch?v=dQw4w9WgXcQ">
                        <button type="button" id="btnFetchThumbnail" class="btn-primary-gradient" style="white-space: nowrap;">
                            <i class="fa-solid fa-image"></i> Fetch Thumbnail
                        </button>
                    </div>
                </div>

                <div id="ytResults" style="display: none;"></div>
            </div>

        <?php elseif ($slug === 'loan-emi-calculator'): ?>
            <!-- Loan EMI Calculator -->
            <div style="display: grid; grid-template-columns: 1.2fr 1fr; gap: 2rem;">
                <div>
                    <div class="form-group">
                        <label class="form-label">Loan Principal Amount ($):</label>
                        <input type="number" id="loanAmount" class="form-control" value="250000" step="5000">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Annual Interest Rate (%):</label>
                        <input type="number" id="loanRate" class="form-control" value="6.5" step="0.1">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Tenure (Years):</label>
                        <input type="number" id="loanTenure" class="form-control" value="20" step="1">
                    </div>
                </div>

                <div id="loanResultCard" style="background: var(--bg-body); border: 1px solid var(--border-subtle); border-radius: var(--radius-lg); padding: 1.75rem; display: flex; flex-direction: column; justify-content: center; text-align: center;">
                    <div style="font-size: 0.85rem; font-weight: 700; color: var(--text-muted); text-transform: uppercase;">Monthly Payment (EMI)</div>
                    <div id="emiResult" style="font-size: 2.8rem; font-weight: 800; color: var(--primary); margin: 0.3rem 0 1rem;">$1,864</div>

                    <div style="display: flex; justify-content: space-around; border-top: 1px solid var(--border-subtle); padding-top: 1rem;">
                        <div>
                            <div style="font-size: 0.75rem; color: var(--text-muted); text-transform: uppercase;">Total Interest</div>
                            <div id="totalInterestResult" style="font-size: 1.25rem; font-weight: 800; color: var(--accent-rose);">$197,330</div>
                        </div>
                        <div>
                            <div style="font-size: 0.75rem; color: var(--text-muted); text-transform: uppercase;">Total Amount Payable</div>
                            <div id="totalPaymentResult" style="font-size: 1.25rem; font-weight: 800; color: var(--text-primary);">$447,330</div>
                        </div>
                    </div>
                </div>
            </div>

        <?php elseif ($slug === 'xml-sitemap-generator'): ?>
            <!-- XML Sitemap Generator -->
            <div>
                <div class="form-group">
                    <label class="form-label">Enter URLs (one per line):</label>
                    <textarea id="sitemapUrls" class="form-control font-mono" style="height: 160px;" placeholder="https://example.com/
https://example.com/about
https://example.com/contact
https://example.com/pricing"></textarea>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-bottom: 1.25rem;">
                    <div class="form-group">
                        <label class="form-label">Change Frequency:</label>
                        <select id="sitemapFreq" class="form-control">
                            <option value="daily">Daily</option>
                            <option value="weekly" selected>Weekly</option>
                            <option value="monthly">Monthly</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Default Priority:</label>
                        <select id="sitemapPriority" class="form-control">
                            <option value="1.0">1.0 (Highest)</option>
                            <option value="0.8" selected>0.8 (Normal)</option>
                            <option value="0.5">0.5 (Low)</option>
                        </select>
                    </div>
                </div>

                <div style="margin-bottom: 1.5rem;">
                    <button type="button" id="btnGenerateSitemap" class="btn-primary-gradient"><i class="fa-solid fa-sitemap"></i> Generate Sitemap</button>
                </div>

                <div id="sitemapResultCard" style="display: none;">
                    <label class="form-label">Generated XML Sitemap:</label>
                    <textarea id="sitemapOutput" class="form-control font-mono" style="height: 220px;" readonly></textarea>
                    <div style="display: flex; gap: 0.75rem; margin-top: 1rem;">
                        <button type="button" id="btnCopySitemap" class="btn-secondary"><i class="fa-regular fa-copy"></i> Copy XML</button>
                        <button type="button" id="btnDownloadSitemap" class="btn-primary-gradient"><i class="fa-solid fa-download"></i> Download sitemap.xml</button>
                    </div>
                </div>
            </div>

        <?php elseif ($slug === 'robots-txt-generator'): ?>
            <!-- Robots.txt Generator -->
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 2rem;">
                <div>
                    <div class="form-group">
                        <label class="form-label">Sitemap URL:</label>
                        <input type="url" id="robotsSitemap" class="form-control" value="https://example.com/sitemap.xml">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Disallowed Paths (one per line):</label>
                        <textarea id="robotsDisallow" class="form-control font-mono" style="height: 100px;">/admin/
/private/
/api/</textarea>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Allowed Paths:</label>
                        <textarea id="robotsAllow" class="form-control font-mono" style="height: 60px;">/public/
/assets/</textarea>
                    </div>
                </div>

                <div>
                    <label class="form-label">Generated robots.txt Preview:</label>
                    <textarea id="robotsOutput" class="form-control font-mono" style="height: 260px;" readonly></textarea>
                    <div style="display: flex; gap: 0.75rem; margin-top: 1rem;">
                        <button type="button" id="btnCopyRobots" class="btn-secondary"><i class="fa-regular fa-copy"></i> Copy File</button>
                        <button type="button" id="btnDownloadRobots" class="btn-primary-gradient"><i class="fa-solid fa-download"></i> Download robots.txt</button>
                    </div>
                </div>
            </div>

        <?php elseif ($slug === 'base64-converter'): ?>
            <!-- Base64 Converter -->
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem;">
                <div class="form-group">
                    <label class="form-label">Input Text:</label>
                    <textarea id="base64Input" class="form-control font-mono" style="height: 260px;" placeholder="Enter plain text or Base64 string here..."></textarea>
                </div>
                <div class="form-group">
                    <label class="form-label">Output Result:</label>
                    <textarea id="base64Output" class="form-control font-mono" style="height: 260px;" placeholder="Converted result will show here..." readonly></textarea>
                </div>
            </div>

            <div style="display: flex; gap: 0.75rem; flex-wrap: wrap;">
                <button type="button" id="btnBase64Encode" class="btn-primary-gradient"><i class="fa-solid fa-lock"></i> Encode to Base64</button>
                <button type="button" id="btnBase64Decode" class="btn-secondary"><i class="fa-solid fa-lock-open"></i> Decode Base64</button>
                <button type="button" id="btnCopyBase64" class="btn-secondary"><i class="fa-regular fa-copy"></i> Copy Output</button>
            </div>

        <?php elseif ($slug === 'case-converter'): ?>
            <!-- Case Converter -->
            <div class="form-group">
                <textarea id="caseInput" class="form-control" style="height: 180px;" placeholder="Type or paste text to transform into uppercase, lowercase, title case, camelCase, etc..."></textarea>
            </div>

            <div style="display: flex; gap: 0.5rem; flex-wrap: wrap;">
                <button type="button" id="btnUpper" class="btn-secondary">UPPERCASE</button>
                <button type="button" id="btnLower" class="btn-secondary">lowercase</button>
                <button type="button" id="btnTitle" class="btn-secondary">Title Case</button>
                <button type="button" id="btnSentence" class="btn-secondary">Sentence case</button>
                <button type="button" id="btnCamel" class="btn-secondary">camelCase</button>
                <button type="button" id="btnSnake" class="btn-secondary">snake_case</button>
                <button type="button" id="btnKebab" class="btn-secondary">kebab-case</button>
                <button type="button" id="btnCopyCase" class="btn-primary-gradient" style="margin-left: auto;"><i class="fa-regular fa-copy"></i> Copy Result</button>
            </div>

        <?php elseif ($slug === 'hash-generator'): ?>
            <!-- Hash Generator -->
            <div class="form-group">
                <label class="form-label">Source Text to Hash:</label>
                <input type="text" id="hashInput" class="form-control" placeholder="Enter text to hash...">
            </div>

            <div style="display: flex; flex-direction: column; gap: 1rem; margin-top: 1.5rem;">
                <?php foreach (['sha256' => 'SHA-256', 'sha512' => 'SHA-512', 'sha1' => 'SHA-1', 'md5' => 'MD5'] as $k => $label): ?>
                    <div>
                        <label class="form-label"><?= $label ?>:</label>
                        <div style="display: flex; gap: 0.5rem;">
                            <input type="text" id="hash_<?= $k ?>" class="form-control font-mono" readonly placeholder="Waiting for input...">
                            <button type="button" class="btn-secondary btn-sm btn-copy-hash" data-target="hash_<?= $k ?>"><i class="fa-regular fa-copy"></i> Copy</button>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

        <?php elseif ($slug === 'uuid-generator'): ?>
            <!-- UUID Generator -->
            <div style="display: flex; gap: 1rem; align-items: center; margin-bottom: 1.25rem; flex-wrap: wrap;">
                <div>
                    <label class="form-label" style="margin-bottom: 0.2rem;">Quantity:</label>
                    <select id="uuidCount" class="form-control" style="width: auto;">
                        <option value="1">1 UUID</option>
                        <option value="5">5 UUIDs</option>
                        <option value="10">10 UUIDs</option>
                        <option value="25">25 UUIDs</option>
                    </select>
                </div>
                <div style="display: flex; gap: 1rem; align-items: center; margin-top: 1.2rem;">
                    <label style="display: inline-flex; align-items: center; gap: 0.4rem; font-size: 0.9rem; font-weight: 600;">
                        <input type="checkbox" id="uuidHyphens" checked style="accent-color: var(--primary);"> Hyphens
                    </label>
                    <label style="display: inline-flex; align-items: center; gap: 0.4rem; font-size: 0.9rem; font-weight: 600;">
                        <input type="checkbox" id="uuidUppercase" style="accent-color: var(--primary);"> Uppercase
                    </label>
                </div>
                <div style="margin-top: 1.2rem; margin-left: auto;">
                    <button type="button" id="btnGenerateUUID" class="btn-primary-gradient"><i class="fa-solid fa-arrows-rotate"></i> Regenerate</button>
                </div>
            </div>

            <textarea id="uuidOutput" class="form-control font-mono" style="height: 180px;" readonly></textarea>
            <div style="margin-top: 1rem;">
                <button type="button" id="btnCopyUUID" class="btn-secondary"><i class="fa-regular fa-copy"></i> Copy All UUIDs</button>
            </div>

        <?php elseif ($slug === 'blog-outline-generator'): ?>
            <!-- Blog Outline Generator -->
            <div style="max-width: 680px; margin: 0 auto;">
                <div class="form-group">
                    <label class="form-label">Article Topic or Main Keyword:</label>
                    <div style="display: flex; gap: 0.5rem;">
                        <input type="text" id="outlineTopic" class="form-control" placeholder="e.g. How to Build Fast Web Applications in 2026">
                        <button type="button" id="btnGenerateOutline" class="btn-primary-gradient" style="white-space: nowrap;">
                            <i class="fa-solid fa-wand-magic-sparkles"></i> Generate Outline
                        </button>
                    </div>
                </div>

                <div id="outlineResultCard" style="display: none; margin-top: 1.5rem;">
                    <label class="form-label">Structured Blog Outline (Markdown):</label>
                    <textarea id="outlineOutput" class="form-control font-mono" style="height: 320px;"></textarea>
                    <button type="button" id="btnCopyOutline" class="btn-secondary" style="margin-top: 0.75rem;"><i class="fa-regular fa-copy"></i> Copy Outline</button>
                </div>
            </div>

        <?php elseif ($slug === 'age-calculator'): ?>
            <!-- Age Calculator -->
            <div style="max-width: 600px; margin: 0 auto;">
                <div class="form-group">
                    <label class="form-label">Select Date of Birth:</label>
                    <div style="display: flex; gap: 0.5rem;">
                        <input type="date" id="dobInput" class="form-control" value="2000-01-01">
                        <button type="button" id="btnCalculateAge" class="btn-primary-gradient" style="white-space: nowrap;">
                            <i class="fa-solid fa-calculator"></i> Calculate Age
                        </button>
                    </div>
                </div>

                <div id="ageResultCard" style="display: none; margin-top: 2rem;">
                    <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 1rem; text-align: center; margin-bottom: 1.5rem;">
                        <div style="background: var(--bg-body); padding: 1.25rem; border-radius: var(--radius-md); border: 1px solid var(--border-subtle);">
                            <div id="ageYears" style="font-size: 2.5rem; font-weight: 800; color: var(--primary);">0</div>
                            <div style="font-weight: 700; color: var(--text-muted); text-transform: uppercase; font-size: 0.8rem;">Years</div>
                        </div>
                        <div style="background: var(--bg-body); padding: 1.25rem; border-radius: var(--radius-md); border: 1px solid var(--border-subtle);">
                            <div id="ageMonths" style="font-size: 2.5rem; font-weight: 800; color: var(--accent-violet);">0</div>
                            <div style="font-weight: 700; color: var(--text-muted); text-transform: uppercase; font-size: 0.8rem;">Months</div>
                        </div>
                        <div style="background: var(--bg-body); padding: 1.25rem; border-radius: var(--radius-md); border: 1px solid var(--border-subtle);">
                            <div id="ageDays" style="font-size: 2.5rem; font-weight: 800; color: var(--accent-cyan);">0</div>
                            <div style="font-weight: 700; color: var(--text-muted); text-transform: uppercase; font-size: 0.8rem;">Days</div>
                        </div>
                    </div>

                    <div style="display: flex; justify-content: space-around; background: var(--bg-body); padding: 1rem; border-radius: var(--radius-md); border: 1px solid var(--border-subtle); text-align: center;">
                        <div>
                            <div style="font-size: 0.75rem; color: var(--text-muted); text-transform: uppercase;">Total Days Lived</div>
                            <div id="ageTotalDays" style="font-size: 1.25rem; font-weight: 800;">0</div>
                        </div>
                        <div>
                            <div style="font-size: 0.75rem; color: var(--text-muted); text-transform: uppercase;">Total Hours Lived</div>
                            <div id="ageTotalHours" style="font-size: 1.25rem; font-weight: 800;">0</div>
                        </div>
                    </div>
                </div>
            </div>

        <?php elseif ($slug === 'percentage-calculator'): ?>
            <!-- Percentage Calculator -->
            <div style="display: flex; flex-direction: column; gap: 1.5rem; max-width: 680px; margin: 0 auto;">
                <div style="background: var(--bg-body); padding: 1.5rem; border-radius: var(--radius-md); border: 1px solid var(--border-subtle);">
                    <h4 style="font-weight: 700; margin-bottom: 0.75rem;">1. What is X% of Y?</h4>
                    <div style="display: flex; align-items: center; gap: 0.5rem; flex-wrap: wrap;">
                        <span>What is</span>
                        <input type="number" id="calc1_x" class="form-control" style="width: 100px;" value="15">
                        <span>% of</span>
                        <input type="number" id="calc1_y" class="form-control" style="width: 140px;" value="200">
                        <span>=</span>
                        <div id="calc1_res" style="font-size: 1.35rem; font-weight: 800; color: var(--primary); padding-left: 0.5rem;">30.00</div>
                    </div>
                </div>

                <div style="background: var(--bg-body); padding: 1.5rem; border-radius: var(--radius-md); border: 1px solid var(--border-subtle);">
                    <h4 style="font-weight: 700; margin-bottom: 0.75rem;">2. X is what % of Y?</h4>
                    <div style="display: flex; align-items: center; gap: 0.5rem; flex-wrap: wrap;">
                        <input type="number" id="calc2_x" class="form-control" style="width: 100px;" value="25">
                        <span>is what % of</span>
                        <input type="number" id="calc2_y" class="form-control" style="width: 140px;" value="100">
                        <span>=</span>
                        <div id="calc2_res" style="font-size: 1.35rem; font-weight: 800; color: var(--accent-violet); padding-left: 0.5rem;">25.00%</div>
                    </div>
                </div>
            </div>

        <?php elseif ($slug === 'markdown-to-html'): ?>
            <!-- Markdown to HTML -->
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem;">
                <div class="form-group" style="margin-bottom: 0;">
                    <label class="form-label">Markdown Input:</label>
                    <textarea id="markdownInput" class="form-control font-mono" style="height: 320px;" placeholder="# Heading 1
## Heading 2
Write text in **bold** or *italic*.
- Bullet 1
- Bullet 2"></textarea>
                </div>
                <div class="form-group" style="margin-bottom: 0;">
                    <label class="form-label">Live Preview:</label>
                    <div id="markdownPreview" style="height: 320px; overflow-y: auto; background: var(--bg-body); border: 1px solid var(--border-subtle); border-radius: var(--radius-md); padding: 1.25rem;"></div>
                </div>
            </div>
            <textarea id="htmlOutput" style="display: none;"></textarea>
            <div style="margin-top: 1rem;">
                <button type="button" id="btnCopyHtmlOutput" class="btn-primary-gradient"><i class="fa-regular fa-copy"></i> Copy HTML Code</button>
            </div>

        <?php else: ?>
            <!-- Versatile Universal Workspace for Any Tool -->
            <div class="file-dropzone" style="margin-bottom: 1.5rem;">
                <i class="fa-solid <?= e($tool['icon']) ?> dropzone-icon"></i>
                <div class="dropzone-title">Ready to run <?= e($tool['name']) ?></div>
                <div class="dropzone-subtitle"><?= e($tool['short_description']) ?></div>
            </div>

            <div class="form-group">
                <label class="form-label">Input Data / Text:</label>
                <textarea id="genericInput" class="form-control font-mono" style="height: 160px;" placeholder="Type, paste, or configure your input..."></textarea>
            </div>

            <div style="display: flex; gap: 0.75rem;">
                <button type="button" class="btn-primary-gradient" onclick="showToast('Processing complete!', 'success')">
                    <i class="fa-solid fa-play"></i> Run <?= e($tool['name']) ?>
                </button>
                <button type="button" class="btn-secondary" onclick="copyToClipboard(document.getElementById('genericInput').value, 'Copied input to clipboard')">
                    <i class="fa-regular fa-copy"></i> Copy
                </button>
            </div>
        <?php endif; ?>

    </div>

    <!-- In-Content Ad Placeholder -->
    <?= render_ad_slot('tool_middle', 'responsive_banner') ?>

    <!-- ==============================================================
         Tool Guide: Features & How To Use
         ============================================================== -->
    <div class="guide-content-grid">
        <!-- How to Use Card -->
        <div class="guide-card">
            <h3><i class="fa-solid fa-book-open"></i> How to use <?= e($tool['name']) ?></h3>
            <p style="color: var(--text-secondary); margin-bottom: 1rem;"><?= e($tool['description'] ?? $tool['short_description']) ?></p>
            
            <ol>
                <?php 
                $steps = explode("\n", $tool['how_to_use'] ?? "1. Upload or input your content.\n2. Configure your options.\n3. Click execute to process.\n4. Copy or download your result.");
                foreach ($steps as $step):
                    if (!trim($step)) continue;
                ?>
                    <li><?= e(preg_replace('/^\d+\.\s*/', '', trim($step))) ?></li>
                <?php endforeach; ?>
            </ol>
        </div>

        <!-- Key Features Card -->
        <div class="guide-card">
            <h3><i class="fa-solid fa-list-check"></i> Key Features</h3>
            <ul class="features-list">
                <?php 
                $features = explode(";", $tool['features'] ?? "100% Free;Zero registration required;High-speed processing;Privacy guaranteed;Instant export");
                foreach ($features as $feat):
                    if (!trim($feat)) continue;
                ?>
                    <li><i class="fa-solid fa-circle-check"></i> <span><?= e(trim($feat)) ?></span></li>
                <?php endforeach; ?>
            </ul>
        </div>
    </div>

    <!-- Tool Specific FAQs Accordion -->
    <?php if (!empty($faqs)): ?>
        <div style="margin-bottom: 4rem;">
            <h3 style="font-size: 1.5rem; font-weight: 800; margin-bottom: 1.5rem; color: var(--text-primary);">
                <i class="fa-solid fa-circle-question" style="color: var(--primary);"></i> Questions about <?= e($tool['name']) ?>
            </h3>
            <div class="faq-section" style="margin: 0;">
                <?php foreach ($faqs as $fIdx => $faq): ?>
                    <div class="faq-item <?= $fIdx === 0 ? 'active' : '' ?>">
                        <button type="button" class="faq-question">
                            <span><?= e($faq['question']) ?></span>
                            <i class="fa-solid fa-chevron-down"></i>
                        </button>
                        <div class="faq-answer">
                            <p><?= e($faq['answer']) ?></p>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    <?php endif; ?>

    <!-- Related Tools Section -->
    <?php if (!empty($relatedTools)): ?>
        <div style="margin-bottom: 4rem;">
            <div class="section-header">
                <div class="section-title-wrap">
                    <h2><i class="fa-solid fa-shuffle" style="color: var(--primary);"></i> Related <?= e($category['name']) ?></h2>
                    <p class="section-subtitle">More utilities to supercharge your workflow.</p>
                </div>
            </div>
            <div class="tools-grid">
                <?php foreach ($relatedTools as $rel): ?>
                    <a href="<?= url('tool.php?slug=' . $rel['slug']) ?>" class="tool-card">
                        <div class="tool-card-top">
                            <div class="tool-icon-wrapper">
                                <i class="fa-solid <?= e($rel['icon']) ?>"></i>
                            </div>
                        </div>
                        <h3 class="tool-title"><?= e($rel['name']) ?></h3>
                        <p class="tool-desc"><?= e($rel['short_description']) ?></p>
                    </a>
                <?php endforeach; ?>
            </div>
        </div>
    <?php endif; ?>
</div>

<script>
    // Initialize this tool in ToolsEngine and record visit
    document.addEventListener('DOMContentLoaded', () => {
        ToolsEngine.init('<?= e($slug) ?>');
        recordToolVisit('<?= addslashes($tool['name']) ?>', '<?= e($slug) ?>', '<?= e($tool['icon']) ?>', window.location.href);
        // Log tool execution in background
        fetch('api/index.php?action=log_run&slug=<?= urlencode($slug) ?>').catch(() => {});
    });
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
