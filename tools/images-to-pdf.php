<?php
/**
 * ToolsWallet - Images to PDF
 * Domain: toolswallet.online
 * Standalone Particular Page (NO SLUG)
 */

require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/ads.php';
require_once __DIR__ . '/../includes/seo.php';

$slug = 'images-to-pdf';
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
         Tool Interactive Workspace Card (Real Images to PDF Converter)
         ============================================================== -->
    <div class="tool-workspace-card">
        <!-- Dropzone -->
        <div class="file-dropzone" id="imgPdfDropzone" onclick="document.getElementById('imgPdfFileInput').click()" style="cursor: pointer;">
            <i class="fa-solid fa-file-pdf dropzone-icon" style="color: #ef4444;"></i>
            <div class="dropzone-title">Upload Images to Convert to PDF</div>
            <div class="dropzone-subtitle">Select or drag & drop multiple JPG, PNG, or WebP images to merge into a single PDF document.</div>
            <input type="file" id="imgPdfFileInput" class="file-input-hidden" accept="image/*" multiple>
        </div>

        <!-- Options & Selected Images Gallery -->
        <div id="imgPdfWorkspace" style="display: none; margin-top: 1.5rem;">
            <!-- Settings Bar -->
            <div style="background: var(--bg-surface); border: 1px solid var(--border-subtle); border-radius: var(--radius-lg); padding: 1.25rem; margin-bottom: 1.5rem; display: flex; gap: 1.5rem; flex-wrap: wrap; align-items: center;">
                <div style="flex: 1; min-width: 200px;">
                    <label class="form-label" style="font-weight: 700; font-size: 0.85rem; margin-bottom: 0.35rem;">Page Orientation:</label>
                    <select id="pdfOrientation" class="form-control" style="font-weight: 600;">
                        <option value="auto" selected>Auto Fit (Match Image Dimensions)</option>
                        <option value="portrait">Standard Portrait (A4)</option>
                        <option value="landscape">Standard Landscape (A4)</option>
                    </select>
                </div>
                <div style="flex: 1; min-width: 200px;">
                    <label class="form-label" style="font-weight: 700; font-size: 0.85rem; margin-bottom: 0.35rem;">Page Margins:</label>
                    <select id="pdfMargin" class="form-control" style="font-weight: 600;">
                        <option value="0" selected>No Margin (Full Bleed)</option>
                        <option value="20">Small Margins (20pt)</option>
                        <option value="40">Normal Margins (40pt)</option>
                    </select>
                </div>
                <div style="display: flex; align-items: flex-end; gap: 0.75rem;">
                    <button type="button" id="btnConvertToPdf" class="btn-primary-gradient" style="white-space: nowrap;">
                        <i class="fa-solid fa-file-circle-check"></i> Generate & Download PDF
                    </button>
                    <button type="button" id="btnClearImgPdf" class="btn-secondary" style="padding: 0.75rem 1rem;" title="Clear All">
                        <i class="fa-solid fa-trash-can"></i>
                    </button>
                </div>
            </div>

            <!-- Uploaded Image Grid -->
            <div>
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.75rem;">
                    <h4 style="font-size: 0.95rem; font-weight: 700; color: var(--text-primary);">
                        <i class="fa-solid fa-images" style="color: var(--primary);"></i> Selected Images (<span id="imgCountBadge">0</span>)
                    </h4>
                    <span style="font-size: 0.8rem; color: var(--text-muted);">Each image will become a page in your PDF</span>
                </div>
                <div id="imgThumbnailGrid" style="display: grid; grid-template-columns: repeat(auto-fill, minmax(130px, 1fr)); gap: 1rem;"></div>
            </div>
        </div>
    </div>

    <script>
    document.addEventListener('DOMContentLoaded', () => {
        const fileInput = document.getElementById('imgPdfFileInput');
        const workspace = document.getElementById('imgPdfWorkspace');
        const thumbGrid = document.getElementById('imgThumbnailGrid');
        const countBadge = document.getElementById('imgCountBadge');
        const convertBtn = document.getElementById('btnConvertToPdf');
        const clearBtn = document.getElementById('btnClearImgPdf');
        const orientationSelect = document.getElementById('pdfOrientation');
        const marginSelect = document.getElementById('pdfMargin');

        let selectedFiles = [];

        fileInput.addEventListener('change', (e) => {
            if (e.target.files && e.target.files.length) {
                addFiles(Array.from(e.target.files));
            }
        });

        // Drag & drop
        const dropzone = document.getElementById('imgPdfDropzone');
        dropzone.addEventListener('dragover', (e) => { e.preventDefault(); dropzone.style.borderColor = 'var(--primary)'; });
        dropzone.addEventListener('dragleave', () => { dropzone.style.borderColor = 'var(--border-subtle)'; });
        dropzone.addEventListener('drop', (e) => {
            e.preventDefault();
            dropzone.style.borderColor = 'var(--border-subtle)';
            if (e.dataTransfer.files && e.dataTransfer.files.length) {
                addFiles(Array.from(e.dataTransfer.files).filter(f => f.type.startsWith('image/')));
            }
        });

        function addFiles(files) {
            files.forEach(f => {
                if (!selectedFiles.some(existing => existing.name === f.name && existing.size === f.size)) {
                    selectedFiles.push(f);
                }
            });
            renderThumbnails();
        }

        function renderThumbnails() {
            if (selectedFiles.length === 0) {
                workspace.style.display = 'none';
                return;
            }
            workspace.style.display = 'block';
            countBadge.innerText = selectedFiles.length;
            thumbGrid.innerHTML = '';

            selectedFiles.forEach((file, idx) => {
                const card = document.createElement('div');
                card.style.cssText = 'position: relative; background: var(--bg-surface); border: 1px solid var(--border-subtle); border-radius: var(--radius-md); padding: 0.5rem; text-align: center; box-shadow: var(--shadow-sm);';
                
                const img = document.createElement('img');
                img.style.cssText = 'width: 100%; height: 100px; object-fit: cover; border-radius: var(--radius-sm); margin-bottom: 0.35rem;';
                img.src = URL.createObjectURL(file);

                const label = document.createElement('div');
                label.style.cssText = 'font-size: 0.72rem; font-weight: 600; color: var(--text-secondary); overflow: hidden; text-overflow: ellipsis; white-space: nowrap;';
                label.innerText = `P.${idx + 1}: ${file.name}`;

                const removeBtn = document.createElement('button');
                removeBtn.type = 'button';
                removeBtn.style.cssText = 'position: absolute; top: -6px; right: -6px; width: 22px; height: 22px; border-radius: 50%; background: #ef4444; color: #fff; border: none; cursor: pointer; font-size: 0.65rem; display: flex; align-items: center; justify-content: center;';
                removeBtn.innerHTML = '<i class="fa-solid fa-xmark"></i>';
                removeBtn.onclick = () => {
                    selectedFiles.splice(idx, 1);
                    renderThumbnails();
                };

                card.appendChild(removeBtn);
                card.appendChild(img);
                card.appendChild(label);
                thumbGrid.appendChild(card);
            });
        }

        clearBtn.addEventListener('click', () => {
            selectedFiles = [];
            fileInput.value = '';
            renderThumbnails();
            showToast('Cleared all images', 'info');
        });

        convertBtn.addEventListener('click', async () => {
            if (!selectedFiles.length) return showToast('Please upload at least one image', 'error');

            if (!window.PDFLib) {
                return showToast('PDF processing library is loading, please wait a moment...', 'info');
            }

            convertBtn.disabled = true;
            convertBtn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Building PDF...';

            try {
                const { PDFDocument } = PDFLib;
                const pdfDoc = await PDFDocument.create();
                const margin = parseInt(marginSelect.value, 10);
                const orientation = orientationSelect.value;

                for (let i = 0; i < selectedFiles.length; i++) {
                    const file = selectedFiles[i];
                    
                    // Convert image to ArrayBuffer
                    const arrayBuffer = await file.arrayBuffer();
                    let embeddedImg = null;

                    if (file.type === 'image/jpeg' || file.name.endsWith('.jpg') || file.name.endsWith('.jpeg')) {
                        embeddedImg = await pdfDoc.embedJpg(arrayBuffer);
                    } else if (file.type === 'image/png' || file.name.endsWith('.png')) {
                        try {
                            embeddedImg = await pdfDoc.embedPng(arrayBuffer);
                        } catch (pngErr) {
                            // If indexed PNG, convert to JPEG bytes via canvas
                            embeddedImg = await convertImageToJpgEmbed(file, pdfDoc);
                        }
                    } else {
                        // WebP / GIF / BMP fallback via canvas
                        embeddedImg = await convertImageToJpgEmbed(file, pdfDoc);
                    }

                    if (!embeddedImg) continue;

                    let pageWidth, pageHeight;
                    if (orientation === 'portrait') {
                        pageWidth = 595.28; // A4 portrait pt
                        pageHeight = 841.89;
                    } else if (orientation === 'landscape') {
                        pageWidth = 841.89; // A4 landscape pt
                        pageHeight = 595.28;
                    } else {
                        // Auto: page matches image size exactly + margins
                        pageWidth = embeddedImg.width + (margin * 2);
                        pageHeight = embeddedImg.height + (margin * 2);
                    }

                    const page = pdfDoc.addPage([pageWidth, pageHeight]);

                    // Scale image to fit within available area with margins
                    const availWidth = pageWidth - (margin * 2);
                    const availHeight = pageHeight - (margin * 2);
                    const scale = Math.min(availWidth / embeddedImg.width, availHeight / embeddedImg.height, 1);
                    const drawWidth = embeddedImg.width * scale;
                    const drawHeight = embeddedImg.height * scale;

                    // Center on page
                    const x = margin + ((availWidth - drawWidth) / 2);
                    const y = margin + ((availHeight - drawHeight) / 2);

                    page.drawImage(embeddedImg, {
                        x: x,
                        y: y,
                        width: drawWidth,
                        height: drawHeight
                    });
                }

                const pdfBytes = await pdfDoc.save();
                const blob = new Blob([pdfBytes], { type: 'application/pdf' });
                const blobUrl = URL.createObjectURL(blob);

                const link = document.createElement('a');
                link.href = blobUrl;
                link.download = `images_converted_${Date.now()}.pdf`;
                document.body.appendChild(link);
                link.click();
                document.body.removeChild(link);

                showToast('PDF Document generated & downloaded!', 'success');
            } catch (err) {
                console.error(err);
                showToast('Failed to generate PDF: ' + err.message, 'error');
            } finally {
                convertBtn.disabled = false;
                convertBtn.innerHTML = '<i class="fa-solid fa-file-circle-check"></i> Generate & Download PDF';
            }
        });

        // Helper: Convert any image format to JPG embed
        async function convertImageToJpgEmbed(file, pdfDoc) {
            return new Promise((resolve, reject) => {
                const img = new Image();
                img.onload = async () => {
                    const canvas = document.createElement('canvas');
                    canvas.width = img.width;
                    canvas.height = img.height;
                    const ctx = canvas.getContext('2d');
                    ctx.fillStyle = '#FFFFFF';
                    ctx.fillRect(0, 0, canvas.width, canvas.height);
                    ctx.drawImage(img, 0, 0);
                    canvas.toBlob(async (blob) => {
                        const buffer = await blob.arrayBuffer();
                        const embedded = await pdfDoc.embedJpg(buffer);
                        resolve(embedded);
                    }, 'image/jpeg', 0.92);
                };
                img.onerror = reject;
                img.src = URL.createObjectURL(file);
            });
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