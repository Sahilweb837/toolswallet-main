<?php
/**
 * ToolsWallet - Color Picker & Converter
 * Domain: toolswallet.online
 * Standalone Particular Page (NO SLUG)
 */

require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/ads.php';
require_once __DIR__ . '/../includes/seo.php';

$slug = 'color-picker';
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
         Tool Interactive Workspace Card
         ============================================================== -->
    <div class="tool-workspace-card">
        <div class="color-picker-stage">
            <div>
                <label class="form-label"><i class="fa-solid fa-palette"></i> Live Color Preview:</label>
                <div id="cpPreviewBox" class="color-preview-box" style="background-color: #ff5400;">
                    <span id="cpPreviewBadge" class="color-preview-badge">#FF5400</span>
                    <input type="color" id="cpColorInput" value="#ff5400" style="opacity: 0; position: absolute; width: 100%; height: 100%; top: 0; left: 0; cursor: pointer;" title="Click to open color palette picker">
                </div>
                <div style="margin-top: 0.75rem; text-align: center;">
                    <button type="button" class="btn-secondary btn-sm" onclick="document.getElementById('cpColorInput').click()" style="width: 100%;">
                        <i class="fa-solid fa-eye-dropper"></i> Open Color Picker
                    </button>
                </div>

                <div style="margin-top: 1.25rem;">
                    <label class="form-label" style="font-size: 0.8rem; text-transform: uppercase; color: var(--text-muted);">Quick Presets</label>
                    <div class="color-presets-row" id="cpPresetsRow">
                        <span class="color-preset-dot" style="background: #ff5400;" data-color="#ff5400"></span>
                        <span class="color-preset-dot" style="background: #3b82f6;" data-color="#3b82f6"></span>
                        <span class="color-preset-dot" style="background: #10b981;" data-color="#10b981"></span>
                        <span class="color-preset-dot" style="background: #8b5cf6;" data-color="#8b5cf6"></span>
                        <span class="color-preset-dot" style="background: #ec4899;" data-color="#ec4899"></span>
                        <span class="color-preset-dot" style="background: #f59e0b;" data-color="#f59e0b"></span>
                        <span class="color-preset-dot" style="background: #14b8a6;" data-color="#14b8a6"></span>
                        <span class="color-preset-dot" style="background: #0f172a;" data-color="#0f172a"></span>
                    </div>
                </div>
            </div>

            <div>
                <h3 style="font-size: 1.1rem; font-weight: 800; margin-bottom: 1rem; color: var(--text-primary);">
                    Color Formats & Conversions
                </h3>

                <div class="color-channels-grid">
                    <div class="color-channel-item">
                        <label>HEX</label>
                        <div class="color-channel-input-wrap">
                            <input type="text" id="cpHex" class="form-control font-mono" value="#FF5400">
                            <button type="button" class="color-copy-btn" onclick="copyToClipboard(document.getElementById('cpHex').value, 'HEX copied!')"><i class="fa-regular fa-copy"></i></button>
                        </div>
                    </div>
                    <div class="color-channel-item">
                        <label>RGB</label>
                        <div class="color-channel-input-wrap">
                            <input type="text" id="cpRgb" class="form-control font-mono" value="rgb(255, 84, 0)">
                            <button type="button" class="color-copy-btn" onclick="copyToClipboard(document.getElementById('cpRgb').value, 'RGB copied!')"><i class="fa-regular fa-copy"></i></button>
                        </div>
                    </div>
                    <div class="color-channel-item">
                        <label>HSL</label>
                        <div class="color-channel-input-wrap">
                            <input type="text" id="cpHsl" class="form-control font-mono" value="hsl(20, 100%, 50%)">
                            <button type="button" class="color-copy-btn" onclick="copyToClipboard(document.getElementById('cpHsl').value, 'HSL copied!')"><i class="fa-regular fa-copy"></i></button>
                        </div>
                    </div>
                    <div class="color-channel-item">
                        <label>CMYK</label>
                        <div class="color-channel-input-wrap">
                            <input type="text" id="cpCmyk" class="form-control font-mono" value="cmyk(0%, 67%, 100%, 0%)">
                            <button type="button" class="color-copy-btn" onclick="copyToClipboard(document.getElementById('cpCmyk').value, 'CMYK copied!')"><i class="fa-regular fa-copy"></i></button>
                        </div>
                    </div>
                </div>

                <!-- Sliders for Red, Green, Blue -->
                <div style="background: var(--bg-body); border: 1px solid var(--border-subtle); border-radius: var(--radius-md); padding: 1.25rem; margin-top: 1rem;">
                    <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 0.5rem;">
                        <span style="font-size: 0.85rem; font-weight: 700; color: #ef4444;"><i class="fa-solid fa-circle" style="font-size: 0.6rem;"></i> Red (R): <strong id="valR">255</strong></span>
                        <input type="range" id="sliderR" min="0" max="255" value="255" style="width: 65%;">
                    </div>
                    <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 0.5rem;">
                        <span style="font-size: 0.85rem; font-weight: 700; color: #10b981;"><i class="fa-solid fa-circle" style="font-size: 0.6rem;"></i> Green (G): <strong id="valG">84</strong></span>
                        <input type="range" id="sliderG" min="0" max="255" value="84" style="width: 65%;">
                    </div>
                    <div style="display: flex; align-items: center; justify-content: space-between;">
                        <span style="font-size: 0.85rem; font-weight: 700; color: #3b82f6;"><i class="fa-solid fa-circle" style="font-size: 0.6rem;"></i> Blue (B): <strong id="valB">0</strong></span>
                        <input type="range" id="sliderB" min="0" max="255" value="0" style="width: 65%;">
                    </div>
                </div>

                <div style="display: flex; gap: 0.75rem; margin-top: 1.25rem; flex-wrap: wrap;">
                    <button type="button" class="btn-primary-gradient" id="btnRandomColor">
                        <i class="fa-solid fa-shuffle"></i> Random Color
                    </button>
                    <button type="button" class="btn-secondary" onclick="copyToClipboard(document.getElementById('cpHex').value, 'HEX copied!')">
                        <i class="fa-regular fa-copy"></i> Copy HEX
                    </button>
                    <button type="button" class="btn-secondary" onclick="copyToClipboard(`background-color: ${document.getElementById('cpHex').value};`, 'CSS copied!')">
                        <i class="fa-brands fa-css3-alt"></i> Copy CSS Rule
                    </button>
                </div>
            </div>
        </div>
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