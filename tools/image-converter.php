<?php
/**
 * ToolsWallet - Image Format Converter
 * Domain: toolswallet.online
 * Standalone Particular Page (NO SLUG)
 */

require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/ads.php';
require_once __DIR__ . '/../includes/seo.php';

$slug = 'image-converter';
$tool = Database::getTool($slug);

if (!$tool) {
    header("HTTP/1.0 404 Not Found");
    $pageSeo = ['title' => 'Tool Not Found | ToolsWallet'];
    require_once __DIR__ . '/../includes/header.php';
    ?>
    <div class="container" style="padding: 5rem 1rem; text-align: center;">
        <i class="fa-solid fa-screwdriver-wrench" style="font-size: 3rem; color: #ef4444; margin-bottom: 1rem;"></i>
        <h2>Tool Not Found</h2>
        <p style="color: var(--text-secondary); margin-top: 0.5rem;">The utility you requested does not exist or has been updated.</p>
        <a href="<?= url() ?>" class="btn-primary-gradient" style="margin-top: 1.5rem; display: inline-flex;">Explore All Tools</a>
    </div>
    <?php
    require_once __DIR__ . '/../includes/footer.php';
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
    'canonical' => tool_url($tool['slug']),
    'tool' => $tool,
    'breadcrumbs' => [
        ['name' => 'Home', 'url' => url()],
        ['name' => $category['name'], 'url' => category_url($category['slug'])],
        ['name' => $tool['name'], 'url' => tool_url($tool['slug'])]
    ],
    'faqs' => $faqs
];

require_once __DIR__ . '/../includes/header.php';
?>

