<?php
/**
 * ToolsWallet - Contact Support
 * Domain: toolswallet.online
 */

require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/ads.php';
require_once __DIR__ . '/includes/seo.php';

$sentSuccess = false;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $subject = trim($_POST['subject'] ?? '');
    $message = trim($_POST['message'] ?? '');

    if ($name && filter_var($email, FILTER_VALIDATE_EMAIL) && $message) {
        $sentSuccess = true;
    }
}

$pageSeo = [
    'title' => 'Contact Us & Tool Requests - ToolsWallet',
    'description' => 'Need help, found a bug, or have a tool request? Reach out to the ToolsWallet support engineering team.',
    'keywords' => 'contact toolswallet, tool request, report bug, online tools feedback',
    'canonical' => url('contact.php'),
    'breadcrumbs' => [
        ['name' => 'Home', 'url' => url()],
        ['name' => 'Contact Support', 'url' => url('contact.php')]
    ]
];

require_once __DIR__ . '/includes/header.php';
?>

<div class="container" style="max-width: 800px; padding: 2.5rem 1rem 4rem;">
    <!-- Breadcrumbs -->
    <nav class="breadcrumbs" aria-label="Breadcrumb">
        <a href="<?= url() ?>"><i class="fa-solid fa-house"></i> Home</a>
        <span class="separator">/</span>
        <span class="current">Contact Support</span>
    </nav>

    <div style="background: var(--bg-surface); border: 1px solid var(--border-subtle); border-radius: var(--radius-xl); padding: 3rem 2.5rem; box-shadow: var(--shadow-sm);">
        <div class="hero-pill" style="margin-bottom: 1rem;">
            <span class="hero-pill-dot"></span>
            <span>Support & Feature Requests</span>
        </div>
        <h1 style="font-size: 2.2rem; font-weight: 800; color: var(--text-primary); margin-bottom: 0.5rem;">Get in Touch</h1>
        <p style="font-size: 1.05rem; color: var(--text-secondary); margin-bottom: 2rem;">
            Have a tool suggestion, found an issue, or want to partner with us? We read every submission.
        </p>

        <?php if ($sentSuccess): ?>
            <div style="background: var(--primary-light); border: 1px solid var(--primary-border); color: var(--primary-dark); padding: 1.5rem; border-radius: var(--radius-md); margin-bottom: 2rem; display: flex; align-items: center; gap: 1rem;">
                <i class="fa-solid fa-circle-check" style="font-size: 1.8rem; color: var(--primary);"></i>
                <div>
                    <h3 style="font-size: 1.1rem; font-weight: 800; margin-bottom: 0.2rem;">Message Received!</h3>
                    <p style="margin: 0; font-size: 0.9rem;">Thank you for contacting ToolsWallet. Our support engineering team will review your message shortly.</p>
                </div>
            </div>
        <?php endif; ?>

        <form method="POST" action="<?= url('contact.php') ?>">
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.25rem; margin-bottom: 1.25rem;">
                <div class="form-group">
                    <label class="form-label" style="font-weight: 700; font-size: 0.88rem;">Your Full Name:</label>
                    <input type="text" name="name" class="form-control" required placeholder="Alex Morgan">
                </div>
                <div class="form-group">
                    <label class="form-label" style="font-weight: 700; font-size: 0.88rem;">Email Address:</label>
                    <input type="email" name="email" class="form-control" required placeholder="alex@example.com">
                </div>
            </div>

            <div class="form-group" style="margin-bottom: 1.25rem;">
                <label class="form-label" style="font-weight: 700; font-size: 0.88rem;">Subject / Tool Category:</label>
                <select name="subject" class="form-control" style="font-weight: 600;">
                    <option value="General Inquiry">General Question</option>
                    <option value="Tool Request">Request a New Tool</option>
                    <option value="Bug Report">Report a Bug / Issue</option>
                    <option value="Partnership">Partnership / API Access</option>
                </select>
            </div>

            <div class="form-group" style="margin-bottom: 1.5rem;">
                <label class="form-label" style="font-weight: 700; font-size: 0.88rem;">Message Details:</label>
                <textarea name="message" class="form-control" style="height: 160px;" required placeholder="Describe your inquiry or feature request..."></textarea>
            </div>

            <button type="submit" class="btn-primary-gradient" style="font-size: 1rem; padding: 0.85rem 2rem;">
                <i class="fa-solid fa-paper-plane"></i> Send Message
            </button>
        </form>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
