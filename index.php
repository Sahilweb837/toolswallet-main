 <?php
/**
 * ToolsWallet - All-in-One Online Tools Platform
 * Homepage: toolswallet.online
 */

require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/ads.php';
require_once __DIR__ . '/includes/seo.php';

$categories = Database::getCategories();
$allTools = Database::getTools();
$popularTools = Database::getPopularTools(8);
$trendingTools = Database::getTrendingTools(8);
$faqs = Database::getFaqs();

$pageSeo = [
    'title' => 'ToolsWallet - World\'s Best 50+ Free Online Tools for PDF, SEO, Social, AI, Images & Devs',
    'description' => 'ToolsWallet is the world\'s best all-in-one free online tools platform. Convert PDFs, audit SEO, crop social graphics, format JSON, compress images, and calculate loan EMIs with zero registration.',
    'keywords' => 'free online tools, pdf tools, seo tools, social media tools, image compressor, json formatter, qr code generator, online calculators, toolswallet, best online tools',
    'canonical' => url(),
    'faqs' => $faqs
];

require_once __DIR__ . '/includes/header.php';
?>

<style>
/* ============================================================
   TOOLSWALLET â€” ORANGE & WHITE PROFESSIONAL DESIGN SYSTEM
   ============================================================ */

:root {
  --primary: #10B981;
  --primary-dark: #059669;
  --primary-light: #ECFDF5;
  --primary-soft: #D1FAE5;
  --primary-glow: rgba(16, 185, 129, 0.15);
  --primary-glow-strong: rgba(16, 185, 129, 0.25);

  --white: #FFFFFF;
  --off-white: #F8FAFC;
  --surface: #FFFFFF;
  --surface-muted: #FBF6F1;

  --text-primary: #0F172A;
  --text-secondary: #334155;
  --text-muted: #64748B;

  --border-subtle: #E2E8F0;
  --border-strong: #CBD5E1;

  --shadow-xs: 0 1px 3px rgba(0,0,0,0.03);
  --shadow-sm: 0 4px 14px rgba(0,0,0,0.03), 0 2px 6px rgba(16,185,129,0.05);
  --shadow-md: 0 10px 26px rgba(0,0,0,0.05), 0 4px 12px rgba(16,185,129,0.08);
  --shadow-lg: 0 20px 45px rgba(0,0,0,0.07), 0 8px 20px rgba(16,185,129,0.10);

  --radius-sm: 10px;
  --radius-md: 16px;
  --radius-lg: 22px;
  --radius-xl: 32px;
  --radius-full: 999px;

  --primary: var(--primary);
  --accent-violet: #8B5CF6;
  --accent-green: #10B981;
}

/* Reset for the tools section only */
.tw-page * { box-sizing: border-box; }
.tw-page {
  font-family: 'Inter', system-ui, -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
  color: var(--text-primary);
  line-height: 1.55;
  background: var(--off-white);
}
.tw-page .container { max-width: 1280px; margin: 0 auto; padding: 0 1.5rem; }
.tw-page h1, .tw-page h2, .tw-page h3 { font-weight: 700; letter-spacing: -0.02em; line-height: 1.2; }

/* ===== HERO ===== */
.tw-page .hero-section {
  position: relative;
  padding: 3.5rem 0 3rem;
  background: var(--white);
  border-radius: 0 0 var(--radius-xl) var(--radius-xl);
  margin-bottom: 2.5rem;
  overflow: hidden;
  box-shadow: var(--shadow-sm);
}
.tw-page .hero-glow-bg {
  position: absolute;
  top: -30%; right: -8%;
  width: 520px; height: 520px;
  background: radial-gradient(circle, var(--primary-glow-strong) 0%, transparent 70%);
  border-radius: 50%;
  pointer-events: none;
}
.tw-page .hero-pill {
  display: inline-flex; align-items: center; gap: .55rem;
  background: var(--primary-light);
  color: var(--primary-dark);
  padding: .5rem 1.15rem;
  border-radius: var(--radius-full);
  font-weight: 600; font-size: .82rem;
  border: 1px solid rgba(16,185,129,.22);
  margin-bottom: 1.5rem;
}
.tw-page .hero-pill-dot {
  width: 8px; height: 8px; background: var(--primary);
  border-radius: 50%; display: inline-block;
  box-shadow: 0 0 0 4px rgba(16,185,129,.2);
  animation: tw-pulse 2s ease-in-out infinite;
}
@keyframes tw-pulse { 0%,100%{opacity:1} 50%{opacity:.5} }

