<?php
/**
 * ToolsWallet Master Header
 * Domain: toolswallet.online
 */

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/ads.php';
require_once __DIR__ . '/seo.php';
require_once __DIR__ . '/analytics.php';

// Record page analytics & single user footprint
Analytics::track($pageSeo['title'] ?? null, $tool['slug'] ?? null);

$allCategories = Database::getCategories();
?>
<!DOCTYPE html>
<html lang="en" data-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    
    <!-- Dynamic SEO and Schema.org Structured Data -->
    <?= render_seo_tags($pageSeo ?? []) ?>

    <!-- FontAwesome 6 Icons CDN -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" integrity="sha512-DTOQO9RWCH3ppGqcWaEA1BIZOC6xxalwEsw9c2QQeAIftl+Vegovlnee1c9QX4TctnWMn13TZye+giMm8e2LwA==" crossorigin="anonymous" referrerpolicy="no-referrer" />

    <!-- ToolsWallet Core Stylesheet & Favicon -->
    <link rel="icon" type="image/svg+xml" href="<?= asset('images/favicon.svg') ?>">
    <link rel="stylesheet" href="<?= asset('css/style.css') ?>">

    <!-- PDF-Lib & PDF.js for 100% Client-Side Private PDF Processing -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdf-lib/1.17.1/pdf-lib.min.js" integrity="sha512-z8IYLHO3JzgSUETrBiMNzpKG6nvQDksFYeiNTqgvwUC64245txXgvEL37424f46peCE49HSZN0241PKgy8642A==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.min.js" integrity="sha512-q+4liFvd8/FaOWMmmhylGiPHbiAkRR5222TOOCWDNCBI4jn66TXOKSFR48bL5b5eUC0bSTgkQKxmtY3LhnCasA==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
    <script>
        if (window.pdfjsLib) {
            pdfjsLib.GlobalWorkerOptions.workerSrc = 'https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.worker.min.js';
        }
    </script>

    <!-- Global JS Configuration -->
    <script>
        window.APP_CONFIG = {
            baseUrl: '<?= url() ?>',
            apiUrl: '<?= url("api/index.php") ?>'
        };
    </script>
