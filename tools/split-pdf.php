<?php
/**
 * ToolsWallet - Split PDF
 * Domain: toolswallet.online
 * Standalone Particular Page (NO SLUG)
 */

require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/ads.php';
require_once __DIR__ . '/../includes/seo.php';

$slug = 'split-pdf';
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
         Tool Interactive Workspace Card (Real PDF Splitter)
         ============================================================== -->
    <div class="tool-workspace-card">
        <!-- Dropzone -->
        <div class="file-dropzone" id="splitPdfDropzone" onclick="document.getElementById('splitPdfFileInput').click()" style="cursor: pointer;">
            <i class="fa-solid fa-scissors dropzone-icon" style="color: #ef4444;"></i>
            <div class="dropzone-title">Upload PDF to Split & Extract Pages</div>
            <div class="dropzone-subtitle">Extract single pages, custom page ranges (e.g. 1-3, 5), or split documents instantly.</div>
            <input type="file" id="splitPdfFileInput" class="file-input-hidden" accept=".pdf">
        </div>

        <!-- Split Options Workspace -->
        <div id="splitPdfWorkspace" style="display: none; margin-top: 1.5rem;">
            <div style="background: var(--bg-surface); border: 1px solid var(--border-subtle); border-radius: var(--radius-lg); padding: 1.5rem;">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.25rem; flex-wrap: wrap; gap: 0.75rem;">
                    <div>
                        <h4 id="splitFileName" style="font-size: 1.05rem; font-weight: 700; color: var(--text-primary); margin-bottom: 0.2rem;">--</h4>
                        <span id="splitPageCountBadge" class="tw-badge-soft" style="background: var(--primary-light); color: var(--primary-dark); font-weight: 700;">-- Pages Detected</span>
                    </div>
                    <button type="button" id="btnClearSplit" class="btn-secondary" style="padding: 0.5rem 0.85rem; font-size: 0.85rem;">
                        <i class="fa-solid fa-rotate-left"></i> Change PDF
                    </button>
                </div>

                <div class="form-group" style="margin-bottom: 1.25rem;">
                    <label class="form-label" style="font-weight: 700; font-size: 0.9rem;">Pages to Extract:</label>
                    <input type="text" id="splitPageRanges" class="form-control" placeholder="e.g. 1-3, 5, 8" value="1">
                    <div class="form-hint">Specify comma-separated page numbers or ranges (e.g. "1-2, 4"). Leave blank to extract all.</div>
                </div>

                <div style="display: flex; gap: 0.75rem;">
                    <button type="button" id="btnRunSplit" class="btn-primary-gradient">
                        <i class="fa-solid fa-scissors"></i> Extract & Download PDF
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script>
    document.addEventListener('DOMContentLoaded', () => {
        const fileInput = document.getElementById('splitPdfFileInput');
        const workspace = document.getElementById('splitPdfWorkspace');
        const fileNameEl = document.getElementById('splitFileName');
        const pageBadgeEl = document.getElementById('splitPageCountBadge');
        const pageRangeInput = document.getElementById('splitPageRanges');
        const splitBtn = document.getElementById('btnRunSplit');
        const clearBtn = document.getElementById('btnClearSplit');

        let loadedPdfDoc = null;
        let totalPages = 0;
        let originalFileName = '';

        fileInput.addEventListener('change', async (e) => {
            const file = e.target.files[0];
            if (!file) return;
            handleFile(file);
        });

        const dropzone = document.getElementById('splitPdfDropzone');
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
            if (!window.PDFLib) return showToast('PDF engine loading...', 'info');

            originalFileName = file.name;
            fileNameEl.innerText = file.name;

            try {
                const arrayBuffer = await file.arrayBuffer();
                loadedPdfDoc = await PDFLib.PDFDocument.load(arrayBuffer);
                totalPages = loadedPdfDoc.getPageCount();

                pageBadgeEl.innerText = `${totalPages} Page(s) Total`;
                pageRangeInput.value = (totalPages > 1) ? `1-${Math.min(3, totalPages)}` : '1';
                workspace.style.display = 'block';
                showToast(`PDF loaded successfully (${totalPages} pages)`, 'success');
            } catch (err) {
                console.error(err);
                showToast('Failed to parse PDF document', 'error');
            }
        }

        clearBtn.addEventListener('click', () => {
            fileInput.value = '';
            loadedPdfDoc = null;
            totalPages = 0;
            workspace.style.display = 'none';
        });

        splitBtn.addEventListener('click', async () => {
            if (!loadedPdfDoc) return showToast('Please select a PDF file', 'error');

            const rangeStr = pageRangeInput.value.trim();
            const indicesToExtract = parsePageRanges(rangeStr, totalPages);

            if (indicesToExtract.length === 0) {
                return showToast('Invalid page range specified', 'error');
            }

            splitBtn.disabled = true;
            splitBtn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Extracting Pages...';

            try {
                const { PDFDocument } = PDFLib;
                const newPdf = await PDFDocument.create();
                const copiedPages = await newPdf.copyPages(loadedPdfDoc, indicesToExtract);
                copiedPages.forEach(p => newPdf.addPage(p));

                const pdfBytes = await newPdf.save();
                const blob = new Blob([pdfBytes], { type: 'application/pdf' });
                const blobUrl = URL.createObjectURL(blob);

                const link = document.createElement('a');
                link.href = blobUrl;
                const baseName = originalFileName.replace(/\.[^/.]+$/, "");
                link.download = `${baseName}_extracted.pdf`;
                document.body.appendChild(link);
                link.click();
                document.body.removeChild(link);

                showToast(`Extracted ${indicesToExtract.length} page(s) successfully!`, 'success');
            } catch (err) {
                console.error(err);
                showToast('Error extracting pages: ' + err.message, 'error');
            } finally {
                splitBtn.disabled = false;
                splitBtn.innerHTML = '<i class="fa-solid fa-scissors"></i> Extract & Download PDF';
            }
        });

        function parsePageRanges(str, maxPages) {
            if (!str) {
                // all pages
                return Array.from({ length: maxPages }, (_, i) => i);
            }
            const parts = str.split(',');
            const resultSet = new Set();

            parts.forEach(part => {
                const trimmed = part.trim();
                if (trimmed.includes('-')) {
                    const [startStr, endStr] = trimmed.split('-');
                    const start = parseInt(startStr, 10);
                    const end = parseInt(endStr, 10);
                    if (!isNaN(start) && !isNaN(end)) {
                        for (let p = Math.max(1, start); p <= Math.min(maxPages, end); p++) {
                            resultSet.add(p - 1); // 0-based index
                        }
                    }
                } else {
                    const page = parseInt(trimmed, 10);
                    if (!isNaN(page) && page >= 1 && page <= maxPages) {
                        resultSet.add(page - 1);
                    }
                }
            });

            return Array.from(resultSet).sort((a, b) => a - b);
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