.tw-page .hero-title {
  font-size: clamp(2rem, 5vw, 3.1rem);
  font-weight: 800;
  max-width: 820px;
  margin-bottom: 1.1rem;
  color: var(--text-primary);
}
.tw-page .gradient-text {
  background: linear-gradient(135deg, #10B981 0%, #059669 100%);
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
  background-clip: text;
}
.tw-page .hero-subtitle {
  font-size: clamp(1rem, 2vw, 1.15rem);
  color: var(--text-secondary);
  max-width: 680px;
  margin-bottom: 2rem;
}

/* Search box */
.tw-page .hero-search-wrapper { max-width: 740px; position: relative; }
.tw-page .hero-search-box {
  display: flex; align-items: center; gap: .5rem;
  background: var(--white);
  border: 2px solid var(--border-subtle);
  border-radius: var(--radius-full);
  padding: .4rem .4rem .4rem 1.4rem;
  transition: all .2s ease;
  box-shadow: var(--shadow-sm);
}
.tw-page .hero-search-box:focus-within {
  border-color: var(--primary);
  box-shadow: 0 0 0 4px var(--primary-glow);
}
.tw-page .search-icon { color: var(--primary); font-size: 1.05rem; }
.tw-page .hero-search-box input {
  flex: 1; border: none; outline: none; background: transparent;
  font-size: 1rem; padding: .75rem 0; color: var(--text-primary);
  font-family: inherit;
}
.tw-page .hero-search-box input::placeholder { color: var(--text-muted); }
.tw-page .hero-search-btn {
  background: var(--primary); color: #fff; border: none;
  border-radius: var(--radius-full);
  padding: .8rem 1.5rem; font-weight: 600; font-size: .95rem;
  display: inline-flex; align-items: center; gap: .5rem;
  cursor: pointer; transition: all .2s ease;
  white-space: nowrap; font-family: inherit;
}
.tw-page .hero-search-btn:hover {
  background: var(--primary-dark);
  transform: translateY(-1px);
  box-shadow: 0 6px 16px rgba(16,185,129,.35);
}
.tw-page .hero-quick-tags {
  display: flex; flex-wrap: wrap; align-items: center; gap: .55rem;
  margin-top: 1.2rem; font-size: .88rem; color: var(--text-muted);
}
.tw-page .quick-tag-btn {
  background: transparent;
  border: 1px solid var(--border-subtle);
  padding: .32rem .9rem; border-radius: var(--radius-full);
  font-size: .82rem; color: var(--text-secondary);
  cursor: pointer; transition: all .2s ease; font-family: inherit;
}
.tw-page .quick-tag-btn:hover {
  background: var(--primary-light);
  border-color: var(--primary);
  color: var(--primary-dark);
}

/* ===== SECTION HEADERS ===== */
.tw-page .section-header {
  display: flex; justify-content: space-between; align-items: flex-end;
  gap: 1rem; flex-wrap: wrap; margin-bottom: 1.8rem;
}
.tw-page .section-title-wrap h2 {
  font-size: clamp(1.35rem, 3vw, 1.7rem);
  font-weight: 800;
  display: flex; align-items: center; gap: .55rem;
  color: var(--text-primary);
}
.tw-page .section-subtitle {
  color: var(--text-secondary); font-size: .95rem; margin-top: .35rem;
}
.tw-page .section-action-link {
  color: var(--primary-dark); text-decoration: none;
  font-weight: 600; font-size: .92rem;
  display: inline-flex; align-items: center; gap: .4rem;
  transition: gap .2s ease;
}
.tw-page .section-action-link:hover { gap: .65rem; }

/* ===== RECENTLY USED ===== */
.tw-page .recently-used-section { margin-bottom: 2.5rem; }
.tw-page .recently-used-pills {
  display: flex; flex-wrap: wrap; gap: .55rem;
  min-height: 36px;
}
.tw-page .recently-used-pills .quick-tag-btn {
  background: var(--white); border: 1px solid var(--border-subtle);
  display: inline-flex; align-items: center; gap: .4rem;
  padding: .4rem 1rem;
}
.tw-page .recently-used-pills .quick-tag-btn i { color: var(--primary); font-size: .8rem; }

/* ===== TOOL CARDS ===== */
.tw-page .tools-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(260px, 1fr));
  gap: 1.25rem;
}
.tw-page .tool-card {
  display: flex; flex-direction: column;
  background: var(--white);
  border: 1px solid var(--border-subtle);
  border-radius: var(--radius-lg);
  padding: 1.4rem;
  text-decoration: none; color: inherit;
  transition: all .25s cubic-bezier(.2,.8,.2,1);
  position: relative;
  box-shadow: var(--shadow-xs);
}
.tw-page .tool-card:hover {
  transform: translateY(-4px);
  border-color: var(--primary-soft);
  box-shadow: var(--shadow-md);
  background: linear-gradient(180deg, var(--white) 0%, var(--primary-light) 180%);
}
.tw-page .tool-card-top {
  display: flex; justify-content: space-between; align-items: flex-start;
  margin-bottom: 1rem;
}
.tw-page .tool-icon-wrapper {
  width: 48px; height: 48px;
  background: linear-gradient(135deg, var(--primary-light), var(--primary-soft));
  border-radius: var(--radius-md);
  display: flex; align-items: center; justify-content: center;
  color: var(--primary-dark); font-size: 1.3rem;
  transition: all .25s ease;
}
.tw-page .tool-card:hover .tool-icon-wrapper {
  background: linear-gradient(135deg, var(--primary), #FF9F5A);
  color: #fff;
  transform: rotate(-4deg) scale(1.05);
}
.tw-page .tool-badge {
  font-size: .68rem; font-weight: 700; text-transform: uppercase;
  letter-spacing: .04em; padding: .25rem .6rem; border-radius: var(--radius-full);
  background: var(--primary-light); color: var(--primary-dark);
}
.tw-page .tool-badge.badge-new { background: #E6F7EF; color: #0E7A4F; }
.tw-page .tool-badge.badge-hot { background: #FFE9E4; color: #C2410C; }
.tw-page .tool-title {
  font-size: 1.08rem; font-weight: 700; margin-bottom: .4rem;
  color: var(--text-primary);
}
.tw-page .tool-desc {
  font-size: .88rem; color: var(--text-secondary);
  margin-bottom: 1.1rem; flex: 1;
  display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical;
  overflow: hidden;
}
.tw-page .tool-meta {
  display: flex; justify-content: space-between; align-items: center;
  font-size: .78rem; color: var(--text-muted);
  padding-top: .9rem; border-top: 1px solid var(--border-subtle);
}
.tw-page .tool-rating { color: #F59E0B; font-weight: 600; display: inline-flex; align-items: center; gap: .3rem; }
.tw-page .tool-run-count { display: inline-flex; align-items: center; gap: .3rem; }
.tw-page .tool-run-count i { color: var(--primary); }

/* ===== CATEGORIES ===== */
.tw-page .categories-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(240px, 1fr));
  gap: 1.25rem;
}
.tw-page .category-card {
  background: var(--white);
  border: 1px solid var(--border-subtle);
  border-radius: var(--radius-lg);
  padding: 1.4rem;
  text-decoration: none; color: inherit;
  transition: all .25s ease;
  position: relative;
  overflow: hidden;
}
.tw-page .category-card::before {
  content: '';
  position: absolute; top: 0; left: 0; right: 0; height: 3px;
  background: var(--category-color, var(--primary));
  opacity: 0; transition: opacity .25s ease;
}
.tw-page .category-card:hover {
  transform: translateY(-4px);
  box-shadow: var(--shadow-md);
  border-color: var(--category-color, var(--primary));
}
.tw-page .category-card:hover::before { opacity: 1; }
.tw-page .category-header {
  display: flex; justify-content: space-between; align-items: flex-start;
  margin-bottom: 1rem;
}
.tw-page .category-icon-box {
  width: 46px; height: 46px; border-radius: var(--radius-md);
  background: var(--category-light, var(--primary-light));
  color: var(--category-color, var(--primary));
  display: flex; align-items: center; justify-content: center;
  font-size: 1.25rem;
}
.tw-page .category-badge-count {
  font-size: .72rem; font-weight: 600;
  background: var(--surface-muted); color: var(--text-secondary);
  padding: .25rem .6rem; border-radius: var(--radius-full);
}
.tw-page .category-title {
  font-size: 1.08rem; font-weight: 700; margin-bottom: .35rem;
}
.tw-page .category-desc {
  font-size: .86rem; color: var(--text-secondary); margin-bottom: 1rem;
  display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical;
  overflow: hidden;
}
.tw-page .category-footer-link {
  font-size: .82rem; font-weight: 600;
  color: var(--category-color, var(--primary-dark));
  display: inline-flex; align-items: center; gap: .35rem;
}

/* ===== FILTER TABS ===== */
.tw-page .category-filter-tabs {
  display: flex; flex-wrap: wrap; gap: .5rem; margin-bottom: 1.8rem;
}
.tw-page .filter-tab-btn {
  background: var(--white);
  border: 1px solid var(--border-subtle);
  border-radius: var(--radius-full);
  padding: .5rem 1.1rem;
  font-size: .85rem; font-weight: 600;
  color: var(--text-secondary);
  cursor: pointer; transition: all .2s ease;
  font-family: inherit;
}
.tw-page .filter-tab-btn:hover {
  border-color: var(--primary);
  color: var(--primary-dark);
  background: var(--primary-light);
}
.tw-page .filter-tab-btn.active {
  background: var(--primary);
  border-color: var(--primary);
  color: #fff;
  box-shadow: 0 4px 12px rgba(255,123,44,.3);
}

/* ===== FEATURES ===== */
.tw-page .features-section {
  background: var(--white);
  border-radius: var(--radius-xl);
  padding: 3.5rem 0;
  margin-bottom: 4rem;
  box-shadow: var(--shadow-sm);
}
.tw-page .features-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(240px, 1fr));
  gap: 1.5rem;
}
.tw-page .feature-box {
  padding: 1.5rem;
  border-radius: var(--radius-lg);
  background: var(--surface-muted);
  border: 1px solid var(--border-subtle);
  transition: all .25s ease;
}
.tw-page .feature-box:hover {
  background: var(--white);
  border-color: var(--primary-soft);
  box-shadow: var(--shadow-sm);
  transform: translateY(-3px);
}
.tw-page .feature-icon {
  width: 48px; height: 48px;
  background: var(--primary-light);
  color: var(--primary-dark);
  border-radius: var(--radius-md);
  display: flex; align-items: center; justify-content: center;
  font-size: 1.25rem; margin-bottom: 1rem;
}
.tw-page .feature-title {
  font-size: 1.05rem; font-weight: 700; margin-bottom: .4rem;
}
.tw-page .feature-desc {
  font-size: .88rem; color: var(--text-secondary); line-height: 1.6;
}