</head>
<body>

    <!-- Sticky Navigation Bar -->
    <header class="navbar-sticky">
        <div class="container-wide navbar-inner">
            <!-- Brand Logo -->
            <a href="<?= url() ?>" class="brand-logo" aria-label="ToolsWallet Home">
                <div class="brand-icon">
                    <svg width="26" height="26" viewBox="0 0 44 44" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <!-- Wallet Body -->
                        <rect x="7" y="11" width="30" height="23" rx="4.5" fill="#FFFFFF" />
                        <!-- Wallet Flap Pocket -->
                        <path d="M7 15C7 13 9 11 12 11H32C35 11 37 13 37 15V18C33 16.5 27 16 22 16C17 16 11 16.5 7 18V15Z" fill="#ECFDF5" />
                        <!-- Card popping out -->
                        <path d="M14 7C14 6.45 14.45 6 15 6H29C29.55 6 30 6.45 30 7V11H14V7Z" fill="#D1FAE5" />
                        <!-- Clasp with emerald accent -->
                        <rect x="24" y="20" width="13" height="8" rx="3" fill="#10B981" />
                        <circle cx="28" cy="24" r="1.6" fill="#FFFFFF" />
                        <!-- Energetic Spark / Lightning inside wallet -->
                        <path d="M17 17L13 23H17L15 28L21 21H17L19 17H17Z" fill="#059669" />
                    </svg>
                </div>
                <div class="brand-text-wrap">
                    <span class="brand-name">Tools<span class="highlight">Wallet</span></span>
                    <span class="brand-badge-tag">ONLINE</span>
                </div>
            </a>

            <!-- Universal Nav Search Bar -->
            <div class="nav-search-bar">
                <i class="fa-solid fa-magnifying-glass nav-search-icon"></i>
                <input type="text" id="navSearchInput" class="nav-search-input" placeholder="Search 50+ free tools..." autocomplete="off">
                <span class="nav-search-badge">Ctrl K</span>
            </div>

            <!-- Navigation Links & Theme Switcher -->
            <div class="nav-actions">
                <a href="<?= url() ?>#explore-tools" class="nav-link-btn">
                    <i class="fa-solid fa-shapes"></i> All Tools
                </a>
                <a href="<?= url('category.php?slug=pdf-tools') ?>" class="nav-link-btn">
                    <i class="fa-solid fa-file-pdf"></i> PDF
                </a>
                <a href="<?= url('category.php?slug=seo-tools') ?>" class="nav-link-btn">
                    <i class="fa-solid fa-chart-line"></i> SEO
                </a>
                <a href="<?= url('category.php?slug=developer-tools') ?>" class="nav-link-btn">
                    <i class="fa-solid fa-code"></i> Dev
                </a>
                <a href="<?= url('about.php') ?>" class="nav-link-btn">
                    <i class="fa-solid fa-circle-info"></i> About
                </a>

                <!-- Header Login Popup Trigger or User Profile -->
                <?php if (!empty($_SESSION['tw_user'])): ?>
                    <div class="user-nav-dropdown" style="position: relative;">
                        <button type="button" class="user-nav-btn" onclick="toggleUserDropdown()" style="display: flex; align-items: center; gap: 0.5rem; background: var(--bg-secondary); border: 1px solid var(--primary-border); padding: 0.35rem 0.85rem; border-radius: var(--radius-full); cursor: pointer; font-weight: 700; font-size: 0.85rem; color: var(--text-primary);">
                            <div style="width: 26px; height: 26px; border-radius: 50%; background: linear-gradient(135deg, #10b981, #059669); color: #fff; display: flex; align-items: center; justify-content: center; font-size: 0.75rem;">
                                <i class="fa-solid fa-user"></i>
                            </div>
                            <span><?= e(explode(' ', $_SESSION['tw_user']['name'])[0]) ?></span>
                            <i class="fa-solid fa-chevron-down" style="font-size: 0.7rem; color: var(--text-muted);"></i>
                        </button>
                        <div id="userDropdownMenu" style="display: none; position: absolute; right: 0; top: 115%; width: 200px; background: var(--bg-surface); border: 1px solid var(--border-subtle); border-radius: var(--radius-md); box-shadow: var(--shadow-lg); padding: 0.5rem; z-index: 1000;">
                            <div style="padding: 0.5rem; border-bottom: 1px solid var(--border-subtle); font-size: 0.78rem; margin-bottom: 0.35rem;">
                                <div style="font-weight: 700; color: var(--text-primary);"><?= e($_SESSION['tw_user']['name']) ?></div>
                                <div style="color: var(--text-muted); font-size: 0.72rem;"><?= e($_SESSION['tw_user']['email']) ?></div>
                                <span class="badge" style="background: var(--primary-light); color: var(--primary-dark); font-size: 0.68rem; margin-top: 4px; display: inline-block;"><?= e($_SESSION['tw_user']['plan'] ?? 'Pro') ?></span>
                            </div>
                            <a href="<?= url() ?>#explore-tools" class="nav-link-btn" style="width: 100%; text-align: left; padding: 0.45rem 0.6rem; font-size: 0.82rem;"><i class="fa-solid fa-shapes"></i> Explore All Tools</a>
                            <a href="javascript:void(0)" onclick="handleUserLogout()" class="nav-link-btn" style="width: 100%; text-align: left; padding: 0.45rem 0.6rem; font-size: 0.82rem; color: #ef4444;"><i class="fa-solid fa-right-from-bracket"></i> Sign Out</a>
                        </div>
                    </div>
                <?php else: ?>
                    <button type="button" class="btn-primary-gradient btn-sm" onclick="openAuthModal('signin')" style="display: inline-flex; align-items: center; gap: 0.45rem; padding: 0.5rem 1rem; font-size: 0.85rem; border-radius: var(--radius-full); box-shadow: 0 4px 12px rgba(16, 185, 129, 0.28);">
                        <i class="fa-solid fa-arrow-right-to-bracket"></i> Login / Register
                    </button>
                <?php endif; ?>

                <!-- Dark / Light Mode Toggle Button -->
                <button type="button" id="themeToggleBtn" class="theme-toggle-btn" aria-label="Toggle Theme Mode">
                    <i class="fa-solid fa-moon"></i>
                </button>

                <!-- Mobile Menu Button -->
                <button type="button" id="mobileMenuBtn" class="mobile-menu-btn" aria-label="Open Navigation Menu">
                    <i class="fa-solid fa-bars"></i>
                </button>
            </div>
        </div>

        <!-- Mobile Navigation Drawer -->
        <div id="mobileNavDrawer" style="display: none; padding: 1rem 1.25rem; border-top: 1px solid var(--border-subtle); background: var(--bg-surface);">
            <div style="display: flex; flex-direction: column; gap: 0.5rem;">
                <a href="<?= url() ?>" class="nav-link-btn"><i class="fa-solid fa-house"></i> Home</a>
                <a href="<?= url() ?>#explore-tools" class="nav-link-btn"><i class="fa-solid fa-shapes"></i> Browse All Tools</a>
                <?php foreach ($allCategories as $cSlug => $cat): ?>
                    <a href="<?= url('category.php?slug=' . $cSlug) ?>" class="nav-link-btn">
                        <i class="fa-solid <?= e($cat['icon']) ?>" style="color: <?= e($cat['color']) ?>;"></i> <?= e($cat['name']) ?>
                    </a>
                <?php endforeach; ?>
            </div>
        </div>
    </header>

    <?php $headerAd = render_ad_slot('header_top', 'responsive_banner'); ?>
    <?php if (!empty($headerAd)): ?>
    <div class="container" style="margin-top: 0.5rem;">
        <?= $headerAd ?>
    </div>
    <?php endif; ?>

    <!-- Main Content Wrapper -->
    <main class="main-content-wrapper">
