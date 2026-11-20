<?php
/**
 * ToolsWallet - Terms of Service
 * Domain: toolswallet.online
 */

require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/ads.php';
require_once __DIR__ . '/includes/seo.php';

$pageSeo = [
    'title' => 'Terms of Service - ToolsWallet',
    'description' => 'Review the terms of service for ToolsWallet. All utilities are provided free of charge under standard fair use guidelines.',
    'keywords' => 'terms of service, legal terms, toolswallet terms',
    'canonical' => url('terms.php'),
    'breadcrumbs' => [
        ['name' => 'Home', 'url' => url()],
        ['name' => 'Terms of Service', 'url' => url('terms.php')]
    ]
];

require_once __DIR__ . '/includes/header.php';
?>

<div class="container" style="max-width: 900px; padding: 2.5rem 1rem 4rem;">
    <!-- Breadcrumbs -->
    <nav class="breadcrumbs" aria-label="Breadcrumb">
        <a href="<?= url() ?>"><i class="fa-solid fa-house"></i> Home</a>
        <span class="separator">/</span>
        <span class="current">Terms of Service</span>
    </nav>

    <div style="background: var(--bg-surface); border: 1px solid var(--border-subtle); border-radius: var(--radius-xl); padding: 3rem 2.5rem; box-shadow: var(--shadow-sm); line-height: 1.75; color: var(--text-secondary);">
        <h1 style="font-size: 2.2rem; font-weight: 800; color: var(--text-primary); margin-bottom: 0.5rem;">Terms of Service</h1>
        <p style="font-size: 0.85rem; color: var(--text-muted); margin-bottom: 2rem;">Last Updated: November 20, 2026</p>

        <h2 style="font-size: 1.35rem; font-weight: 700; color: var(--text-primary); margin: 1.5rem 0 0.5rem;">1. Acceptance of Terms</h2>
        <p>By accessing and utilizing <strong>ToolsWallet</strong> (toolswallet.online), you agree to be bound by these Terms of Service. If you disagree with any part of these terms, please discontinue use of the platform.</p>

        <h2 style="font-size: 1.35rem; font-weight: 700; color: var(--text-primary); margin: 1.5rem 0 0.5rem;">2. Permitted Use & Fair Usage Policy</h2>
        <p>ToolsWallet provides online tools for personal, educational, commercial, and development purposes. You agree not to:</p>
        <ul style="padding-left: 1.5rem; margin-bottom: 1rem;">
            <li>Use automated bots or scrapers to perform denial-of-service attacks against our infrastructure.</li>
            <li>Use the Website SEO Checker or Redirect Checker to scan private IP ranges, local networks, or unauthorized targets.</li>
            <li>Attempt to upload malicious executables, exploits, or illegal materials.</li>
        </ul>

        <h2 style="font-size: 1.35rem; font-weight: 700; color: var(--text-primary); margin: 1.5rem 0 0.5rem;">3. Disclaimer of Warranties</h2>
        <p>All tools, converters, algorithms, and calculators are provided on an "as is" and "as available" basis without warranties of any kind. While we strive for 100% calculation accuracy, ToolsWallet is not liable for errors or discrepancies in outputs.</p>

        <h2 style="font-size: 1.35rem; font-weight: 700; color: var(--text-primary); margin: 1.5rem 0 0.5rem;">4. Intellectual Property</h2>
        <p>All platform logos, stylesheets, algorithms, and documentation are the property of ToolsWallet. Content that you convert or create using our tools remains 100% your own intellectual property.</p>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