/* ===== FAQ ===== */
.tw-page .faq-section { max-width: 820px; margin: 0 auto; }
.tw-page .faq-item {
  background: var(--white);
  border: 1px solid var(--border-subtle);
  border-radius: var(--radius-md);
  margin-bottom: .75rem;
  overflow: hidden;
  transition: all .2s ease;
}
.tw-page .faq-item.active {
  border-color: var(--primary-soft);
  box-shadow: var(--shadow-sm);
}
.tw-page .faq-question {
  width: 100%; background: transparent; border: none;
  display: flex; justify-content: space-between; align-items: center;
  padding: 1.1rem 1.35rem;
  font-size: .97rem; font-weight: 600;
  color: var(--text-primary);
  cursor: pointer; text-align: left;
  font-family: inherit;
  gap: 1rem;
}
.tw-page .faq-question i {
  color: var(--primary);
  font-size: .85rem;
  transition: transform .25s ease;
  flex-shrink: 0;
}
.tw-page .faq-item.active .faq-question i { transform: rotate(180deg); }
.tw-page .faq-answer {
  max-height: 0; overflow: hidden;
  transition: max-height .3s ease, padding .3s ease;
  padding: 0 1.35rem;
}
.tw-page .faq-item.active .faq-answer {
  max-height: 400px;
  padding: 0 1.35rem 1.1rem;
}
.tw-page .faq-answer p {
  font-size: .9rem; color: var(--text-secondary);
  line-height: 1.7;
}

