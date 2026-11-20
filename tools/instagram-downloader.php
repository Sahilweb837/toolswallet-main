<?php
/**
 * ToolsWallet - Instagram Video & Reels Downloader
 * Domain: toolswallet.online
 * Standalone Particular Page (NO SLUG)
 */

require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/ads.php';
require_once __DIR__ . '/../includes/seo.php';

$slug = 'instagram-downloader';
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
<div class="instagram-downloader-workspace" style="max-width: 820px; margin: 0 auto;">
    <!-- Top banner badge -->
    <div style="text-align: center; margin-bottom: 1.5rem;">
        <span class="badge" style="background: linear-gradient(135deg, #833ab4, #fd1d1d, #fcb045); color: #fff; padding: 6px 16px; font-weight: 700; border-radius: var(--radius-full); font-size: 0.85rem; display: inline-flex; align-items: center; gap: 0.5rem; box-shadow: 0 4px 12px rgba(253, 29, 29, 0.25);">
            <i class="fa-brands fa-instagram"></i> Ultra HD 1080p Instagram Downloader
        </span>
        <h2 style="font-size: clamp(1.4rem, 2.5vw, 1.85rem); font-weight: 800; margin-top: 0.75rem; color: var(--text-primary);">
            Download Instagram Reels, Videos & Photos
        </h2>
        <p style="color: var(--text-secondary); font-size: 0.95rem; max-width: 580px; margin: 0.5rem auto 0;">
            Paste any Instagram URL to download Reels, Feed Videos, Carousel Photos, and Audio in original HD quality without watermarks.
        </p>
    </div>

    <!-- Supported Pills -->
    <div style="display: flex; justify-content: center; gap: 0.5rem; margin-bottom: 1.5rem; flex-wrap: wrap;">
        <span style="font-size: 0.8rem; font-weight: 700; padding: 4px 10px; border-radius: var(--radius-md); background: var(--bg-body); border: 1px solid var(--border-subtle); color: var(--text-secondary);"><i class="fa-solid fa-film" style="color: #ec4899;"></i> Reels</span>
        <span style="font-size: 0.8rem; font-weight: 700; padding: 4px 10px; border-radius: var(--radius-md); background: var(--bg-body); border: 1px solid var(--border-subtle); color: var(--text-secondary);"><i class="fa-solid fa-play" style="color: #ef4444;"></i> Videos</span>
        <span style="font-size: 0.8rem; font-weight: 700; padding: 4px 10px; border-radius: var(--radius-md); background: var(--bg-body); border: 1px solid var(--border-subtle); color: var(--text-secondary);"><i class="fa-solid fa-image" style="color: #10b981;"></i> Photos</span>
        <span style="font-size: 0.8rem; font-weight: 700; padding: 4px 10px; border-radius: var(--radius-md); background: var(--bg-body); border: 1px solid var(--border-subtle); color: var(--text-secondary);"><i class="fa-solid fa-images" style="color: #8b5cf6;"></i> Carousels</span>
        <span style="font-size: 0.8rem; font-weight: 700; padding: 4px 10px; border-radius: var(--radius-md); background: var(--bg-body); border: 1px solid var(--border-subtle); color: var(--text-secondary);"><i class="fa-solid fa-music" style="color: #f59e0b;"></i> Audio Track</span>
    </div>

    <!-- URL Input Box with Action Buttons -->
    <div class="form-group" style="margin-bottom: 1rem;">
        <div style="position: relative; display: flex; gap: 0.5rem; flex-wrap: wrap;">
            <div style="position: relative; flex: 1; min-width: 280px;">
                <i class="fa-brands fa-instagram" style="position: absolute; left: 1rem; top: 50%; transform: translateY(-50%); color: #ec4899; font-size: 1.25rem;"></i>
                <input type="url" id="igUrlInput" class="form-control" style="padding-left: 2.8rem; padding-right: 5rem; height: 54px; font-size: 1rem; border-radius: var(--radius-lg);" placeholder="Paste Instagram link here (e.g. https://www.instagram.com/reel/...)" autofocus>
                <button type="button" id="btnPasteIg" class="btn-secondary btn-sm" style="position: absolute; right: 0.5rem; top: 50%; transform: translateY(-50%); font-weight: 700; font-size: 0.75rem; padding: 0.35rem 0.65rem;">
                    <i class="fa-regular fa-clipboard"></i> Paste
                </button>
            </div>
            <button type="button" id="btnFetchIg" class="btn-primary-gradient" style="height: 54px; padding: 0 1.75rem; font-size: 1rem; font-weight: 700; border-radius: var(--radius-lg); white-space: nowrap; display: inline-flex; align-items: center; gap: 0.5rem; box-shadow: 0 4px 15px rgba(234, 88, 12, 0.35);">
                <i class="fa-solid fa-cloud-arrow-down"></i> Get Download Links
            </button>
        </div>
        <div style="display: flex; justify-content: space-between; align-items: center; margin-top: 0.5rem; font-size: 0.8rem; color: var(--text-muted);">
            <span>Supports Reels, Stories, Photos & Posts</span>
            <button type="button" id="btnClearIg" style="background: none; border: none; color: var(--text-muted); cursor: pointer; text-decoration: underline;">Clear</button>
        </div>
    </div>

    <!-- Results Container (Populated via JS) -->
    <div id="igResultContainer" style="display: none;"></div>

    <!-- Quick 3-Step Visual Process -->
    <div style="margin-top: 2rem; padding-top: 1.5rem; border-top: 1px solid var(--border-subtle);">
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 1rem; text-align: center;">
            <div style="background: var(--bg-body); padding: 1rem; border-radius: var(--radius-md); border: 1px solid var(--border-subtle);">
                <div style="width: 36px; height: 36px; border-radius: 50%; background: rgba(255, 107, 0, 0.1); color: var(--primary); display: inline-flex; align-items: center; justify-content: center; font-weight: 800; margin-bottom: 0.5rem;">
                    <i class="fa-solid fa-link"></i>
                </div>
                <div style="font-weight: 700; font-size: 0.9rem; color: var(--text-primary);">1. Copy Link</div>
                <div style="font-size: 0.8rem; color: var(--text-muted); margin-top: 0.2rem;">Copy link of any Reel, video or photo from Instagram.</div>
            </div>
            <div style="background: var(--bg-body); padding: 1rem; border-radius: var(--radius-md); border: 1px solid var(--border-subtle);">
                <div style="width: 36px; height: 36px; border-radius: 50%; background: rgba(255, 107, 0, 0.1); color: var(--primary); display: inline-flex; align-items: center; justify-content: center; font-weight: 800; margin-bottom: 0.5rem;">
                    <i class="fa-solid fa-magnifying-glass"></i>
                </div>
                <div style="font-weight: 700; font-size: 0.9rem; color: var(--text-primary);">2. Paste & Fetch</div>
                <div style="font-size: 0.8rem; color: var(--text-muted); margin-top: 0.2rem;">Paste into the search field and click Get Links.</div>
            </div>
            <div style="background: var(--bg-body); padding: 1rem; border-radius: var(--radius-md); border: 1px solid var(--border-subtle);">
                <div style="width: 36px; height: 36px; border-radius: 50%; background: rgba(255, 107, 0, 0.1); color: var(--primary); display: inline-flex; align-items: center; justify-content: center; font-weight: 800; margin-bottom: 0.5rem;">
                    <i class="fa-solid fa-download"></i>
                </div>
                <div style="font-weight: 700; font-size: 0.9rem; color: var(--text-primary);">3. Save HD File</div>
                <div style="font-size: 0.8rem; color: var(--text-muted); margin-top: 0.2rem;">Choose 1080p MP4 or MP3 and save directly to device.</div>
            </div>
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