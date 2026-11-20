<?php
/**
 * ToolsWallet - On-Page SEO Analyzer
 * Domain: toolswallet.online
 * Standalone Particular Page (NO SLUG)
 */

require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/ads.php';
require_once __DIR__ . '/../includes/seo.php';

$slug = 'seo-analyzer';
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
    <!-- ==============================================================
         Tool Interactive Workspace Card (Real-Time SaaS SEO Auditor)
         ============================================================== -->
    <div class="tool-workspace-card" id="seoAnalyzerApp">
        <style>
            .seo-audit-input-box {
                background: var(--bg-surface);
                border: 2px solid var(--border-subtle);
                border-radius: var(--radius-lg);
                padding: 1.5rem;
                box-shadow: 0 4px 20px -2px rgba(15, 23, 42, 0.04);
                margin-bottom: 2rem;
            }
            .seo-url-bar {
                display: flex;
                gap: 0.75rem;
                flex-wrap: wrap;
            }
            .seo-url-bar input {
                flex: 1;
                min-width: 260px;
                padding: 0.85rem 1.25rem;
                font-size: 1rem;
                border: 1px solid var(--border-subtle);
                border-radius: var(--radius-md);
                background: var(--bg-body);
                color: var(--text-primary);
                font-family: inherit;
                outline: none;
                transition: border-color 0.2s;
            }
            .seo-url-bar input:focus {
                border-color: var(--primary);
                box-shadow: 0 0 0 3px var(--primary-light);
            }
            .seo-sample-pills {
                display: flex;
                align-items: center;
                gap: 0.5rem;
                margin-top: 0.85rem;
                font-size: 0.82rem;
                color: var(--text-muted);
                flex-wrap: wrap;
            }
            .seo-sample-btn {
                background: var(--bg-body);
                border: 1px solid var(--border-subtle);
                padding: 0.25rem 0.65rem;
                border-radius: var(--radius-full);
                font-size: 0.78rem;
                color: var(--text-secondary);
                cursor: pointer;
                transition: all 0.2s;
            }
            .seo-sample-btn:hover {
                border-color: var(--primary);
                color: var(--primary-dark);
                background: var(--primary-light);
            }
            .seo-metric-row {
                display: grid;
                grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
                gap: 1rem;
                margin-bottom: 2rem;
            }
            .seo-metric-card {
                background: var(--bg-surface);
                border: 1px solid var(--border-subtle);
                border-radius: var(--radius-md);
                padding: 1.15rem;
                display: flex;
                flex-direction: column;
                gap: 0.4rem;
                box-shadow: 0 2px 8px rgba(0,0,0,0.02);
            }
            .seo-metric-card-num {
                font-size: 1.75rem;
                font-weight: 800;
                line-height: 1;
            }
            .seo-metric-card-title {
                font-size: 0.8rem;
                font-weight: 700;
                color: var(--text-muted);
                text-transform: uppercase;
                letter-spacing: 0.04em;
            }
            .seo-score-hero {
                display: flex;
                align-items: center;
                justify-content: space-between;
                background: linear-gradient(135deg, var(--bg-surface) 0%, var(--primary-light) 250%);
                border: 1px solid var(--primary-border);
                border-radius: var(--radius-lg);
                padding: 1.75rem 2rem;
                margin-bottom: 2rem;
                flex-wrap: wrap;
                gap: 1.5rem;
            }
            .seo-score-dial-wrap {
                display: flex;
                align-items: center;
                gap: 1.5rem;
            }
            .seo-score-dial {
                width: 100px;
                height: 100px;
                border-radius: 50%;
                display: flex;
                flex-direction: column;
                align-items: center;
                justify-content: center;
                border: 5px solid var(--primary);
                background: var(--bg-surface);
                box-shadow: 0 0 20px var(--primary-glow);
                flex-shrink: 0;
            }
            .seo-score-dial span.val {
                font-size: 2rem;
                font-weight: 800;
                color: var(--text-primary);
                line-height: 1;
            }
            .seo-score-dial span.denom {
                font-size: 0.75rem;
                font-weight: 700;
                color: var(--primary);
            }
            .seo-audit-tab-btn {
                background: transparent;
                border: none;
                border-bottom: 2px solid transparent;
                padding: 0.65rem 1.25rem;
                font-weight: 700;
                font-size: 0.92rem;
                color: var(--text-secondary);
                cursor: pointer;
                transition: all 0.2s;
            }
            .seo-audit-tab-btn.active {
                color: var(--primary);
                border-bottom-color: var(--primary);
            }
            .seo-check-item {
                display: flex;
                align-items: flex-start;
                gap: 1rem;
                padding: 1rem 1.25rem;
                background: var(--bg-surface);
                border: 1px solid var(--border-subtle);
                border-radius: var(--radius-md);
                margin-bottom: 0.75rem;
            }
            .seo-check-badge-passed {
                width: 32px;
                height: 32px;
                border-radius: 50%;
                background: var(--primary-light);
                color: var(--primary-dark);
                display: flex;
                align-items: center;
                justify-content: center;
                flex-shrink: 0;
                font-size: 0.85rem;
            }
            .seo-check-badge-warning {
                width: 32px;
                height: 32px;
                border-radius: 50%;
                background: #fef3c7;
                color: #d97706;
                display: flex;
                align-items: center;
                justify-content: center;
                flex-shrink: 0;
                font-size: 0.85rem;
            }
            .seo-check-badge-failed {
                width: 32px;
                height: 32px;
                border-radius: 50%;
                background: #fee2e2;
                color: #dc2626;
                display: flex;
                align-items: center;
                justify-content: center;
                flex-shrink: 0;
                font-size: 0.85rem;
            }
        </style>

        <!-- Input Box -->
        <div class="seo-audit-input-box">
            <label class="form-label" style="font-weight: 700; font-size: 0.95rem; margin-bottom: 0.6rem; display: block;">
                Enter Website or Webpage URL:
            </label>
            <div class="seo-url-bar">
                <input type="url" id="seoAuditUrl" placeholder="https://example.com" value="https://google.com">
                <button type="button" id="btnRunAudit" class="btn-primary-gradient" style="white-space: nowrap;">
                    <i class="fa-solid fa-magnifying-glass-chart"></i> Analyze Website
                </button>
            </div>
            <div class="seo-sample-pills">
                <span>Quick Test:</span>
                <button type="button" class="seo-sample-btn" onclick="document.getElementById('seoAuditUrl').value='https://google.com'; document.getElementById('btnRunAudit').click();">google.com</button>
                <button type="button" class="seo-sample-btn" onclick="document.getElementById('seoAuditUrl').value='https://wikipedia.org'; document.getElementById('btnRunAudit').click();">wikipedia.org</button>
                <button type="button" class="seo-sample-btn" onclick="document.getElementById('seoAuditUrl').value='https://github.com'; document.getElementById('btnRunAudit').click();">github.com</button>
                <span style="margin-left: auto; font-size: 0.75rem;"><i class="fa-solid fa-shield-halved text-emerald"></i> SSRF Defended</span>
            </div>
        </div>

        <!-- Real-Time Audit Results Dashboard -->
        <div id="auditResults" style="display: none;">
            
            <!-- Hero Score & Actions Bar -->
            <div class="seo-score-hero">
                <div class="seo-score-dial-wrap">
                    <div class="seo-score-dial" id="seoScoreDial">
                        <span class="val" id="auditScoreVal">--</span>
                        <span class="denom">/ 100</span>
                    </div>
                    <div>
                        <div style="font-size: 0.8rem; font-weight: 700; color: var(--primary-dark); text-transform: uppercase;">SEO Health Index</div>
                        <h2 style="font-size: 1.35rem; font-weight: 800; color: var(--text-primary); margin: 0.2rem 0;" id="auditEffectiveUrl">--</h2>
                        <div style="display: flex; gap: 0.5rem; flex-wrap: wrap; align-items: center; font-size: 0.82rem; margin-top: 0.35rem;">
                            <span id="auditHttpBadge" class="tw-badge-soft" style="background: var(--primary-light); color: var(--primary-dark);">HTTP 200 OK</span>
                            <span id="auditTimeBadge" style="color: var(--text-muted);"><i class="fa-solid fa-bolt"></i> 140ms</span>
                            <span id="auditSslBadge" style="color: #10b981;"><i class="fa-solid fa-lock"></i> HTTPS Secure</span>
                        </div>
                    </div>
                </div>

                <!-- Export & Share Buttons -->
                <div style="display: flex; gap: 0.6rem; flex-wrap: wrap;">
                    <button type="button" class="tool-action-btn" onclick="window.print()">
                        <i class="fa-solid fa-file-pdf"></i> Print / Save PDF
                    </button>
                    <button type="button" class="tool-action-btn" onclick="copyToClipboard(window.location.href, 'Audit link copied!')">
                        <i class="fa-solid fa-share-nodes"></i> Share
                    </button>
                </div>
            </div>

            <!-- 4 Metric Snapshot Cards -->
            <div class="seo-metric-row">
                <div class="seo-metric-card">
                    <span class="seo-metric-card-title">Passed Checks</span>
                    <span class="seo-metric-card-num" style="color: #10b981;" id="statPassedCount">0</span>
                    <span style="font-size: 0.75rem; color: var(--text-muted);">Fully optimized factors</span>
                </div>
                <div class="seo-metric-card">
                    <span class="seo-metric-card-title">Warnings</span>
                    <span class="seo-metric-card-num" style="color: #f59e0b;" id="statWarningCount">0</span>
                    <span style="font-size: 0.75rem; color: var(--text-muted);">Needs improvement</span>
                </div>
                <div class="seo-metric-card">
                    <span class="seo-metric-card-title">Errors</span>
                    <span class="seo-metric-card-num" style="color: #ef4444;" id="statFailedCount">0</span>
                    <span style="font-size: 0.75rem; color: var(--text-muted);">Critical ranking blockers</span>
                </div>
                <div class="seo-metric-card">
                    <span class="seo-metric-card-title">Speed & Security</span>
                    <span class="seo-metric-card-num" style="color: var(--primary-dark);" id="statSpeedVal">--</span>
                    <span style="font-size: 0.75rem; color: var(--text-muted);">Server response duration</span>
                </div>
            </div>

            <!-- Nav Tabs -->
            <div style="display: flex; border-bottom: 2px solid var(--border-subtle); margin-bottom: 1.5rem; gap: 0.5rem; overflow-x: auto;">
                <button type="button" class="seo-audit-tab-btn active" onclick="switchAuditTab(this, 'tabContentMeta')">
                    <i class="fa-solid fa-file-lines"></i> Meta & Content
                </button>
                <button type="button" class="seo-audit-tab-btn" onclick="switchAuditTab(this, 'tabTechnical')">
                    <i class="fa-solid fa-gears"></i> Technical & SSL
                </button>
                <button type="button" class="seo-audit-tab-btn" onclick="switchAuditTab(this, 'tabSocialMedia')">
                    <i class="fa-solid fa-share-nodes"></i> Media & Social
                </button>
                <button type="button" class="seo-audit-tab-btn" onclick="switchAuditTab(this, 'tabRecommendations')">
                    <i class="fa-solid fa-list-check"></i> Actionable Fixes
                </button>
            </div>

            <!-- Tab 1: Meta & Content -->
            <div id="tabContentMeta" class="seo-tab-panel">
                <div class="seo-check-item">
                    <div id="iconTitleCheck" class="seo-check-badge-passed"><i class="fa-solid fa-check"></i></div>
                    <div style="flex: 1;">
                        <div style="font-weight: 700; font-size: 0.92rem; color: var(--text-primary);">Page Title Tag</div>
                        <div id="auditTitleText" style="font-size: 0.88rem; color: var(--text-secondary); margin-top: 0.2rem; font-weight: 500;">--</div>
                    </div>
                </div>

                <div class="seo-check-item">
                    <div id="iconDescCheck" class="seo-check-badge-passed"><i class="fa-solid fa-check"></i></div>
                    <div style="flex: 1;">
                        <div style="font-weight: 700; font-size: 0.92rem; color: var(--text-primary);">Meta Description Tag</div>
                        <div id="auditDescText" style="font-size: 0.88rem; color: var(--text-secondary); margin-top: 0.2rem; font-weight: 500;">--</div>
                    </div>
                </div>

                <div class="seo-check-item">
                    <div id="iconH1Check" class="seo-check-badge-passed"><i class="fa-solid fa-check"></i></div>
                    <div style="flex: 1;">
                        <div style="font-weight: 700; font-size: 0.92rem; color: var(--text-primary);">H1 & Headings Hierarchy</div>
                        <div id="auditH1Text" style="font-size: 0.88rem; color: var(--text-secondary); margin-top: 0.2rem;">--</div>
                    </div>
                </div>
            </div>

            <!-- Tab 2: Technical & SSL -->
            <div id="tabTechnical" class="seo-tab-panel" style="display: none;">
                <div class="seo-check-item">
                    <div id="iconCanonicalCheck" class="seo-check-badge-passed"><i class="fa-solid fa-link"></i></div>
                    <div style="flex: 1;">
                        <div style="font-weight: 700; font-size: 0.92rem; color: var(--text-primary);">Canonical URL Consistency</div>
                        <div id="auditCanonicalText" style="font-size: 0.88rem; color: var(--text-secondary); margin-top: 0.2rem;">--</div>
                    </div>
                </div>

                <div class="seo-check-item">
                    <div id="iconRobotsCheck" class="seo-check-badge-passed"><i class="fa-solid fa-robot"></i></div>
                    <div style="flex: 1;">
                        <div style="font-weight: 700; font-size: 0.92rem; color: var(--text-primary);">Robots Meta Directive</div>
                        <div id="auditRobotsText" style="font-size: 0.88rem; color: var(--text-secondary); margin-top: 0.2rem;">--</div>
                    </div>
                </div>

                <div class="seo-check-item">
                    <div id="iconViewportCheck" class="seo-check-badge-passed"><i class="fa-solid fa-mobile-screen"></i></div>
                    <div style="flex: 1;">
                        <div style="font-weight: 700; font-size: 0.92rem; color: var(--text-primary);">Mobile Viewport & Responsiveness</div>
                        <div id="auditViewportText" style="font-size: 0.88rem; color: var(--text-secondary); margin-top: 0.2rem;">--</div>
                    </div>
                </div>
            </div>

            <!-- Tab 3: Media & Social -->
            <div id="tabSocialMedia" class="seo-tab-panel" style="display: none;">
                <div class="seo-check-item">
                    <div id="iconImagesCheck" class="seo-check-badge-passed"><i class="fa-solid fa-image"></i></div>
                    <div style="flex: 1;">
                        <div style="font-weight: 700; font-size: 0.92rem; color: var(--text-primary);">Image Accessibility (Alt Attributes)</div>
                        <div id="auditImagesText" style="font-size: 0.88rem; color: var(--text-secondary); margin-top: 0.2rem;">--</div>
                    </div>
                </div>

                <div class="seo-check-item">
                    <div id="iconOgCheck" class="seo-check-badge-passed"><i class="fa-brands fa-facebook"></i></div>
                    <div style="flex: 1;">
                        <div style="font-weight: 700; font-size: 0.92rem; color: var(--text-primary);">OpenGraph & Social Sharing Tags</div>
                        <div id="auditOgText" style="font-size: 0.88rem; color: var(--text-secondary); margin-top: 0.2rem;">--</div>
                    </div>
                </div>

                <div class="seo-check-item">
                    <div id="iconSchemaCheck" class="seo-check-badge-passed"><i class="fa-solid fa-code"></i></div>
                    <div style="flex: 1;">
                        <div style="font-weight: 700; font-size: 0.92rem; color: var(--text-primary);">Structured Data (Schema.org JSON-LD)</div>
                        <div id="auditSchemaText" style="font-size: 0.88rem; color: var(--text-secondary); margin-top: 0.2rem;">--</div>
                    </div>
                </div>
            </div>

            <!-- Tab 4: Actionable Fixes & Recommendations -->
            <div id="tabRecommendations" class="seo-tab-panel" style="display: none;">
                <div id="auditRecommendationsList"></div>
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