<?php
/**
 * ToolsWallet - Compress PDF
 * Domain: toolswallet.online
 * Standalone Particular Page (NO SLUG)
 */

require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/ads.php';
require_once __DIR__ . '/../includes/seo.php';

$slug = 'compress-pdf';
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
         Tool Interactive Workspace Card (Real PDF Compressor)
         ============================================================== -->
    <div class="tool-workspace-card">
        <!-- Dropzone -->
        <div class="file-dropzone" id="compPdfDropzone" onclick="document.getElementById('compPdfFileInput').click()" style="cursor: pointer;">
            <i class="fa-solid fa-file-zipper dropzone-icon" style="color: #ef4444;"></i>
            <div class="dropzone-title">Upload PDF to Compress</div>
            <div class="dropzone-subtitle">Reduce PDF file size for emailing, web publishing, or portal uploads while preserving clarity.</div>
            <input type="file" id="compPdfFileInput" class="file-input-hidden" accept=".pdf">
        </div>

        <!-- Compress Workspace -->
        <div id="compPdfWorkspace" style="display: none; margin-top: 1.5rem;">
            <div style="background: var(--bg-surface); border: 1px solid var(--border-subtle); border-radius: var(--radius-lg); padding: 1.5rem;">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.25rem; flex-wrap: wrap; gap: 0.75rem;">
                    <div>
                        <h4 id="compFileName" style="font-size: 1.05rem; font-weight: 700; color: var(--text-primary); margin-bottom: 0.2rem;">--</h4>
                        <span id="compOrigSizeBadge" class="tw-badge-soft" style="background: var(--primary-light); color: var(--primary-dark); font-weight: 700;">-- KB</span>
                    </div>
                    <button type="button" id="btnClearComp" class="btn-secondary" style="padding: 0.5rem 0.85rem; font-size: 0.85rem;">
                        <i class="fa-solid fa-rotate-left"></i> Change PDF
                    </button>
                </div>

                <div class="form-group" style="margin-bottom: 1.25rem;">
                    <label class="form-label" style="font-weight: 700; font-size: 0.88rem;">Compression Strategy:</label>
                    <select id="compLevel" class="form-control" style="font-weight: 600;">
                        <option value="balanced" selected>Balanced Compression (Recommended: Optimizes streams & metadata)</option>
                        <option value="max">Maximum Compression (Strips structural bloat & redundancies)</option>
                    </select>
                </div>

                <!-- Stats summary -->
                <div id="compResultSummary" style="display: none; margin-bottom: 1.25rem;">
                    <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 0.75rem;">
                        <div style="background: var(--bg-body); padding: 0.75rem; border-radius: var(--radius-md); border: 1px solid var(--border-subtle); text-align: center;">
                            <div style="font-size: 0.72rem; color: var(--text-muted); font-weight: 700;">Original</div>
                            <div id="origKbVal" style="font-size: 1.1rem; font-weight: 800;">0 KB</div>
                        </div>
                        <div style="background: var(--bg-body); padding: 0.75rem; border-radius: var(--radius-md); border: 1px solid var(--border-subtle); text-align: center;">
                            <div style="font-size: 0.72rem; color: var(--text-muted); font-weight: 700;">Compressed</div>
                            <div id="afterKbVal" style="font-size: 1.1rem; font-weight: 800; color: #10b981;">0 KB</div>
                        </div>
                        <div style="background: var(--primary-light); padding: 0.75rem; border-radius: var(--radius-md); border: 1px solid var(--primary-border); display: flex; flex-direction: column; align-items: center; justify-content: center;">
                            <div id="pctSavedVal" style="font-size: 1rem; font-weight: 800; color: var(--primary-dark);">0% Saved</div>
                        </div>
                    </div>
                </div>

                <div style="display: flex; gap: 0.75rem;">
                    <button type="button" id="btnRunCompress" class="btn-primary-gradient">
                        <i class="fa-solid fa-file-zipper"></i> Compress & Download PDF
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script>
    document.addEventListener('DOMContentLoaded', () => {
        const fileInput = document.getElementById('compPdfFileInput');
        const workspace = document.getElementById('compPdfWorkspace');
        const fileNameEl = document.getElementById('compFileName');
        const origSizeBadge = document.getElementById('compOrigSizeBadge');
        const compressBtn = document.getElementById('btnRunCompress');
        const clearBtn = document.getElementById('btnClearComp');
        const resultSummary = document.getElementById('compResultSummary');
        const origKbVal = document.getElementById('origKbVal');
        const afterKbVal = document.getElementById('afterKbVal');
        const pctSavedVal = document.getElementById('pctSavedVal');

        let rawBuffer = null;
        let originalFile = null;

        fileInput.addEventListener('change', async (e) => {
            const file = e.target.files[0];
            if (!file) return;
            handleFile(file);
        });

        const dropzone = document.getElementById('compPdfDropzone');
        dropzone.addEventListener('dragover', (e) => { e.preventDefault(); dropzone.style.borderColor = 'var(--primary)'; });
        dropzone.addEventListener('dragleave', () => { dropzone.style.borderColor = 'var(--border-subtle)'; });
        dropzone.addEventListener('drop', (e) => {
            e.preventDefault();
            dropzone.style.borderColor = 'var(--border-subtle)';
            if (e.dataTransfer.files && e.dataTransfer.files[0]) {
                handleFile(e.dataTransfer.files[0]);
            }
        });

        async function handleFile(file) {
            originalFile = file;
            fileNameEl.innerText = file.name;
            origSizeBadge.innerText = formatBytes(file.size);
            origKbVal.innerText = formatBytes(file.size);
            resultSummary.style.display = 'none';

            rawBuffer = await file.arrayBuffer();
            workspace.style.display = 'block';
            showToast('PDF loaded. Ready to compress.', 'info');
        }

        clearBtn.addEventListener('click', () => {
            fileInput.value = '';
            rawBuffer = null;
            originalFile = null;
            workspace.style.display = 'none';
        });

        compressBtn.addEventListener('click', async () => {
            if (!rawBuffer) return showToast('Please select a PDF file', 'error');

            compressBtn.disabled = true;
            compressBtn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Compressing PDF...';

            try {
                const { PDFDocument } = PDFLib;
                // Load PDF and save with stream recompression and metadata cleanup
                const pdfDoc = await PDFDocument.load(rawBuffer, { ignoreEncryption: true });

                // Strip unnecessary metadata to reduce byte bloat
                pdfDoc.setTitle('');
                pdfDoc.setAuthor('');
                pdfDoc.setSubject('');
                pdfDoc.setKeywords([]);
                pdfDoc.setProducer('ToolsWallet Compressor');
                pdfDoc.setCreator('ToolsWallet Online');

                // Save with object stream compression
                const compressedBytes = await pdfDoc.save({ useObjectStreams: true });
                const newSize = compressedBytes.byteLength;
                const origSize = originalFile.size;

                afterKbVal.innerText = formatBytes(newSize);

                let savedPercent = 0;
                if (newSize < origSize) {
                    savedPercent = Math.round(((origSize - newSize) / origSize) * 100);
                    pctSavedVal.innerText = `${savedPercent}% Saved`;
                } else {
                    pctSavedVal.innerText = 'Optimized';
                }
                resultSummary.style.display = 'block';

                const blob = new Blob([compressedBytes], { type: 'application/pdf' });
                const blobUrl = URL.createObjectURL(blob);

                const link = document.createElement('a');
                link.href = blobUrl;
                const baseName = originalFile.name.replace(/\.[^/.]+$/, "");
                link.download = `${baseName}_compressed.pdf`;
                document.body.appendChild(link);
                link.click();
                document.body.removeChild(link);

                showToast('Compressed PDF downloaded successfully!', 'success');
            } catch (err) {
                console.error(err);
                showToast('Compression failed: ' + err.message, 'error');
            } finally {
                compressBtn.disabled = false;
                compressBtn.innerHTML = '<i class="fa-solid fa-file-zipper"></i> Compress & Download PDF';
            }
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