<div class="container">
    <!-- Breadcrumbs -->
    <nav class="breadcrumbs" aria-label="Breadcrumb">
        <a href="<?= url() ?>"><i class="fa-solid fa-house"></i> Home</a>
        <span class="separator">/</span>
        <a href="<?= category_url($category['slug']) ?>"><?= e($category['name']) ?></a>
        <span class="separator">/</span>
        <span class="current"><?= e($tool['name']) ?></span>
    </nav>

    <!-- Tool Page Header -->
    <div class="tool-page-header">
        <div class="tool-header-left">
            <div class="tool-page-icon">
                <i class="<?= e(fa_class($tool['icon'])) ?>"></i>
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
         Tool Interactive Workspace Card (Full Image Format Converter)
         ============================================================== -->
    <div class="tool-workspace-card">
        <!-- Dropzone -->
        <div class="file-dropzone" id="imgConvDropzone" onclick="document.getElementById('imgConvFileInput').click()" style="cursor: pointer;">
            <i class="fa-solid fa-file-image dropzone-icon" style="color: var(--primary);"></i>
            <div class="dropzone-title">Upload Image to Convert</div>
            <div class="dropzone-subtitle">Convert between WebP, PNG, JPG, and GIF instantly with zero quality loss (100% Client-Side Privacy)</div>
            <input type="file" id="imgConvFileInput" class="file-input-hidden" accept="image/*">
        </div>

        <!-- Conversion Workspace (Hidden until file selected) -->
        <div id="imgConvWorkspace" style="display: none; margin-top: 1.5rem;">
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 1.5rem; align-items: flex-start;">
                <!-- Controls Column -->
                <div style="background: var(--bg-surface); border: 1px solid var(--border-subtle); border-radius: var(--radius-lg); padding: 1.5rem;">
                    <h3 style="font-size: 1.1rem; font-weight: 700; margin-bottom: 1rem; color: var(--text-primary);">
                        <i class="fa-solid fa-sliders" style="color: var(--primary);"></i> Conversion Settings
                    </h3>

                    <div class="form-group" style="margin-bottom: 1rem;">
                        <label class="form-label" style="font-weight: 600; font-size: 0.9rem;">Target Format:</label>
                        <select id="targetFormat" class="form-control" style="font-weight: 600;">
                            <option value="image/webp" selected>WebP (Next-Gen, High Compression)</option>
                            <option value="image/png">PNG (Lossless, Transparent)</option>
                            <option value="image/jpeg">JPG / JPEG (Universal)</option>
                        </select>
                    </div>

                    <div class="form-group" style="margin-bottom: 1.25rem;">
                        <div style="display: flex; justify-content: space-between; margin-bottom: 0.4rem;">
                            <label class="form-label" style="font-weight: 600; font-size: 0.9rem;">Output Quality:</label>
                            <span id="qualityDisplay" style="font-weight: 700; color: var(--primary);">85%</span>
                        </div>
                        <input type="range" id="qualityRange" min="20" max="100" value="85" style="width: 100%; accent-color: var(--primary);">
                    </div>

                    <!-- Size Comparison Metrics -->
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 0.75rem; margin-bottom: 1.25rem;">
                        <div style="background: var(--bg-body); padding: 0.85rem; border-radius: var(--radius-md); border: 1px solid var(--border-subtle);">
                            <div style="font-size: 0.75rem; font-weight: 700; color: var(--text-muted); text-transform: uppercase;">Original File</div>
                            <div id="origSizeDisplay" style="font-size: 1.15rem; font-weight: 800; color: var(--text-primary); margin-top: 0.2rem;">0 KB</div>
                            <div id="origFormatDisplay" style="font-size: 0.75rem; color: var(--text-secondary);">--</div>
                        </div>
                        <div style="background: var(--primary-light); padding: 0.85rem; border-radius: var(--radius-md); border: 1px solid var(--primary-border);">
                            <div style="font-size: 0.75rem; font-weight: 700; color: var(--primary-dark); text-transform: uppercase;">Converted File</div>
                            <div id="newSizeDisplay" style="font-size: 1.15rem; font-weight: 800; color: var(--primary-dark); margin-top: 0.2rem;">0 KB</div>
                            <div id="savingsDisplay" style="font-size: 0.75rem; font-weight: 700; color: var(--primary);">0% Saved</div>
                        </div>
                    </div>

                    <div style="display: flex; gap: 0.75rem; flex-wrap: wrap;">
                        <button type="button" id="btnDownloadConverted" class="btn-primary-gradient" style="flex: 1; justify-content: center;">
                            <i class="fa-solid fa-download"></i> Download Converted Image
                        </button>
                        <button type="button" id="btnResetConv" class="btn-secondary" style="padding: 0.75rem 1rem;">
                            <i class="fa-solid fa-rotate-left"></i>
                        </button>
                    </div>
                </div>

                <!-- Preview Column -->
                <div style="background: var(--bg-surface); border: 1px solid var(--border-subtle); border-radius: var(--radius-lg); padding: 1.5rem; text-align: center;">
                    <h3 style="font-size: 1.1rem; font-weight: 700; margin-bottom: 1rem; color: var(--text-primary); text-align: left;">
                        <i class="fa-solid fa-eye" style="color: var(--primary);"></i> Converted Preview
                    </h3>
                    <div style="background: repeating-conic-gradient(#f1f5f9 0% 25%, transparent 0% 50%) 50% / 20px 20px; border-radius: var(--radius-md); padding: 1rem; display: flex; align-items: center; justify-content: center; min-height: 240px; border: 1px solid var(--border-subtle);">
                        <img id="convertedPreviewImg" alt="Converted Preview" style="max-width: 100%; max-height: 280px; object-fit: contain; border-radius: var(--radius-sm); box-shadow: 0 4px 12px rgba(0,0,0,0.08);">
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
    document.addEventListener('DOMContentLoaded', () => {
        const fileInput = document.getElementById('imgConvFileInput');
        const workspace = document.getElementById('imgConvWorkspace');
        const formatSelect = document.getElementById('targetFormat');
        const qualityRange = document.getElementById('qualityRange');
        const qualityDisplay = document.getElementById('qualityDisplay');
        const origSizeDisplay = document.getElementById('origSizeDisplay');
        const origFormatDisplay = document.getElementById('origFormatDisplay');
        const newSizeDisplay = document.getElementById('newSizeDisplay');
        const savingsDisplay = document.getElementById('savingsDisplay');
        const previewImg = document.getElementById('convertedPreviewImg');
        const downloadBtn = document.getElementById('btnDownloadConverted');
        const resetBtn = document.getElementById('btnResetConv');

        let originalFile = null;
        let convertedBlob = null;
        let originalImage = null;

        // File Selection
        fileInput.addEventListener('change', (e) => {
            const file = e.target.files[0];
            if (!file) return;
            handleSelectedFile(file);
        });

        // Drag & Drop
        const dropzone = document.getElementById('imgConvDropzone');
        dropzone.addEventListener('dragover', (e) => { e.preventDefault(); dropzone.style.borderColor = 'var(--primary)'; });
        dropzone.addEventListener('dragleave', () => { dropzone.style.borderColor = 'var(--border-subtle)'; });
        dropzone.addEventListener('drop', (e) => {
            e.preventDefault();
            dropzone.style.borderColor = 'var(--border-subtle)';
            if (e.dataTransfer.files && e.dataTransfer.files[0]) {
                handleSelectedFile(e.dataTransfer.files[0]);
            }
        });

        function handleSelectedFile(file) {
            originalFile = file;
            origSizeDisplay.innerText = formatBytes(file.size);
            origFormatDisplay.innerText = file.type || file.name.split('.').pop().toUpperCase();

            const reader = new FileReader();
            reader.onload = (event) => {
                const img = new Image();
                img.onload = () => {
                    originalImage = img;
                    workspace.style.display = 'block';
                    processConversion();
                    showToast('Image loaded successfully!', 'success');
                };
                img.src = event.target.result;
            };
            reader.readAsDataURL(file);
        }

        function processConversion() {
            if (!originalImage) return;

            const targetMime = formatSelect.value;
            const quality = parseInt(qualityRange.value, 10) / 100;

            const canvas = document.createElement('canvas');
            canvas.width = originalImage.naturalWidth || originalImage.width;
            canvas.height = originalImage.naturalHeight || originalImage.height;

            const ctx = canvas.getContext('2d');
            // If JPEG, fill white background for transparency safety
            if (targetMime === 'image/jpeg') {
                ctx.fillStyle = '#FFFFFF';
                ctx.fillRect(0, 0, canvas.width, canvas.height);
            }
            ctx.drawImage(originalImage, 0, 0);

            canvas.toBlob((blob) => {
                if (!blob) return;
                convertedBlob = blob;
                const blobUrl = URL.createObjectURL(blob);
                previewImg.src = blobUrl;

                newSizeDisplay.innerText = formatBytes(blob.size);

                const origBytes = originalFile.size;
                const newBytes = blob.size;
                if (newBytes < origBytes) {
                    const pct = Math.round(((origBytes - newBytes) / origBytes) * 100);
                    savingsDisplay.innerText = `${pct}% Smaller`;
                    savingsDisplay.style.color = '#10b981';
                } else {
                    const pct = Math.round(((newBytes - origBytes) / origBytes) * 100);
                    savingsDisplay.innerText = `+${pct}% (High Quality)`;
                    savingsDisplay.style.color = 'var(--text-muted)';
                }
            }, targetMime, quality);
        }

        formatSelect.addEventListener('change', processConversion);
        qualityRange.addEventListener('input', () => {
            qualityDisplay.innerText = `${qualityRange.value}%`;
            processConversion();
        });

        downloadBtn.addEventListener('click', () => {
            if (!convertedBlob) return showToast('Please select an image first', 'error');

            const extMap = {
                'image/webp': 'webp',
                'image/png': 'png',
                'image/jpeg': 'jpg'
            };
            const ext = extMap[formatSelect.value] || 'png';
            const baseName = (originalFile.name.replace(/\.[^/.]+$/, "")) || 'converted_image';
            const filename = `${baseName}.${ext}`;

            const link = document.createElement('a');
            link.href = URL.createObjectURL(convertedBlob);
            link.download = filename;
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
            showToast(`Downloaded ${filename}!`, 'success');
        });

        resetBtn.addEventListener('click', () => {
            fileInput.value = '';
            originalFile = null;
            originalImage = null;
            convertedBlob = null;
            workspace.style.display = 'none';
            showToast('Reset workspace', 'info');
        });

        function formatBytes(bytes) {
            if (bytes === 0) return '0 Bytes';
            const k = 1024;
            const sizes = ['Bytes', 'KB', 'MB', 'GB'];
            const i = Math.floor(Math.log(bytes) / Math.log(k));
            return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
        }
    });
    </script>

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
                    <a href="<?= tool_url($rel['slug']) ?>" class="tool-card">
                        <div class="tool-card-top">
                            <div class="tool-icon-wrapper">
                                <i class="<?= e(fa_class($rel['icon'])) ?>"></i>
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
        fetch('<?= url("api/index.php?action=log_run&slug=" . urlencode($slug)) ?>').catch(() => {});
    });
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>