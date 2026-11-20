<?php
/**
 * ToolsWallet - Merge PDF
 * Domain: toolswallet.online
 * Standalone Particular Page (NO SLUG)
 */

require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/ads.php';
require_once __DIR__ . '/../includes/seo.php';

$slug = 'merge-pdf';
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
         Tool Interactive Workspace Card (Real PDF Merger)
         ============================================================== -->
    <div class="tool-workspace-card">
        <!-- Dropzone -->
        <div class="file-dropzone" id="mergePdfDropzone" onclick="document.getElementById('mergePdfFileInput').click()" style="cursor: pointer;">
            <i class="fa-solid fa-file-circle-plus dropzone-icon" style="color: #ef4444;"></i>
            <div class="dropzone-title">Upload Multiple PDFs to Merge</div>
            <div class="dropzone-subtitle">Combine 2 or more PDF files into a single organized document in your desired order.</div>
            <input type="file" id="mergePdfFileInput" class="file-input-hidden" accept=".pdf" multiple>
        </div>

        <!-- Selected Files List & Merge Actions -->
        <div id="mergePdfWorkspace" style="display: none; margin-top: 1.5rem;">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem;">
                <h4 style="font-size: 1rem; font-weight: 700; color: var(--text-primary);">
                    <i class="fa-solid fa-list-ol" style="color: var(--primary);"></i> Merge Queue (<span id="mergePdfCountBadge">0</span> Files)
                </h4>
                <div style="display: flex; gap: 0.6rem;">
                    <button type="button" id="btnRunMerge" class="btn-primary-gradient">
                        <i class="fa-solid fa-layer-group"></i> Merge & Download PDF
                    </button>
                    <button type="button" id="btnClearMerge" class="btn-secondary" style="padding: 0.75rem 1rem;">
                        <i class="fa-solid fa-trash-can"></i>
                    </button>
                </div>
            </div>

            <!-- Queue Items List -->
            <div id="mergeQueueList" style="display: flex; flex-direction: column; gap: 0.65rem;"></div>
        </div>
    </div>

    <script>
    document.addEventListener('DOMContentLoaded', () => {
        const fileInput = document.getElementById('mergePdfFileInput');
        const workspace = document.getElementById('mergePdfWorkspace');
        const queueList = document.getElementById('mergeQueueList');
        const countBadge = document.getElementById('mergePdfCountBadge');
        const mergeBtn = document.getElementById('btnRunMerge');
        const clearBtn = document.getElementById('btnClearMerge');

        let pdfFiles = [];

        fileInput.addEventListener('change', (e) => {
            if (e.target.files && e.target.files.length) {
                addFiles(Array.from(e.target.files));
            }
        });

        // Drag & drop
        const dropzone = document.getElementById('mergePdfDropzone');
        dropzone.addEventListener('dragover', (e) => { e.preventDefault(); dropzone.style.borderColor = 'var(--primary)'; });
        dropzone.addEventListener('dragleave', () => { dropzone.style.borderColor = 'var(--border-subtle)'; });
        dropzone.addEventListener('drop', (e) => {
            e.preventDefault();
            dropzone.style.borderColor = 'var(--border-subtle)';
            if (e.dataTransfer.files && e.dataTransfer.files.length) {
                addFiles(Array.from(e.dataTransfer.files).filter(f => f.name.toLowerCase().endsWith('.pdf')));
            }
        });

        function addFiles(files) {
            files.forEach(f => {
                pdfFiles.push(f);
            });
            renderQueue();
            showToast(`Added ${files.length} PDF(s) to queue`, 'info');
        }

        function renderQueue() {
            if (pdfFiles.length === 0) {
                workspace.style.display = 'none';
                return;
            }
            workspace.style.display = 'block';
            countBadge.innerText = pdfFiles.length;
            queueList.innerHTML = '';

            pdfFiles.forEach((file, idx) => {
                const item = document.createElement('div');
                item.style.cssText = 'display: flex; align-items: center; justify-content: space-between; background: var(--bg-surface); border: 1px solid var(--border-subtle); border-radius: var(--radius-md); padding: 0.85rem 1.15rem; box-shadow: var(--shadow-sm);';

                const left = document.createElement('div');
                left.style.cssText = 'display: flex; align-items: center; gap: 0.85rem;';
                left.innerHTML = `
                    <span style="width: 28px; height: 28px; border-radius: 50%; background: var(--primary-light); color: var(--primary-dark); font-weight: 800; font-size: 0.8rem; display: flex; align-items: center; justify-content: center;">${idx + 1}</span>
                    <div>
                        <div style="font-weight: 700; font-size: 0.92rem; color: var(--text-primary);">${escapeHtml(file.name)}</div>
                        <div style="font-size: 0.75rem; color: var(--text-muted);">${formatBytes(file.size)}</div>
                    </div>
                `;

                const right = document.createElement('div');
                right.style.cssText = 'display: flex; align-items: center; gap: 0.4rem;';

                if (idx > 0) {
                    const upBtn = document.createElement('button');
                    upBtn.type = 'button';
                    upBtn.style.cssText = 'background: var(--bg-body); border: 1px solid var(--border-subtle); border-radius: var(--radius-sm); padding: 0.35rem 0.6rem; cursor: pointer; color: var(--text-secondary);';
                    upBtn.innerHTML = '<i class="fa-solid fa-arrow-up"></i>';
                    upBtn.onclick = () => {
                        const temp = pdfFiles[idx];
                        pdfFiles[idx] = pdfFiles[idx - 1];
                        pdfFiles[idx - 1] = temp;
                        renderQueue();
                    };
                    right.appendChild(upBtn);
                }

                if (idx < pdfFiles.length - 1) {
                    const downBtn = document.createElement('button');
                    downBtn.type = 'button';
                    downBtn.style.cssText = 'background: var(--bg-body); border: 1px solid var(--border-subtle); border-radius: var(--radius-sm); padding: 0.35rem 0.6rem; cursor: pointer; color: var(--text-secondary);';
                    downBtn.innerHTML = '<i class="fa-solid fa-arrow-down"></i>';
                    downBtn.onclick = () => {
                        const temp = pdfFiles[idx];
                        pdfFiles[idx] = pdfFiles[idx + 1];
                        pdfFiles[idx + 1] = temp;
                        renderQueue();
                    };
                    right.appendChild(downBtn);
                }

                const delBtn = document.createElement('button');
                delBtn.type = 'button';
                delBtn.style.cssText = 'background: #fee2e2; border: 1px solid #fecaca; border-radius: var(--radius-sm); padding: 0.35rem 0.6rem; cursor: pointer; color: #dc2626;';
                delBtn.innerHTML = '<i class="fa-solid fa-trash"></i>';
                delBtn.onclick = () => {
                    pdfFiles.splice(idx, 1);
                    renderQueue();
                };
                right.appendChild(delBtn);

                item.appendChild(left);
                item.appendChild(right);
                queueList.appendChild(item);
            });
        }

        clearBtn.addEventListener('click', () => {
            pdfFiles = [];
            fileInput.value = '';
            renderQueue();
            showToast('Cleared merge queue', 'info');
        });

        mergeBtn.addEventListener('click', async () => {
            if (pdfFiles.length < 2) {
                return showToast('Please select at least 2 PDF files to merge', 'error');
            }

            if (!window.PDFLib) {
                return showToast('PDF engine is loading, please wait...', 'info');
            }

            mergeBtn.disabled = true;
            mergeBtn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Merging Documents...';

            try {
                const { PDFDocument } = PDFLib;
                const mergedPdf = await PDFDocument.create();

                for (let i = 0; i < pdfFiles.length; i++) {
                    const file = pdfFiles[i];
                    const arrayBuffer = await file.arrayBuffer();
                    const pdf = await PDFDocument.load(arrayBuffer);
                    const copiedPages = await mergedPdf.copyPages(pdf, pdf.getPageIndices());
                    copiedPages.forEach((page) => mergedPdf.addPage(page));
                }

                const mergedBytes = await mergedPdf.save();
                const blob = new Blob([mergedBytes], { type: 'application/pdf' });
                const blobUrl = URL.createObjectURL(blob);

                const link = document.createElement('a');
                link.href = blobUrl;
                link.download = `merged_document_${Date.now()}.pdf`;
                document.body.appendChild(link);
                link.click();
                document.body.removeChild(link);

                showToast(`Successfully merged ${pdfFiles.length} PDFs!`, 'success');
            } catch (err) {
                console.error(err);
                showToast('Failed to merge PDFs: ' + err.message, 'error');
            } finally {
                mergeBtn.disabled = false;
                mergeBtn.innerHTML = '<i class="fa-solid fa-layer-group"></i> Merge & Download PDF';
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