<?php
/**
 * ToolsWallet - PDF to Images
 * Domain: toolswallet.online
 * Standalone Particular Page (NO SLUG)
 */

require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/ads.php';
require_once __DIR__ . '/../includes/seo.php';

$slug = 'pdf-to-images';
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
         Tool Interactive Workspace Card (Real PDF to Images Converter)
         ============================================================== -->
    <div class="tool-workspace-card">
        <!-- Dropzone -->
        <div class="file-dropzone" id="pdfImgDropzone" onclick="document.getElementById('pdfImgFileInput').click()" style="cursor: pointer;">
            <i class="fa-solid fa-file-image dropzone-icon" style="color: #ef4444;"></i>
            <div class="dropzone-title">Upload PDF to Extract Images</div>
            <div class="dropzone-subtitle">Convert every page of your PDF document into crisp, high-resolution PNG or JPG images.</div>
            <input type="file" id="pdfImgFileInput" class="file-input-hidden" accept=".pdf">
        </div>

        <!-- Render Workspace -->
        <div id="pdfImgWorkspace" style="display: none; margin-top: 1.5rem;">
            <div style="background: var(--bg-surface); border: 1px solid var(--border-subtle); border-radius: var(--radius-lg); padding: 1.5rem; margin-bottom: 1.5rem;">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem; flex-wrap: wrap; gap: 0.75rem;">
                    <div>
                        <h4 id="pdfImgFileName" style="font-size: 1.05rem; font-weight: 700; color: var(--text-primary); margin-bottom: 0.2rem;">--</h4>
                        <span id="pdfImgPageBadge" class="tw-badge-soft" style="background: var(--primary-light); color: var(--primary-dark); font-weight: 700;">-- Pages</span>
                    </div>
                    <div style="display: flex; gap: 0.75rem; align-items: center;">
                        <select id="imgOutputFormat" class="form-control" style="font-weight: 600; width: auto; padding: 0.45rem 0.85rem;">
                            <option value="image/png" selected>PNG (Lossless High-Res)</option>
                            <option value="image/jpeg">JPG (Smaller Size)</option>
                        </select>
                        <button type="button" id="btnExtractAllPages" class="btn-primary-gradient">
                            <i class="fa-solid fa-images"></i> Render All Pages
                        </button>
                    </div>
                </div>
            </div>

            <!-- Rendered Pages Grid -->
            <div>
                <h4 style="font-size: 1rem; font-weight: 700; color: var(--text-primary); margin-bottom: 1rem;">
                    <i class="fa-solid fa-photo-film" style="color: var(--primary);"></i> Extracted Pages
                </h4>
                <div id="pdfPagesGrid" style="display: grid; grid-template-columns: repeat(auto-fill, minmax(200px, 1fr)); gap: 1.25rem;"></div>
            </div>
        </div>
    </div>

    <script>
    document.addEventListener('DOMContentLoaded', () => {
        const fileInput = document.getElementById('pdfImgFileInput');
        const workspace = document.getElementById('pdfImgWorkspace');
        const fileNameEl = document.getElementById('pdfImgFileName');
        const pageBadgeEl = document.getElementById('pdfImgPageBadge');
        const formatSelect = document.getElementById('imgOutputFormat');
        const extractBtn = document.getElementById('btnExtractAllPages');
        const pagesGrid = document.getElementById('pdfPagesGrid');

        let rawBuffer = null;
        let pdfDoc = null;
        let totalPages = 0;
        let baseDocName = '';

        fileInput.addEventListener('change', async (e) => {
            const file = e.target.files[0];
            if (!file) return;
            handleFile(file);
        });

        const dropzone = document.getElementById('pdfImgDropzone');
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
            if (!window.pdfjsLib) return showToast('PDF.js library is loading, please wait...', 'info');

            baseDocName = file.name.replace(/\.[^/.]+$/, "");
            fileNameEl.innerText = file.name;

            try {
                rawBuffer = await file.arrayBuffer();
                const loadingTask = pdfjsLib.getDocument({ data: rawBuffer });
                pdfDoc = await loadingTask.promise;
                totalPages = pdfDoc.numPages;

                pageBadgeEl.innerText = `${totalPages} Page(s) Detected`;
                workspace.style.display = 'block';
                pagesGrid.innerHTML = '';
                showToast(`PDF loaded (${totalPages} pages). Click "Render All Pages" to convert.`, 'success');
            } catch (err) {
                console.error(err);
                showToast('Failed to load PDF: ' + err.message, 'error');
            }
        }

        extractBtn.addEventListener('click', async () => {
            if (!pdfDoc) return showToast('Please select a PDF file first', 'error');

            extractBtn.disabled = true;
            extractBtn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Rendering Pages...';
            pagesGrid.innerHTML = '';

            const mime = formatSelect.value;
            const ext = (mime === 'image/jpeg') ? 'jpg' : 'png';

            try {
                for (let pageNum = 1; pageNum <= totalPages; pageNum++) {
                    const page = await pdfDoc.getPage(pageNum);
                    const viewport = page.getViewport({ scale: 1.5 });

                    const canvas = document.createElement('canvas');
                    const ctx = canvas.getContext('2d');
                    canvas.height = viewport.height;
                    canvas.width = viewport.width;

                    if (mime === 'image/jpeg') {
                        ctx.fillStyle = '#FFFFFF';
                        ctx.fillRect(0, 0, canvas.width, canvas.height);
                    }

                    const renderContext = {
                        canvasContext: ctx,
                        viewport: viewport
                    };
                    await page.render(renderContext).promise;

                    const dataUrl = canvas.toDataURL(mime, 0.92);

                    // Create page card
                    const card = document.createElement('div');
                    card.style.cssText = 'background: var(--bg-surface); border: 1px solid var(--border-subtle); border-radius: var(--radius-md); padding: 0.75rem; text-align: center; box-shadow: var(--shadow-sm);';
                    
                    const img = document.createElement('img');
                    img.style.cssText = 'width: 100%; height: auto; max-height: 220px; object-fit: contain; border-radius: var(--radius-sm); border: 1px solid var(--border-subtle); margin-bottom: 0.5rem;';
                    img.src = dataUrl;

                    const title = document.createElement('div');
                    title.style.cssText = 'font-size: 0.82rem; font-weight: 700; color: var(--text-primary); margin-bottom: 0.4rem;';
                    title.innerText = `Page ${pageNum}`;

                    const dlBtn = document.createElement('button');
                    dlBtn.type = 'button';
                    dlBtn.className = 'btn-primary-gradient';
                    dlBtn.style.cssText = 'font-size: 0.78rem; padding: 0.4rem 0.8rem; width: 100%; justify-content: center;';
                    dlBtn.innerHTML = '<i class="fa-solid fa-download"></i> Download Image';
                    dlBtn.onclick = () => {
                        const link = document.createElement('a');
                        link.href = dataUrl;
                        link.download = `${baseDocName}_page_${pageNum}.${ext}`;
                        document.body.appendChild(link);
                        link.click();
                        document.body.removeChild(link);
                        showToast(`Downloaded Page ${pageNum}!`, 'success');
                    };

                    card.appendChild(img);
                    card.appendChild(title);
                    card.appendChild(dlBtn);
                    pagesGrid.appendChild(card);
                }

                showToast(`Successfully extracted all ${totalPages} pages!`, 'success');
            } catch (err) {
                console.error(err);
                showToast('Failed to render pages: ' + err.message, 'error');
            } finally {
                extractBtn.disabled = false;
                extractBtn.innerHTML = '<i class="fa-solid fa-images"></i> Render All Pages';
            }
        });
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