<?php
/**
 * ToolsWallet Category Page
 * Domain: toolswallet.online
 */

require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/ads.php';
require_once __DIR__ . '/includes/seo.php';

$slug = trim($_GET['slug'] ?? 'pdf-tools');
$category = Database::getCategory($slug);

if (!$category) {
    header("HTTP/1.0 404 Not Found");
    $pageSeo = ['title' => 'Category Not Found | ToolsWallet'];
    require_once __DIR__ . '/includes/header.php';
    ?>
    <div class="container" style="padding: 5rem 1rem; text-align: center;">
        <i class="fa-solid fa-triangle-exclamation" style="font-size: 3rem; color: #ef4444; margin-bottom: 1rem;"></i>
        <h2>Category Not Found</h2>
        <p style="color: var(--text-secondary); margin-top: 0.5rem;">The category you requested does not exist or has been moved.</p>
        <a href="<?= url() ?>" class="btn-primary-gradient" style="margin-top: 1.5rem; display: inline-flex;">Return Home</a>
    </div>
    <?php
    require_once __DIR__ . '/includes/footer.php';
    exit;
}

$tools = Database::getTools($slug);
$faqs = Database::getFaqs(null, $slug);
$allCategories = Database::getCategories();

$pageSeo = [
    'title' => $category['meta_title'] ?? ($category['name'] . ' - Free Online Tools | ToolsWallet'),
    'description' => $category['meta_description'] ?? $category['description'],
    'canonical' => url('category.php?slug=' . $category['slug']),
    'breadcrumbs' => [
        ['name' => 'Home', 'url' => url()],
        ['name' => 'Categories', 'url' => url() . '#explore-tools'],
        ['name' => $category['name'], 'url' => url('category.php?slug=' . $category['slug'])]
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
        <a href="<?= url() ?>#explore-tools">Categories</a>
        <span class="separator">/</span>
        <span class="current"><?= e($category['name']) ?></span>
    </nav>

    <!-- Category Banner Header -->
    <div style="background: var(--bg-surface); border: 1px solid var(--border-subtle); border-radius: var(--radius-xl); padding: 2.5rem; margin-bottom: 2.5rem; position: relative; overflow: hidden; box-shadow: var(--shadow-sm);">
        <div style="position: absolute; top: 0; left: 0; bottom: 0; width: 6px; background: <?= e($category['color']) ?>;"></div>
        <div style="display: flex; align-items: center; gap: 1.5rem; flex-wrap: wrap;">
            <div style="width: 72px; height: 72px; border-radius: var(--radius-lg); background: <?= e($category['color']) ?>15; color: <?= e($category['color']) ?>; display: flex; align-items: center; justify-content: center; font-size: 2rem; flex-shrink: 0;">
                <i class="fa-solid <?= e($category['icon']) ?>"></i>
            </div>
            <div style="flex: 1;">
                <div style="display: flex; align-items: center; gap: 0.75rem; margin-bottom: 0.4rem;">
                    <h1 style="font-size: clamp(1.8rem, 3.5vw, 2.5rem); font-weight: 800; letter-spacing: -0.02em; color: var(--text-primary);"><?= e($category['name']) ?></h1>
                    <span style="font-size: 0.85rem; font-weight: 700; padding: 4px 10px; border-radius: var(--radius-full); background: var(--bg-secondary); color: var(--text-secondary);"><?= count($tools) ?> Tools Available</span>
                </div>
                <p style="color: var(--text-secondary); font-size: 1.05rem; max-width: 800px;"><?= e($category['description']) ?></p>
            </div>
        </div>
    </div>

    <!-- Category Tools Grid -->
    <div class="section-header">
        <div class="section-title-wrap">
            <h2><i class="fa-solid fa-wrench" style="color: <?= e($category['color']) ?>;"></i> Available <?= e($category['name']) ?></h2>
            <p class="section-subtitle">Pick any tool below to launch and run directly in your browser.</p>
        </div>
    </div>

    <div class="tools-grid" style="margin-bottom: 3.5rem;">
        <?php foreach ($tools as $tool): ?>
            <a href="<?= tool_url($tool['slug']) ?>" 
               class="tool-card" 
               data-slug="<?= e($tool['slug']) ?>" 
               data-name="<?= e($tool['name']) ?>" 
               data-desc="<?= e($tool['short_description']) ?>" 
               data-category="<?= e($tool['category_slug'] ?? '') ?>"
               data-keywords="<?= e($tool['meta_keywords'] ?? '') ?>"
               data-icon="<?= e(fa_class($tool['icon'])) ?>"
               data-url="<?= tool_url($tool['slug']) ?>">
                
                <div class="tool-card-top">
                    <div class="tool-icon-wrapper" style="background: <?= e($category['color']) ?>15; color: <?= e($category['color']) ?>;">
                        <i class="<?= e(fa_class($tool['icon'])) ?>"></i>
                    </div>
                    <?php if (!empty($tool['badge'])): ?>
                        <span class="tool-badge badge-<?= strtolower($tool['badge']) ?>"><?= e($tool['badge']) ?></span>
                    <?php endif; ?>
                </div>

                <h3 class="tool-title"><?= e($tool['name']) ?></h3>
                <p class="tool-desc"><?= e($tool['short_description']) ?></p>

                <div class="tool-meta">
                    <span class="tool-rating">
                        <i class="fa-solid fa-star"></i> <?= e((string)($tool['rating'] ?? '4.9')) ?>
                    </span>
                    <span class="tool-run-count">
                        <i class="fa-solid fa-circle-play"></i> <?= format_number_k((int)($tool['run_count'] ?? 1000)) ?> uses
                    </span>
                </div>
            </a>
        <?php endforeach; ?>
    </div>

    <!-- In-Content Ad -->
    <?= render_ad_slot('category_bottom', 'responsive_banner') ?>

    <!-- Other Categories Carousel / Navigation -->
    <div style="margin: 4rem 0;">
        <h3 style="font-size: 1.35rem; font-weight: 800; margin-bottom: 1.5rem; color: var(--text-primary);">Explore Other Tool Categories</h3>
        <div style="display: flex; gap: 1rem; flex-wrap: wrap;">
            <?php foreach ($allCategories as $cSlug => $cat): 
                if ($cSlug === $slug) continue;
            ?>
                <a href="<?= url('category.php?slug=' . $cSlug) ?>" class="recent-pill" style="padding: 0.65rem 1.25rem;">
                    <i class="fa-solid <?= e($cat['icon']) ?>" style="color: <?= e($cat['color']) ?>;"></i>
                    <span><?= e($cat['name']) ?></span>
                </a>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
