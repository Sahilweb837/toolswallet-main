<?php
/**
 * ToolsWallet - Privacy Policy
 * Domain: toolswallet.online
 */

require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/ads.php';
require_once __DIR__ . '/includes/seo.php';

$pageSeo = [
    'title' => 'Privacy Policy - ToolsWallet',
    'description' => 'ToolsWallet is committed to 100% user privacy. Learn how our browser-first processing prevents file uploads and keeps your documents secure.',
    'keywords' => 'privacy policy, data security, client-side tools, toolswallet privacy',
    'canonical' => url('privacy.php'),
    'breadcrumbs' => [
        ['name' => 'Home', 'url' => url()],
        ['name' => 'Privacy Policy', 'url' => url('privacy.php')]
    ]
];

require_once __DIR__ . '/includes/header.php';
?>

<div class="container" style="max-width: 900px; padding: 2.5rem 1rem 4rem;">
    <!-- Breadcrumbs -->
    <nav class="breadcrumbs" aria-label="Breadcrumb">
        <a href="<?= url() ?>"><i class="fa-solid fa-house"></i> Home</a>
        <span class="separator">/</span>
        <span class="current">Privacy Policy</span>
    </nav>

    <div style="background: var(--bg-surface); border: 1px solid var(--border-subtle); border-radius: var(--radius-xl); padding: 3rem 2.5rem; box-shadow: var(--shadow-sm); line-height: 1.75; color: var(--text-secondary);">
        <h1 style="font-size: 2.2rem; font-weight: 800; color: var(--text-primary); margin-bottom: 0.5rem;">Privacy Policy</h1>
        <p style="font-size: 0.85rem; color: var(--text-muted); margin-bottom: 2rem;">Last Updated: November 20, 2026</p>

        <h2 style="font-size: 1.35rem; font-weight: 700; color: var(--text-primary); margin: 1.5rem 0 0.5rem;">1. Our Privacy Philosophy</h2>
        <p>At ToolsWallet (<strong>toolswallet.online</strong>), we believe your data belongs to you. We design all our tools with a <strong>privacy-by-design</strong> architecture. Unlike traditional online converters, our PDF, image, developer, and calculator utilities process your documents client-side inside your own web browser whenever technologically feasible.</p>

        <h2 style="font-size: 1.35rem; font-weight: 700; color: var(--text-primary); margin: 1.5rem 0 0.5rem;">2. File Uploads & Zero Storage Guarantee</h2>
        <p>When you use tools such as <em>Images to PDF</em>, <em>Merge PDF</em>, <em>Split PDF</em>, <em>Rotate PDF</em>, or <em>Image Converter</em>:</p>
        <ul style="padding-left: 1.5rem; margin-bottom: 1rem;">
            <li>Your files are parsed in your browser's local memory using pure JavaScript and WebAssembly (via <code>PDF-Lib</code>, <code>PDF.js</code>, and HTML5 Canvas).</li>
            <li>Your files are <strong>never transmitted</strong> to or stored on our servers.</li>
            <li>For any optional server-side utilities, all temporary files are generated in an isolated directory and automatically destroyed after processing via automated garbage collection.</li>
        </ul>

        <h2 style="font-size: 1.35rem; font-weight: 700; color: var(--text-primary); margin: 1.5rem 0 0.5rem;">3. Real-Time SEO Analyzer & Crawler Protection</h2>
        <p>When using our <em>Website SEO Checker</em>, our backend crawler visits the public URL you request to analyze metadata, response headers, and headings. We do not inspect, cache, or store private website content, and we enforce strict Server-Side Request Forgery (SSRF) filters preventing queries to internal networks.</p>

        <h2 style="font-size: 1.35rem; font-weight: 700; color: var(--text-primary); margin: 1.5rem 0 0.5rem;">4. Cookies & Analytics</h2>
        <p>ToolsWallet does not use intrusive tracking cookies. We maintain standard session cookies only if you voluntarily create an account or save favorite tools. We may use privacy-respecting analytics to track aggregate page visit numbers to improve site performance.</p>

        <h2 style="font-size: 1.35rem; font-weight: 700; color: var(--text-primary); margin: 1.5rem 0 0.5rem;">5. Contact Us</h2>
        <p>If you have any questions or feedback regarding our privacy practices, please contact our team via our <a href="<?= url('contact.php') ?>" style="color: var(--primary); font-weight: 600;">Contact Page</a>.</p>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