/* ===== SEO CONTENT ===== */
.tw-page .seo-block {
  background: var(--white);
  border: 1px solid var(--border-subtle);
  border-radius: var(--radius-xl);
  padding: 2.5rem;
  box-shadow: var(--shadow-sm);
  margin-bottom: 4rem;
}
.tw-page .seo-block h2 {
  font-size: 1.5rem; font-weight: 800; margin-bottom: 1rem;
  color: var(--text-primary);
}
.tw-page .seo-block p {
  color: var(--text-secondary);
  line-height: 1.75;
  margin-bottom: 1rem;
  font-size: .95rem;
}
.tw-page .seo-block p:last-child { margin-bottom: 0; }
.tw-page .seo-block strong { color: var(--primary-dark); }

/* ===== EMPTY STATE ===== */
.tw-page #searchEmptyState i { color: var(--text-muted); }
.tw-page #searchEmptyState h3 { font-weight: 700; }

/* ===== MODERN SAAS SNAPSHOT GRID (REF. SEMAS UI) ===== */
.tw-hero-snapshot-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
  gap: 1.25rem;
  margin-top: 2.75rem;
}
.tw-snapshot-card {
  background: var(--white);
  border: 1px solid var(--border-subtle);
  border-radius: var(--radius-lg);
  padding: 1.35rem 1.45rem;
  box-shadow: 0 8px 24px -4px rgba(15, 23, 42, 0.05), 0 2px 6px -1px rgba(16, 185, 129, 0.04);
  transition: all .25s ease;
  display: flex;
  flex-direction: column;
}
.tw-snapshot-card:hover {
  transform: translateY(-3px);
  border-color: var(--primary-soft);
  box-shadow: 0 16px 32px -6px rgba(16, 185, 129, 0.12);
}
.tw-snapshot-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 1.1rem;
}
.tw-snapshot-badge {
  display: inline-flex;
  align-items: center;
  gap: .45rem;
  font-size: .82rem;
  font-weight: 700;
  color: var(--text-primary);
}
.tw-snapshot-badge i {
  color: var(--primary);
}
.tw-trend-badge {
  font-size: .75rem;
  font-weight: 700;
  padding: .2rem .55rem;
  border-radius: var(--radius-full);
}
.tw-trend-up {
  background: var(--primary-light);
  color: var(--primary-dark);
}
.tw-stat-list {
  display: flex;
  flex-direction: column;
  gap: .85rem;
}
.tw-stat-row {
  display: flex;
  align-items: center;
  gap: .85rem;
}
.tw-stat-badge-num {
  width: 40px;
  height: 40px;
  border-radius: var(--radius-md);
  background: var(--primary-light);
  color: var(--primary-dark);
  display: flex;
  align-items: center;
  justify-content: center;
  font-weight: 800;
  font-size: .95rem;
  flex-shrink: 0;
  border: 1px solid var(--primary-soft);
}
.tw-stat-details {
  display: flex;
  flex-direction: column;
}
.tw-stat-name {
  font-size: .88rem;
  font-weight: 700;
  color: var(--text-primary);
  line-height: 1.2;
}
.tw-stat-delta {
  font-size: .75rem;
  font-weight: 600;
  margin-top: .15rem;
}
.text-emerald { color: var(--primary); }
.tw-status-dot-pulse {
  width: 10px;
  height: 10px;
  background: var(--primary);
  border-radius: 50%;
  box-shadow: 0 0 0 4px var(--primary-glow);
  animation: tw-pulse 2s infinite ease-in-out;
}
.tw-gauge-wrap {
  display: flex;
  justify-content: center;
  align-items: center;
  padding: .75rem 0;
  flex: 1;
}
.tw-gauge-circle {
  width: 110px;
  height: 110px;
  border-radius: 50%;
  border: 4px solid var(--primary-light);
  border-top-color: var(--primary);
  border-right-color: var(--primary);
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  box-shadow: inset 0 2px 6px rgba(16, 185, 129, 0.08);
}
.tw-gauge-val {
  font-size: 1.65rem;
  font-weight: 800;
  color: var(--text-primary);
  line-height: 1;
}
.tw-gauge-val small {
  font-size: .85rem;
  font-weight: 700;
  color: var(--primary);
}
.tw-gauge-sub {
  font-size: .7rem;
  font-weight: 600;
  color: var(--text-muted);
  margin-top: .2rem;
}
.tw-gauge-footer {
  text-align: center;
  font-size: .8rem;
  font-weight: 600;
  color: var(--primary-dark);
  background: var(--primary-light);
  padding: .45rem .75rem;
  border-radius: var(--radius-full);
  margin-top: auto;
}
.tw-snapshot-cta-title {
  font-size: 1.08rem;
  font-weight: 800;
  color: var(--text-primary);
  margin-bottom: .4rem;
}
.tw-snapshot-cta-desc {
  font-size: .82rem;
  color: var(--text-secondary);
  line-height: 1.55;
  margin-bottom: 1.1rem;
  flex: 1;
}
.tw-snapshot-cta-btn {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  gap: .55rem;
  background: linear-gradient(135deg, #10B981 0%, #059669 100%);
  color: #fff !important;
  text-decoration: none;
  font-size: .88rem;
  font-weight: 700;
  padding: .75rem 1.25rem;
  border-radius: var(--radius-full);
  box-shadow: 0 4px 14px rgba(16, 185, 129, 0.35);
  transition: all .2s ease;
}
.tw-snapshot-cta-btn:hover {
  transform: translateY(-2px);
  box-shadow: 0 6px 20px rgba(16, 185, 129, 0.45);
}
.tw-badge-soft {
  background: var(--primary-light);
  color: var(--primary-dark);
  font-size: .72rem;
  font-weight: 700;
  padding: .2rem .55rem;
  border-radius: var(--radius-full);
}

/* ===== RESPONSIVE ===== */
@media (max-width: 640px) {
  .tw-page .hero-search-box { flex-wrap: wrap; border-radius: var(--radius-lg); padding: .6rem; }
  .tw-page .hero-search-box input { width: 100%; order: 1; padding: .5rem .5rem; }
  .tw-page .hero-search-btn { width: 100%; justify-content: center; order: 2; margin-top: .4rem; }
  .tw-page .search-icon { display: none; }
  .tw-page .seo-block { padding: 1.5rem; }
  .tw-page .features-section { padding: 2rem 0; }
}
</style>

<div class="tw-page">

<!-- ==============================================================
     Hero Section
     ============================================================== -->
<section class="hero-section">
    <div class="hero-glow-bg"></div>
    <div class="container">
        <div class="hero-pill">
            <span class="hero-pill-dot"></span>
            <span>World's Best Online Tools &bull; 50+ Free &bull; 100% Client-Side Privacy</span>
        </div>

        <h1 class="hero-title">
            The World's Best <span class="gradient-text">Online Tools Platform</span><br>For Creators & Developers
        </h1>

        <p class="hero-subtitle">
            Instant, premium utilities for PDF documents, on-page SEO, social media, AI writing, image optimization, developer workflows, and finance calculations. Trusted by millions â€” no login, no installation, no cost.
        </p>

        <div class="hero-search-wrapper">
            <div class="hero-search-box">
                <i class="fa-solid fa-magnifying-glass search-icon"></i>
                <input type="text" id="heroSearchInput" placeholder="What tool do you need today? (e.g. JSON, PDF, QR Code, EMI...)" autocomplete="off">
                <button type="button" class="hero-search-btn">
                    <i class="fa-solid fa-wand-magic-sparkles"></i>
                    <span>Find Tool</span>
                </button>
            </div>

            <div id="searchResultsDropdown" class="search-results-dropdown"></div>

            <div class="hero-quick-tags">
                <span>Trending:</span>
                <button type="button" class="quick-tag-btn" data-search="JSON Formatter">JSON Formatter</button>
                <button type="button" class="quick-tag-btn" data-search="PDF to Images">PDF to Images</button>
                <button type="button" class="quick-tag-btn" data-search="QR Code">QR Code</button>
                <button type="button" class="quick-tag-btn" data-search="Image Compressor">Image Compressor</button>
                <button type="button" class="quick-tag-btn" data-search="Loan EMI">Loan EMI</button>
                <button type="button" class="quick-tag-btn" data-search="Word Counter">Word Counter</button>
            </div>
        </div>

        <!-- Modern SaaS Dashboard Snapshot Cards (Ref. Semas UI) -->
        <div class="tw-hero-snapshot-grid">
            <!-- Card 1: Platform Snapshot -->
            <div class="tw-snapshot-card">
                <div class="tw-snapshot-header">
                    <span class="tw-snapshot-badge"><i class="fa-solid fa-chart-pie"></i> Platform Snapshot</span>
                    <span class="tw-trend-badge tw-trend-up"><i class="fa-solid fa-arrow-trend-up"></i> +14%</span>
                </div>
                <div class="tw-stat-list">
                    <div class="tw-stat-row">
                        <div class="tw-stat-badge-num">50+</div>
                        <div class="tw-stat-details">
                            <span class="tw-stat-name">Active Tools</span>
                            <span class="tw-stat-delta text-emerald"><i class="fa-solid fa-check"></i> 100% Free Forever</span>
                        </div>
                    </div>
                    <div class="tw-stat-row">
                        <div class="tw-stat-badge-num">0s</div>
                        <div class="tw-stat-details">
                            <span class="tw-stat-name">Setup Required</span>
                            <span class="tw-stat-delta text-emerald"><i class="fa-solid fa-bolt"></i> Instant in Browser</span>
                        </div>
                    </div>
                    <div class="tw-stat-row">
                        <div class="tw-stat-badge-num">#1</div>
                        <div class="tw-stat-details">
                            <span class="tw-stat-name">Data Privacy</span>
                            <span class="tw-stat-delta text-emerald"><i class="fa-solid fa-shield-halved"></i> Zero Server File Leaks</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Card 2: Live Engine Speed & Efficiency -->
            <div class="tw-snapshot-card">
                <div class="tw-snapshot-header">
                    <span class="tw-snapshot-badge"><i class="fa-solid fa-gauge-high"></i> Live Engine Speed</span>
                    <span class="tw-status-dot-pulse" title="Active Engine Online"></span>
                </div>
                <div class="tw-gauge-wrap">
                    <div class="tw-gauge-circle">
                        <div class="tw-gauge-val">140<small>ms</small></div>
                        <div class="tw-gauge-sub">Avg Processing</div>
                    </div>
                </div>
                <div class="tw-gauge-footer">
                    <span>⚡ Sub-millisecond execution</span>
                </div>
            </div>

            <!-- Card 3: Real-Time SEO & Quick Launch -->
            <div class="tw-snapshot-card">
                <div class="tw-snapshot-header">
                    <span class="tw-snapshot-badge"><i class="fa-solid fa-magnifying-glass-chart"></i> Real-Time SEO</span>
                    <span class="tw-badge-soft">SSRF Protected</span>
                </div>
                <h4 class="tw-snapshot-cta-title">Audit Any Website Live</h4>
                <p class="tw-snapshot-cta-desc">Full on-page crawler: inspect title, meta, H1-H6, mobile viewport, SSL, and image alt tags in seconds.</p>
                <a href="<?= tool_url('seo-analyzer') ?>" class="tw-snapshot-cta-btn">
                    <span>Run Free SEO Audit</span>
                    <i class="fa-solid fa-arrow-right"></i>
                </a>
            </div>
        </div>
    </div>
</section>

<!-- ==============================================================
     Recently Used Tools
     ============================================================== -->
<section id="recentToolsSection" class="recently-used-section">
    <div class="container">
        <div class="section-header" style="margin-bottom: 0.75rem;">
            <div class="section-title-wrap">
                <h2 style="font-size: 1.2rem;"><i class="fa-solid fa-clock-rotate-left" style="color: var(--primary);"></i> Recently Used Tools</h2>
            </div>
        </div>
        <div id="recentToolsPills" class="recently-used-pills"></div>
    </div>
</section>

<!-- ==============================================================
     Popular Tools Section
     ============================================================== -->
<section class="container" style="margin-bottom: 3.5rem;">
    <div class="section-header">
        <div class="section-title-wrap">
            <h2><i class="fa-solid fa-fire" style="color: #EF4444;"></i> Popular Utilities</h2>
            <p class="section-subtitle">Our most frequently used online tools by developers, designers, and marketers.</p>
        </div>
        <a href="#explore-tools" class="section-action-link">View All Tools <i class="fa-solid fa-arrow-right"></i></a>
    </div>

    <div class="tools-grid">
        <?php foreach ($popularTools as $tool): ?>
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
                    <div class="tool-icon-wrapper">
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
</section>

<!-- Middle In-Content Ad Slot -->
<div class="container">
    <?= render_ad_slot('home_middle', 'responsive_banner') ?>
</div>

<!-- ==============================================================
     Category Grid Section
     ============================================================== -->
<section class="container" style="margin-bottom: 4rem;">
    <div class="section-header">
        <div class="section-title-wrap">
            <h2><i class="fa-solid fa-layer-group" style="color: var(--primary);"></i> Browse By Category</h2>
            <p class="section-subtitle">Organized categories equipped with comprehensive tool suites.</p>
        </div>
    </div>

    <div class="categories-grid">
        <?php foreach ($categories as $cSlug => $cat): 
            $catToolsCount = count(array_filter($allTools, fn($t) => ($t['category_slug'] ?? '') === $cSlug));
        ?>
            <a href="<?= url('category.php?slug=' . $cSlug) ?>" 
               class="category-card" 
               style="--category-color: <?= e($cat['color']) ?>; --category-light: <?= e($cat['color']) ?>18;">
                <div class="category-header">
                    <div class="category-icon-box">
                        <i class="fa-solid <?= e($cat['icon']) ?>"></i>
                    </div>
                    <span class="category-badge-count"><?= $catToolsCount ?> Tools</span>
                </div>
                <h3 class="category-title"><?= e($cat['name']) ?></h3>
                <p class="category-desc"><?= e($cat['description']) ?></p>
                <div class="category-footer-link">
                    Explore Suite <i class="fa-solid fa-chevron-right"></i>
                </div>
            </a>
        <?php endforeach; ?>
    </div>
</section>

<!-- ==============================================================
     All Tools Explorer
     ============================================================== -->
<section id="explore-tools" class="container" style="margin-bottom: 4.5rem;">
    <div class="section-header">
        <div class="section-title-wrap">
            <h2><i class="fa-solid fa-shapes" style="color: var(--accent-violet);"></i> All Online Tools</h2>
            <p class="section-subtitle">Filter instantly across our entire collection of 50+ free utilities.</p>
        </div>
    </div>

    <div class="category-filter-tabs">
        <button type="button" class="filter-tab-btn active" data-category="all">All Tools (<?= count($allTools) ?>)</button>
        <?php foreach ($categories as $cSlug => $cat): 
            $cnt = count(array_filter($allTools, fn($t) => ($t['category_slug'] ?? '') === $cSlug));
        ?>
            <button type="button" class="filter-tab-btn" data-category="<?= e($cSlug) ?>">
                <?= e($cat['name']) ?> (<?= $cnt ?>)
            </button>
        <?php endforeach; ?>
    </div>

    <div class="tools-grid">
        <?php foreach ($allTools as $tool): ?>
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
                    <div class="tool-icon-wrapper">
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

    <div id="searchEmptyState" style="display: none; text-align: center; padding: 4rem 1rem;">
        <i class="fa-solid fa-face-frown" style="font-size: 3rem; color: var(--text-muted); margin-bottom: 1rem;"></i>
        <h3 style="font-weight: 700;">No tools found</h3>
        <p style="color: var(--text-secondary); margin-top: 0.5rem;">Try refining your search keyword or browse by category above.</p>
    </div>
</section>

<!-- ==============================================================
     Why Choose ToolsWallet
     ============================================================== -->
<section class="features-section">
    <div class="container">
        <div style="text-align: center; max-width: 680px; margin: 0 auto 3rem;">
            <h2 style="font-size: clamp(1.6rem, 4vw, 2rem); font-weight: 800; letter-spacing: -0.02em;">
                Engineered for Speed, Privacy & Power
            </h2>
            <p style="color: var(--text-secondary); margin-top: 0.5rem;">
                The world's best online tools are built with modern browser APIs and a privacy-first architecture.
            </p>
        </div>

        <div class="features-grid">
            <div class="feature-box">
                <div class="feature-icon"><i class="fa-solid fa-bolt-lightning"></i></div>
                <h3 class="feature-title">Lightning-Fast Execution</h3>
                <p class="feature-desc">Most utilities run natively in your browser using WebAssembly and HTML5 Canvas with instant results.</p>
            </div>

            <div class="feature-box">
                <div class="feature-icon"><i class="fa-solid fa-shield-halved"></i></div>
                <h3 class="feature-title">100% Private & Secure</h3>
                <p class="feature-desc">Files and personal data are never stored permanently. In-browser tools never upload your files anywhere.</p>
            </div>

            <div class="feature-box">
                <div class="feature-icon"><i class="fa-solid fa-circle-dollar-to-slot"></i></div>
                <h3 class="feature-title">Completely Free Forever</h3>
                <p class="feature-desc">No subscriptions, no hidden upgrade paywalls, and no credit card required. Pure unlimited productivity.</p>
            </div>

            <div class="feature-box">
                <div class="feature-icon"><i class="fa-solid fa-mobile-screen-button"></i></div>
                <h3 class="feature-title">Mobile-First Design</h3>
                <p class="feature-desc">Engineered to work seamlessly across mobile smartphones, tablets, laptops, and ultra-wide desktop screens.</p>
            </div>
        </div>
    </div>
</section>

<!-- ==============================================================
     FAQ Accordion Section
     ============================================================== -->
<section id="faqs" class="container" style="margin-bottom: 4.5rem;">
    <div style="text-align: center; max-width: 620px; margin: 0 auto 2.5rem;">
        <h2 style="font-size: 1.85rem; font-weight: 800;">
            <i class="fa-solid fa-circle-question" style="color: var(--primary);"></i> Frequently Asked Questions
        </h2>
        <p style="color: var(--text-secondary); margin-top: 0.4rem;">Everything you need to know about using the world's best online tools.</p>
    </div>

    <div class="faq-section">
        <?php foreach ($faqs as $idx => $faq): ?>
            <div class="faq-item <?= $idx === 0 ? 'active' : '' ?>">
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
</section>

<!-- ==============================================================
     SEO Long-Form Content Block
     ============================================================== -->
<section class="container">
    <div class="seo-block">
        <h2>ToolsWallet: The World's Best Online Tool Suite for Modern Digital Workflows</h2>
        <p>
            In today's fast-paced digital ecosystem, creators, developers, designers, and marketers need reliable web utilities that do not require software installations or expensive monthly SaaS subscriptions. <strong>ToolsWallet (toolswallet.online)</strong> bridges this gap by offering a curated, secure suite of 50+ essential tools spanning PDF document manipulation, on-page SEO audits, image format optimization, social media previewing, AI content writing, code minification, and mathematical financial calculators.
        </p>
        <p>
            Every utility on ToolsWallet is architected with modern web technologies such as client-side HTML5 canvas, Web Cryptography API, and lightweight server processing with strict SSRF (Server-Side Request Forgery) safeguards. Whether you need to compress an oversized image, beautify complex JSON payloads, generate Google rich snippet schema, or calculate your monthly mortgage EMI, ToolsWallet provides high-performance, private, zero-footprint execution directly in your browser.
        </p>
        <p>
            <strong>Why millions of creators trust us:</strong> we ship the fastest online tools on the web, keep every file 100% private, and never ask for a login, a credit card, or a subscription. Whether you're a solo developer debugging JSON at 3 AM, a marketer auditing on-page SEO, or a student compressing a photo for a resume â€” ToolsWallet is engineered to be the world's best online tools platform.
        </p>
    </div>
</section>

</div><!-- /.tw-page -->

<script>
// ===== TOOLSWALLET HOMEPAGE INTERACTIONS =====
(function() {
  'use strict';

  // ---------- FAQ Accordion ----------
  document.querySelectorAll('.tw-page .faq-question').forEach(function(btn) {
    btn.addEventListener('click', function() {
      var item = this.closest('.faq-item');
      var isActive = item.classList.contains('active');
      document.querySelectorAll('.tw-page .faq-item').forEach(function(i) {
        i.classList.remove('active');
      });
      if (!isActive) item.classList.add('active');
    });
  });

  // ---------- Category Filter Tabs ----------
  var tabs = document.querySelectorAll('.tw-page .filter-tab-btn');
  var cards = document.querySelectorAll('#explore-tools .tool-card');

  tabs.forEach(function(tab) {
    tab.addEventListener('click', function() {
      var cat = this.getAttribute('data-category');
      tabs.forEach(function(t) { t.classList.remove('active'); });
      this.classList.add('active');

      var visible = 0;
      cards.forEach(function(card) {
        var match = (cat === 'all') || (card.getAttribute('data-category') === cat);
        card.style.display = match ? '' : 'none';
        if (match) visible++;
      });
      var empty = document.getElementById('searchEmptyState');
      if (empty) empty.style.display = visible === 0 ? 'block' : 'none';
    });
  });

  // ---------- Search (hero + quick tags) ----------
  var heroInput = document.getElementById('heroSearchInput');
  var searchResults = document.getElementById('searchResultsDropdown');

  function filterTools(query) {
    query = (query || '').trim().toLowerCase();
    var visible = 0;
    cards.forEach(function(card) {
      var name = (card.getAttribute('data-name') || '').toLowerCase();
      var desc = (card.getAttribute('data-desc') || '').toLowerCase();
      var kw   = (card.getAttribute('data-keywords') || '').toLowerCase();
      var match = !query || name.includes(query) || desc.includes(query) || kw.includes(query);
      card.style.display = match ? '' : 'none';
      if (match) visible++;
    });
    var empty = document.getElementById('searchEmptyState');
    if (empty) empty.style.display = visible === 0 ? 'block' : 'none';
    if (searchResults) searchResults.style.display = 'none';
    if (query.length > 0) {
      document.getElementById('explore-tools').scrollIntoView({ behavior: 'smooth', block: 'start' });
    }
  }

  if (heroInput) {
    heroInput.addEventListener('input', function() {
      var q = this.value.trim();
      if (q.length < 2) {
        if (searchResults) searchResults.style.display = 'none';
        return;
      }
      var matches = [];
      cards.forEach(function(card) {
        var name = card.getAttribute('data-name') || '';
        if (name.toLowerCase().includes(q.toLowerCase())) {
          matches.push({
            name: name,
            url: card.getAttribute('data-url') || '#'
          });
        }
      });
      if (searchResults) {
        if (matches.length === 0) {
          searchResults.style.display = 'none';
        } else {
          searchResults.innerHTML = matches.slice(0, 6).map(function(m) {
            return '<a href="' + m.url + '"><i class="fa-solid fa-arrow-right"></i> ' + m.name + '</a>';
          }).join('');
          searchResults.style.display = 'block';
        }
      }
    });

    heroInput.addEventListener('keydown', function(e) {
      if (e.key === 'Enter') {
        filterTools(this.value);
      }
    });
  }

  var findBtn = document.querySelector('.tw-page .hero-search-btn');
  if (findBtn) {
    findBtn.addEventListener('click', function() {
      filterTools(heroInput ? heroInput.value : '');
    });
  }

  document.querySelectorAll('.tw-page .quick-tag-btn').forEach(function(btn) {
    btn.addEventListener('click', function() {
      var term = this.getAttribute('data-search') || this.textContent.trim();
      if (heroInput) heroInput.value = term;
      filterTools(term);
    });
  });

  // ---------- Recently Used (localStorage) ----------
  var RECENT_KEY = 'tw_recent_tools';
  var recentPills = document.getElementById('recentToolsPills');

  function saveRecent(name, slug) {
    if (!name || !slug) return;
    try {
      var recent = JSON.parse(localStorage.getItem(RECENT_KEY) || '[]');
      recent = recent.filter(function(r) { return r.slug !== slug; });
      recent.unshift({ name: name, slug: slug });
      recent = recent.slice(0, 5);
      localStorage.setItem(RECENT_KEY, JSON.stringify(recent));
      renderRecent();
    } catch(e) {}
  }

  function renderRecent() {
    if (!recentPills) return;
    try {
      var recent = JSON.parse(localStorage.getItem(RECENT_KEY) || '[]');
      if (!recent.length) {
        recentPills.innerHTML = '<span style="color:var(--text-muted);font-size:.85rem;">Your recently used tools will appear here.</span>';
        return;
      }
      recentPills.innerHTML = recent.map(function(r) {
        return '<button type="button" class="quick-tag-btn" data-recent-slug="' + r.slug + '">' +
               '<i class="fa-solid fa-clock-rotate-left"></i> ' + r.name + '</button>';
      }).join('');
      recentPills.querySelectorAll('[data-recent-slug]').forEach(function(b) {
        b.addEventListener('click', function() {
          var slug = this.getAttribute('data-recent-slug');
          window.location.href = '/tool/' + slug;
        });
      });
    } catch(e) {
      recentPills.innerHTML = '<span style="color:var(--text-muted);font-size:.85rem;">Your recently used tools will appear here.</span>';
    }
  }

  // Record click on tool cards for the recent list
  document.querySelectorAll('.tw-page .tool-card').forEach(function(card) {
    card.addEventListener('click', function() {
      var name = card.getAttribute('data-name');
      var slug = card.getAttribute('data-slug');
      if (name && slug) saveRecent(name, slug);
    });
  });

  renderRecent();
})();
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
