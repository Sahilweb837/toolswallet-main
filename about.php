<?php
/**
 * ToolsWallet - About Us
 * Domain: toolswallet.online
 */

require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/ads.php';
require_once __DIR__ . '/includes/seo.php';

$pageSeo = [
    'title' => 'About ToolsWallet - All-in-One Free Online Tools Platform',
    'description' => 'Learn about ToolsWallet, our mission to build the world\'s fastest, private, and 100% free developer, SEO, PDF, image, and text utilities platform.',
    'keywords' => 'about toolswallet, free online tools, online pdf editor, live seo checker, image compressor',
    'canonical' => url('about.php'),
    'breadcrumbs' => [
        ['name' => 'Home', 'url' => url()],
        ['name' => 'About Us', 'url' => url('about.php')]
    ]
];

require_once __DIR__ . '/includes/header.php';
?>

<div class="container" style="max-width: 1000px; padding: 2.5rem 1rem 4rem;">
    <!-- Breadcrumbs -->
    <nav class="breadcrumbs" aria-label="Breadcrumb">
        <a href="<?= url() ?>"><i class="fa-solid fa-house"></i> Home</a>
        <span class="separator">/</span>
        <span class="current">About ToolsWallet</span>
    </nav>

    <!-- Hero Header -->
    <div style="background: var(--bg-surface); border: 1px solid var(--border-subtle); border-radius: var(--radius-xl); padding: 3rem 2.5rem; margin-bottom: 3rem; box-shadow: var(--shadow-sm); position: relative; overflow: hidden;">
        <div style="position: absolute; top: -50px; right: -50px; width: 220px; height: 220px; background: radial-gradient(circle, var(--primary-glow) 0%, transparent 70%); border-radius: 50%; pointer-events: none;"></div>
        <div class="hero-pill" style="margin-bottom: 1rem;">
            <span class="hero-pill-dot"></span>
            <span>Our Mission & Technology</span>
        </div>
        <h1 style="font-size: clamp(2rem, 4vw, 2.75rem); font-weight: 800; color: var(--text-primary); margin-bottom: 1rem; line-height: 1.2;">
            Empowering Millions of Creators with <span class="gradient-text">Instant, Private Tools</span>
        </h1>
        <p style="font-size: 1.1rem; color: var(--text-secondary); line-height: 1.7; max-width: 780px;">
            ToolsWallet was founded on a simple principle: everyday digital tools should be completely free, blindingly fast, and respect your privacy. No forced subscriptions, no registration walls, and zero server storage leaks.
        </p>
    </div>

    <!-- Core Pillars Grid -->
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 1.5rem; margin-bottom: 3.5rem;">
        <div class="tw-snapshot-card">
            <div style="width: 48px; height: 48px; border-radius: var(--radius-md); background: var(--primary-light); color: var(--primary-dark); display: flex; align-items: center; justify-content: center; font-size: 1.35rem; margin-bottom: 1rem;">
                <i class="fa-solid fa-shield-halved"></i>
            </div>
            <h3 style="font-size: 1.15rem; font-weight: 800; color: var(--text-primary); margin-bottom: 0.5rem;">100% Client-Side Privacy</h3>
            <p style="font-size: 0.9rem; color: var(--text-secondary); line-height: 1.6;">
                Most PDF converters and image editors upload your sensitive files to cloud servers. ToolsWallet executes file manipulations (merging, converting, splitting, resizing) directly in your browser using WebAssembly and Web Workers. Your data never touches our disks.
            </p>
        </div>

        <div class="tw-snapshot-card">
            <div style="width: 48px; height: 48px; border-radius: var(--radius-md); background: #fef3c7; color: #d97706; display: flex; align-items: center; justify-content: center; font-size: 1.35rem; margin-bottom: 1rem;">
                <i class="fa-solid fa-bolt"></i>
            </div>
            <h3 style="font-size: 1.15rem; font-weight: 800; color: var(--text-primary); margin-bottom: 0.5rem;">Sub-Millisecond Engine</h3>
            <p style="font-size: 0.9rem; color: var(--text-secondary); line-height: 1.6;">
                Built on high-performance modular PHP 8.2+ and lightweight JavaScript micro-utilities, our platform serves queries in under 15ms. No heavy framework bottlenecks or sluggish page loads.
            </p>
        </div>

        <div class="tw-snapshot-card">
            <div style="width: 48px; height: 48px; border-radius: var(--radius-md); background: #e0f2fe; color: #0284c7; display: flex; align-items: center; justify-content: center; font-size: 1.35rem; margin-bottom: 1rem;">
                <i class="fa-solid fa-lock-open"></i>
            </div>
            <h3 style="font-size: 1.15rem; font-weight: 800; color: var(--text-primary); margin-bottom: 0.5rem;">Forever Free & Open</h3>
            <p style="font-size: 0.9rem; color: var(--text-secondary); line-height: 1.6;">
                Every tool — from our Real-Time SEO Analyzer to JSON Formatter and Image Converter — is completely free to use without requiring an account or credit card.
            </p>
        </div>
    </div>

    <!-- Stats Banner -->
    <div style="background: var(--bg-surface); border: 1px solid var(--border-subtle); border-radius: var(--radius-lg); padding: 2.5rem; text-align: center; box-shadow: var(--shadow-sm); margin-bottom: 3.5rem;">
        <h2 style="font-size: 1.6rem; font-weight: 800; color: var(--text-primary); margin-bottom: 1.5rem;">The Platform at a Glance</h2>
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 1.5rem;">
            <div>
                <div style="font-size: 2.5rem; font-weight: 800; color: var(--primary);">50+</div>
                <div style="font-size: 0.88rem; font-weight: 700; color: var(--text-muted); text-transform: uppercase;">Active Utilities</div>
            </div>
            <div>
                <div style="font-size: 2.5rem; font-weight: 800; color: #0284c7;">7</div>
                <div style="font-size: 0.88rem; font-weight: 700; color: var(--text-muted); text-transform: uppercase;">Core Categories</div>
            </div>
            <div>
                <div style="font-size: 2.5rem; font-weight: 800; color: #f59e0b;">0s</div>
                <div style="font-size: 0.88rem; font-weight: 700; color: var(--text-muted); text-transform: uppercase;">Setup Time</div>
            </div>
            <div>
                <div style="font-size: 2.5rem; font-weight: 800; color: #10b981;">100%</div>
                <div style="font-size: 0.88rem; font-weight: 700; color: var(--text-muted); text-transform: uppercase;">Privacy Guaranteed</div>
            </div>
        </div>
    </div>

    <!-- CTA -->
    <div style="text-align: center;">
        <a href="<?= url() ?>#explore-tools" class="btn-primary-gradient" style="font-size: 1.05rem; padding: 0.9rem 2rem;">
            <i class="fa-solid fa-shapes"></i> Explore All Free Tools
        </a>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
