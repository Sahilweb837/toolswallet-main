 <?php
/**
 * ToolsWallet - YouTube Video & MP3 Downloader
 * Standalone Particular Page (NO SLUG)
 * FIXED: self-contained CSS — no missing stylesheet dependency
 */

require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/ads.php';
require_once __DIR__ . '/../includes/seo.php';

$slug = 'youtube-downloader';
$tool = Database::getTool($slug);

if (!$tool) {
    header("HTTP/1.0 404 Not Found");
    $pageSeo = ['title' => 'Tool Not Found | ToolsWallet'];
    require_once __DIR__ . '/../includes/header.php';
    ?>
    <div class="container" style="padding: 5rem 1rem; text-align: center; font-family: system-ui, sans-serif;">
        <h2>Tool Not Found</h2>
        <a href="<?= url() ?>" style="display:inline-block;margin-top:1.5rem;padding:0.9rem 1.6rem;background:#ff6b00;color:#fff;border-radius:12px;text-decoration:none;font-weight:700;">Explore All Tools</a>
    </div>
    <?php
    require_once __DIR__ . '/../includes/footer.php';
    exit;
}

$category = Database::getCategory($tool['category_slug'] ?? 'developer-tools') ?? [
    'name' => $tool['category_name'] ?? 'Utilities',
    'slug' => $tool['category_slug'] ?? 'developer-tools',
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
        ['name' => $tool['name'], 'url' => tool_url($tool['slug'])],
    ],
    'faqs' => $faqs,
];

require_once __DIR__ . '/../includes/header.php';
?>

<!-- ==================================================================
     SELF-CONTAINED CSS — everything scoped to .ytdl
     No dependency on header.php having loaded any stylesheet.
     ================================================================== -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=JetBrains+Mono:wght@400;500&display=swap" rel="stylesheet">

<style>
/* ---------- Reset ONLY inside this tool (doesn't touch your theme) ---------- */
.ytdl, .ytdl *, .ytdl *::before, .ytdl *::after { box-sizing: border-box; }
.ytdl button { font-family: inherit; }
.ytdl a { text-decoration: none; }
.ytdl img { max-width: 100%; display: block; }

/* ---------- Root variables (fallback in case header didn't set them) ---------- */
.ytdl {
  --ytdl-primary: #ff6b00;
  --ytdl-primary-hover: #ea580c;
  --ytdl-primary-dark: #c2410c;
  --ytdl-primary-light: rgba(255, 107, 0, 0.08);
  --ytdl-primary-border: rgba(255, 107, 0, 0.22);
  --ytdl-primary-glow: rgba(255, 107, 0, 0.35);

  --ytdl-bg: #ffffff;
  --ytdl-bg-soft: #fff7ed;
  --ytdl-text: #18181b;
  --ytdl-text-2: #52525b;
  --ytdl-muted: #a1a1aa;
  --ytdl-inverse: #ffffff;

  --ytdl-amber: #f59e0b;
  --ytdl-rose: #f43f5e;
  --ytdl-emerald: #10b981;
  --ytdl-blue: #0284c7;
  --ytdl-violet: #ff8533;

  --ytdl-border: #f1f1f4;
  --ytdl-border-2: #e4e4e7;

  --ytdl-shadow-sm: 0 1px 3px 0 rgba(0,0,0,0.04);
  --ytdl-shadow-md: 0 4px 8px -2px rgba(234,88,12,0.06), 0 2px 4px -2px rgba(0,0,0,0.04);
  --ytdl-shadow-lg: 0 12px 20px -4px rgba(234,88,12,0.08), 0 4px 6px -4px rgba(0,0,0,0.03);
  --ytdl-shadow-xl: 0 20px 25px -5px rgba(234,88,12,0.10), 0 8px 10px -6px rgba(0,0,0,0.04);
  --ytdl-glow: 0 0 25px -4px var(--ytdl-primary-glow);

  --ytdl-r-sm: 6px;
  --ytdl-r-md: 10px;
  --ytdl-r-lg: 16px;
  --ytdl-r-xl: 22px;
  --ytdl-r-full: 9999px;

  --ytdl-t-fast: 0.15s ease;
  --ytdl-t-norm: 0.25s cubic-bezier(0.4, 0, 0.2, 1);

  --ytdl-font: 'Plus Jakarta Sans', system-ui, -apple-system, 'Segoe UI', Roboto, sans-serif;
  --ytdl-mono: 'JetBrains Mono', Consolas, Monaco, monospace;

  font-family: var(--ytdl-font);
  color: var(--ytdl-text);
  line-height: 1.6;
  -webkit-font-smoothing: antialiased;
}

/* ---------- Page wrapper ---------- */
.ytdl { padding: 0 0 3rem; }
.ytdl .ytdl-container { max-width: 1180px; margin: 0 auto; padding: 0 1.5rem; }

/* ---------- Breadcrumbs ---------- */
.ytdl .crumbs {
  display: flex; align-items: center; flex-wrap: wrap; gap: 0.5rem;
  font-size: 0.875rem; color: var(--ytdl-text-2);
  margin: 1.5rem 0 2rem;
  padding: 0.7rem 1rem;
  background: var(--ytdl-bg-soft);
  border-radius: var(--ytdl-r-md);
  border: 1px solid var(--ytdl-primary-border);
  width: fit-content;
}
.ytdl .crumbs a { color: var(--ytdl-text-2); font-weight: 500; transition: color var(--ytdl-t-fast); }
.ytdl .crumbs a:hover { color: var(--ytdl-primary); }
.ytdl .crumbs .sep { color: var(--ytdl-muted); font-size: 0.75rem; }
.ytdl .crumbs .now { color: var(--ytdl-primary-dark); font-weight: 700; }

/* ---------- Header ---------- */
.ytdl .head {
  display: flex; align-items: flex-start; justify-content: space-between;
  flex-wrap: wrap; gap: 1.5rem; margin-bottom: 2.25rem;
}
.ytdl .head-left { display: flex; gap: 1.15rem; align-items: flex-start; flex: 1; min-width: 280px; }
.ytdl .head-icon {
  width: 64px; height: 64px;
  background: linear-gradient(145deg, var(--ytdl-primary), var(--ytdl-primary-hover));
  border-radius: var(--ytdl-r-lg);
  display: flex; align-items: center; justify-content: center;
  font-size: 1.9rem; color: var(--ytdl-inverse);
  box-shadow: var(--ytdl-shadow-md), var(--ytdl-glow);
  flex-shrink: 0; position: relative; overflow: hidden;
}
.ytdl .head-icon::after {
  content: ''; position: absolute; inset: 0;
  background: linear-gradient(145deg, rgba(255,255,255,0.25), transparent 55%);
  pointer-events: none;
}
.ytdl .head-title {
  font-size: 1.9rem; font-weight: 800;
  letter-spacing: -0.025em; line-height: 1.15; color: var(--ytdl-text);
  margin: 0;
}
.ytdl .head-tag { font-size: 1rem; color: var(--ytdl-text-2); margin-top: 0.4rem; max-width: 620px; }
.ytdl .head-badge {
  display: inline-block; font-size: 0.68rem; font-weight: 800;
  padding: 0.28rem 0.72rem; border-radius: var(--ytdl-r-full);
  letter-spacing: 0.05em; text-transform: uppercase;
  background: var(--ytdl-primary); color: var(--ytdl-inverse);
  box-shadow: var(--ytdl-glow); margin-left: 0.5rem; vertical-align: middle;
}
.ytdl .head-actions { display: flex; align-items: center; gap: 0.6rem; flex-wrap: wrap; }
.ytdl .rating {
  display: inline-flex; align-items: center; gap: 0.4rem;
  background: var(--ytdl-bg); padding: 0.55rem 0.9rem;
  border-radius: var(--ytdl-r-md); border: 1px solid var(--ytdl-border-2);
  font-weight: 700; font-size: 0.9rem; color: var(--ytdl-text);
  box-shadow: var(--ytdl-shadow-sm);
}
.ytdl .rating i { color: var(--ytdl-amber); font-size: 0.85rem; }
.ytdl .act-btn {
  background: var(--ytdl-bg); border: 1px solid var(--ytdl-border-2);
  border-radius: var(--ytdl-r-md); padding: 0.55rem 1.05rem;
  font-size: 0.87rem; font-weight: 600; color: var(--ytdl-text);
  display: inline-flex; align-items: center; gap: 0.45rem;
  cursor: pointer; transition: all var(--ytdl-t-fast);
  box-shadow: var(--ytdl-shadow-sm); font-family: inherit;
}
.ytdl .act-btn:hover {
  background: var(--ytdl-primary-light); border-color: var(--ytdl-primary);
  color: var(--ytdl-primary-dark); transform: translateY(-1px);
  box-shadow: var(--ytdl-shadow-md);
}

/* ---------- Workspace ---------- */
.ytdl .work {
  background: var(--ytdl-bg); border-radius: var(--ytdl-r-xl);
  padding: 2.25rem 2rem; box-shadow: var(--ytdl-shadow-lg);
  border: 1px solid var(--ytdl-border);
  margin-bottom: 2.5rem; position: relative; overflow: hidden;
}
.ytdl .work::before {
  content: ''; position: absolute; top: 0; left: 0; right: 0; height: 4px;
  background: linear-gradient(90deg, var(--ytdl-primary), var(--ytdl-amber), var(--ytdl-primary));
  background-size: 200% 100%; animation: ytdlShimmer 4s ease infinite;
}
@keyframes ytdlShimmer {
  0%,100% { background-position: 0% 50%; }
  50%     { background-position: 100% 50%; }
}
.ytdl .dl-badge {
  display: inline-flex; align-items: center; gap: 0.5rem;
  background: var(--ytdl-primary); color: var(--ytdl-inverse);
  padding: 6px 16px; font-weight: 700; border-radius: var(--ytdl-r-full);
  font-size: 0.82rem; box-shadow: var(--ytdl-shadow-md), var(--ytdl-glow);
}
.ytdl .pill {
  font-size: 0.76rem; font-weight: 700; padding: 5px 11px;
  border-radius: var(--ytdl-r-full);
  background: var(--ytdl-bg-soft);
  border: 1px solid var(--ytdl-primary-border);
  color: var(--ytdl-text-2);
  display: inline-flex; align-items: center; gap: 0.35rem;
}

/* ---------- Input ---------- */
.ytdl .inp {
  width: 100%; padding: 0.9rem 1.2rem; font-size: 1rem;
  font-family: inherit; border: 1.5px solid var(--ytdl-border-2);
  border-radius: var(--ytdl-r-lg); background: var(--ytdl-bg);
  color: var(--ytdl-text);
  transition: border-color var(--ytdl-t-fast), box-shadow var(--ytdl-t-fast);
}
.ytdl .inp::placeholder { color: var(--ytdl-muted); }
.ytdl .inp:hover { border-color: var(--ytdl-primary); }
.ytdl .inp:focus {
  outline: none; border-color: var(--ytdl-primary);
  box-shadow: 0 0 0 4px var(--ytdl-primary-light), 0 0 20px -4px var(--ytdl-primary-glow);
}

/* ---------- Buttons ---------- */
.ytdl .btn-main {
  background: linear-gradient(145deg, var(--ytdl-primary), var(--ytdl-primary-hover));
  color: var(--ytdl-inverse); border: none;
  padding: 0.9rem 1.7rem; border-radius: var(--ytdl-r-lg);
  font-weight: 700; font-size: 1rem; font-family: inherit;
  display: inline-flex; align-items: center; justify-content: center;
  gap: 0.55rem; cursor: pointer; transition: all var(--ytdl-t-fast);
  box-shadow: var(--ytdl-shadow-md), 0 4px 15px -4px var(--ytdl-primary-glow);
  white-space: nowrap; height: 54px; position: relative; overflow: hidden;
}
.ytdl .btn-main::after {
  content: ''; position: absolute; inset: 0;
  background: linear-gradient(145deg, rgba(255,255,255,0.2), transparent 55%);
  pointer-events: none;
}
.ytdl .btn-main:hover {
  background: linear-gradient(145deg, var(--ytdl-primary-hover), var(--ytdl-primary-dark));
  transform: translateY(-2px);
  box-shadow: var(--ytdl-shadow-lg), 0 8px 25px -4px var(--ytdl-primary-glow);
}
.ytdl .btn-main:active { transform: translateY(0); }

.ytdl .btn-sm {
  background: var(--ytdl-bg); border: 1.5px solid var(--ytdl-border-2);
  border-radius: var(--ytdl-r-md); padding: 0.42rem 0.75rem;
  font-size: 0.75rem; font-weight: 700; color: var(--ytdl-text-2);
  display: inline-flex; align-items: center; gap: 0.3rem;
  cursor: pointer; transition: all var(--ytdl-t-fast); font-family: inherit;
}
.ytdl .btn-sm:hover {
  background: var(--ytdl-primary-light);
  border-color: var(--ytdl-primary); color: var(--ytdl-primary-dark);
}

/* ---------- Result box ---------- */
.ytdl .result {
  margin-top: 1.75rem; padding: 2rem 1.5rem;
  background: var(--ytdl-bg-soft); border-radius: var(--ytdl-r-lg);
  border: 1.5px dashed var(--ytdl-primary-border);
  min-height: 150px;
  display: flex; flex-direction: column;
  align-items: center; justify-content: center;
  color: var(--ytdl-text-2); font-size: 0.95rem;
  text-align: center; transition: all var(--ytdl-t-norm);
}
.ytdl .result.has-content {
  border-style: solid; background: var(--ytdl-bg);
  align-items: stretch; text-align: left; padding: 1.5rem;
}
.ytdl .result .placeholder-icon {
  font-size: 2.6rem; color: var(--ytdl-primary);
  opacity: 0.35; margin-bottom: 0.8rem;
}

/* ---------- Download options ---------- */
.ytdl .dl-opt {
  display: flex; align-items: center; gap: 0.85rem;
  background: var(--ytdl-bg); padding: 0.9rem 1.1rem;
  border-radius: var(--ytdl-r-lg);
  border: 1.5px solid var(--ytdl-border-2);
  color: var(--ytdl-text); transition: all var(--ytdl-t-fast);
  box-shadow: var(--ytdl-shadow-sm); cursor: pointer;
}
.ytdl .dl-opt:hover {
  border-color: var(--ytdl-primary);
  background: var(--ytdl-primary-light);
  transform: translateX(3px); box-shadow: var(--ytdl-shadow-md);
}
.ytdl .dl-opt i.fa-download { color: var(--ytdl-primary); font-size: 1rem; }
.ytdl .dl-opt .opt-icon { font-size: 1.25rem; }
.ytdl .dl-opt .opt-title { font-weight: 700; font-size: 0.92rem; }
.ytdl .dl-opt .opt-sub { font-size: 0.72rem; color: var(--ytdl-muted); }

/* ---------- Guide cards ---------- */
.ytdl .guide-grid {
  display: grid; grid-template-columns: 1fr 1fr;
  gap: 1.5rem; margin: 2.25rem 0 2.75rem;
}
.ytdl .guide {
  background: var(--ytdl-bg); border-radius: var(--ytdl-r-xl);
  padding: 1.9rem; border: 1px solid var(--ytdl-border);
  box-shadow: var(--ytdl-shadow-sm);
  transition: all var(--ytdl-t-norm);
  position: relative; overflow: hidden;
}
.ytdl .guide::before {
  content: ''; position: absolute; top: 0; left: 0; width: 4px; height: 100%;
  background: linear-gradient(180deg, var(--ytdl-primary), var(--ytdl-amber));
  opacity: 0.85;
}
.ytdl .guide:hover {
  box-shadow: var(--ytdl-shadow-md);
  border-color: var(--ytdl-primary-border);
}
.ytdl .guide h3 {
  font-size: 1.25rem; font-weight: 800;
  display: flex; align-items: center; gap: 0.65rem;
  margin-bottom: 1.15rem; color: var(--ytdl-text);
  letter-spacing: -0.01em; margin-top: 0;
}
.ytdl .guide h3 i {
  color: var(--ytdl-primary); font-size: 1.15rem;
  width: 34px; height: 34px; background: var(--ytdl-primary-light);
  border-radius: var(--ytdl-r-md);
  display: inline-flex; align-items: center; justify-content: center;
}
.ytdl .guide ol { padding-left: 1.3rem; margin: 0; }
.ytdl .guide ol li {
  margin-bottom: 0.7rem; color: var(--ytdl-text-2);
  font-size: 0.96rem; line-height: 1.55; padding-left: 0.3rem;
}
.ytdl .guide ol li::marker { color: var(--ytdl-primary); font-weight: 800; }
.ytdl .guide p { color: var(--ytdl-text-2); font-size: 0.94rem; margin: 0 0 1rem; }
.ytdl .features { list-style: none; padding: 0; margin: 0; }
.ytdl .features li {
  display: flex; align-items: flex-start; gap: 0.65rem;
  margin-bottom: 0.8rem; color: var(--ytdl-text-2);
  font-size: 0.96rem; line-height: 1.55;
}
.ytdl .features li i {
  color: var(--ytdl-primary); font-size: 0.95rem;
  margin-top: 0.3rem; flex-shrink: 0;
}

/* ---------- FAQ ---------- */
.ytdl .faq-title {
  font-size: 1.4rem; font-weight: 800; margin-bottom: 1.4rem;
  color: var(--ytdl-text); display: flex; align-items: center; gap: 0.55rem;
}
.ytdl .faq-title i { color: var(--ytdl-primary); }
.ytdl .faq-wrap { margin: 2rem 0 3rem; }
.ytdl .faq-item {
  background: var(--ytdl-bg); border: 1px solid var(--ytdl-border);
  border-radius: var(--ytdl-r-lg); margin-bottom: 0.7rem;
  overflow: hidden; transition: all var(--ytdl-t-fast);
}
.ytdl .faq-item:hover { border-color: var(--ytdl-primary-border); }
.ytdl .faq-item.active {
  border-color: var(--ytdl-primary); box-shadow: var(--ytdl-shadow-md);
}
.ytdl .faq-q {
  width: 100%; text-align: left; background: transparent;
  border: none; padding: 1.1rem 1.4rem;
  font-size: 1rem; font-weight: 700; color: var(--ytdl-text);
  display: flex; align-items: center; justify-content: space-between; gap: 1rem;
  cursor: pointer; font-family: inherit;
  transition: background var(--ytdl-t-fast);
}
.ytdl .faq-q:hover { background: var(--ytdl-primary-light); }
.ytdl .faq-q i {
  color: var(--ytdl-muted); transition: all var(--ytdl-t-norm);
  font-size: 0.82rem; flex-shrink: 0;
}
.ytdl .faq-item.active .faq-q i {
  transform: rotate(180deg); color: var(--ytdl-primary);
}
.ytdl .faq-a {
  max-height: 0; overflow: hidden;
  transition: max-height var(--ytdl-t-norm), padding var(--ytdl-t-norm);
  padding: 0 1.4rem; color: var(--ytdl-text-2);
  background: var(--ytdl-bg-soft);
}
.ytdl .faq-item.active .faq-a {
  max-height: 260px; padding: 0 1.4rem 1.25rem;
}
.ytdl .faq-a p {
  font-size: 0.95rem; line-height: 1.65;
  padding-top: 0.2rem; margin: 0;
}

/* ---------- Related tools ---------- */
.ytdl .sec-head { margin-bottom: 1.5rem; }
.ytdl .sec-head h2 {
  font-size: 1.55rem; font-weight: 800;
  display: flex; align-items: center; gap: 0.6rem;
  letter-spacing: -0.02em; margin: 0;
}
.ytdl .sec-head h2 i { color: var(--ytdl-primary); font-size: 1.3rem; }
.ytdl .sec-head p {
  color: var(--ytdl-text-2); margin: 0.3rem 0 0; font-size: 0.95rem;
}
.ytdl .tools {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(230px, 1fr));
  gap: 1.25rem;
}
.ytdl .tcard {
  background: var(--ytdl-bg); border-radius: var(--ytdl-r-xl);
  padding: 1.5rem 1.4rem 1.7rem; border: 1px solid var(--ytdl-border);
  color: inherit; transition: all var(--ytdl-t-norm);
  box-shadow: var(--ytdl-shadow-sm);
  display: flex; flex-direction: column;
  position: relative; overflow: hidden;
}
.ytdl .tcard::before {
  content: ''; position: absolute; top: 0; left: 0; right: 0; height: 3px;
  background: linear-gradient(90deg, var(--ytdl-primary), var(--ytdl-amber));
  transform: scaleX(0); transform-origin: left;
  transition: transform var(--ytdl-t-norm);
}
.ytdl .tcard:hover {
  border-color: var(--ytdl-primary-border);
  transform: translateY(-4px); box-shadow: var(--ytdl-shadow-lg);
}
.ytdl .tcard:hover::before { transform: scaleX(1); }
.ytdl .tcard-top { display: flex; align-items: center; margin-bottom: 1rem; }
.ytdl .tcard-icon {
  width: 50px; height: 50px; background: var(--ytdl-primary-light);
  border-radius: var(--ytdl-r-lg);
  display: flex; align-items: center; justify-content: center;
  font-size: 1.4rem; color: var(--ytdl-primary);
  transition: all var(--ytdl-t-norm);
}
.ytdl .tcard:hover .tcard-icon {
  background: linear-gradient(145deg, var(--ytdl-primary), var(--ytdl-primary-hover));
  color: var(--ytdl-inverse); transform: scale(1.05) rotate(-3deg);
  box-shadow: var(--ytdl-glow);
}
.ytdl .tcard h3 {
  font-size: 1.1rem; font-weight: 700; margin: 0 0 0.35rem;
  color: var(--ytdl-text); letter-spacing: -0.01em;
}
.ytdl .tcard p {
  font-size: 0.87rem; color: var(--ytdl-text-2);
  line-height: 1.5; flex: 1; margin: 0;
}

/* ---------- Toast ---------- */
.ytdl-toast {
  position: fixed; bottom: 2rem; right: 2rem;
  background: #18181b; color: #fff;
  padding: 0.9rem 1.5rem; border-radius: 9999px;
  font-size: 0.9rem; font-weight: 600;
  box-shadow: 0 20px 25px -5px rgba(0,0,0,0.2);
  opacity: 0; transform: translateY(16px);
  transition: all 0.25s; pointer-events: none;
  z-index: 99999; font-family: 'Plus Jakarta Sans', system-ui, sans-serif;
}
.ytdl-toast.show { opacity: 1; transform: translateY(0); }

/* ---------- Responsive ---------- */
@media (max-width: 900px) {
  .ytdl .guide-grid { grid-template-columns: 1fr; }
}
@media (max-width: 768px) {
  .ytdl .ytdl-container { padding: 0 1rem; }
  .ytdl .head { flex-direction: column; }
  .ytdl .head-left { width: 100%; }
  .ytdl .head-title { font-size: 1.6rem; }
  .ytdl .head-actions { width: 100%; justify-content: flex-start; }
  .ytdl .work { padding: 1.75rem 1.15rem; }
  .ytdl .tools { grid-template-columns: repeat(auto-fill, minmax(200px, 1fr)); gap: 1rem; }
  .ytdl .guide { padding: 1.5rem; }
}
@media (max-width: 600px) {
  .ytdl .url-row { flex-direction: column !important; }
  .ytdl .url-row .btn-main { width: 100%; }
  .ytdl .url-row .btn-paste { display: none; }
  .ytdl .dl-grid { grid-template-columns: 1fr !important; }
}
@media (max-width: 480px) {
  .ytdl .head-left { flex-direction: column; align-items: flex-start; }
  .ytdl .tools { grid-template-columns: 1fr; }
  .ytdl .head-actions .act-btn { flex: 1; justify-content: center; }
  .ytdl .head-icon { width: 54px; height: 54px; font-size: 1.55rem; }
  .ytdl .head-title { font-size: 1.35rem; }
}
</style>

<!-- ==================================================================
     MARKUP
     ================================================================== -->
<div class="ytdl">
  <div class="ytdl-container">

    <!-- Breadcrumbs -->
    <nav class="crumbs" aria-label="Breadcrumb">
      <a href="<?= url() ?>"><i class="fa-solid fa-house"></i> Home</a>
      <span class="sep">/</span>
      <a href="<?= category_url($category['slug']) ?>"><?= e($category['name']) ?></a>
      <span class="sep">/</span>
      <span class="now"><?= e($tool['name']) ?></span>
    </nav>

    <!-- Header -->
    <div class="head">
      <div class="head-left">
        <div class="head-icon">
          <i class="<?= e(fa_class($tool['icon'])) ?>"></i>
        </div>
        <div>
          <div style="display: flex; align-items: center; gap: 0.6rem; flex-wrap: wrap;">
            <h1 class="head-title"><?= e($tool['name']) ?></h1>
            <?php if (!empty($tool['badge'])): ?>
              <span class="head-badge"><?= e($tool['badge']) ?></span>
            <?php endif; ?>
          </div>
          <p class="head-tag"><?= e($tool['short_description']) ?></p>
        </div>
      </div>

      <div class="head-actions">
        <span class="rating"><i class="fa-solid fa-star"></i> <?= e((string)($tool['rating'] ?? '4.9')) ?></span>
        <button type="button" class="act-btn" onclick="ytdlCopyUrl()">
          <i class="fa-solid fa-share-nodes"></i> Share
        </button>
        <button type="button" class="act-btn" onclick="ytdlToast('Added to favorites!')">
          <i class="fa-regular fa-bookmark"></i> Save
        </button>
      </div>
    </div>

    <!-- Workspace -->
    <div class="work">
      <div style="max-width: 860px; margin: 0 auto;">

        <div style="text-align: center; margin-bottom: 1.6rem;">
          <span class="dl-badge">
            <i class="fa-brands fa-youtube"></i> High-Speed YouTube Downloader
          </span>
          <h2 style="font-size: clamp(1.35rem, 2.5vw, 1.75rem); font-weight: 800; margin-top: 0.8rem; color: var(--ytdl-text); letter-spacing: -0.02em;">
            Download YouTube Videos in 1080p, 720p &amp; MP3
          </h2>
          <p style="color: var(--ytdl-text-2); font-size: 0.93rem; max-width: 620px; margin: 0.5rem auto 0;">
            Download YouTube videos, Shorts, and audio in MP4 &amp; MP3 formats in original high quality with fast direct links.
          </p>
        </div>

        <div style="display: flex; justify-content: center; gap: 0.5rem; margin-bottom: 1.5rem; flex-wrap: wrap;">
          <span class="pill"><i class="fa-solid fa-video" style="color: var(--ytdl-primary);"></i> 1080p Full HD</span>
          <span class="pill"><i class="fa-solid fa-play" style="color: var(--ytdl-blue);"></i> 720p HD</span>
          <span class="pill"><i class="fa-solid fa-music" style="color: var(--ytdl-emerald);"></i> 320 kbps MP3</span>
          <span class="pill"><i class="fa-solid fa-bolt" style="color: var(--ytdl-amber);"></i> YouTube Shorts</span>
          <span class="pill"><i class="fa-solid fa-image" style="color: var(--ytdl-violet);"></i> MaxRes Thumbnail</span>
        </div>

        <div style="margin-bottom: 0.6rem;">
          <div class="url-row" style="position: relative; display: flex; gap: 0.55rem; flex-wrap: wrap;">
            <div style="position: relative; flex: 1; min-width: 260px;">
              <i class="fa-brands fa-youtube" style="position: absolute; left: 1rem; top: 50%; transform: translateY(-50%); color: var(--ytdl-primary); font-size: 1.2rem; z-index: 2;"></i>
              <input type="url" id="ytdlInput" class="inp" style="padding-left: 2.75rem; padding-right: 5rem; height: 54px;" placeholder="Paste YouTube link (e.g. https://www.youtube.com/watch?v=... or shorts/...)" autofocus>
              <button type="button" id="ytdlPaste" class="btn-sm btn-paste" style="position: absolute; right: 0.5rem; top: 50%; transform: translateY(-50%); z-index: 2;">
                <i class="fa-regular fa-clipboard"></i> Paste
              </button>
            </div>
            <button type="button" id="ytdlFetch" class="btn-main">
              <i class="fa-solid fa-cloud-arrow-down"></i> Convert &amp; Download
            </button>
          </div>
          <div style="display: flex; justify-content: space-between; align-items: center; margin-top: 0.6rem; font-size: 0.78rem; color: var(--ytdl-muted); flex-wrap: wrap; gap: 0.5rem;">
            <span><i class="fa-solid fa-circle-info" style="margin-right: 0.25rem;"></i> Supports Watch URLs, Shorts, youtu.be, and mobile links</span>
            <button type="button" id="ytdlClear" style="background: none; border: none; color: var(--ytdl-muted); cursor: pointer; text-decoration: underline; font-size: 0.78rem; font-weight: 600; font-family: inherit;">Clear</button>
          </div>
        </div>

        <div id="ytdlResult" class="result">
          <i class="fa-regular fa-circle-play placeholder-icon"></i>
          <span>
            Paste a YouTube link above and click <strong style="color: var(--ytdl-primary-dark);">"Convert &amp; Download"</strong>.<br>
            Download options will appear here.
          </span>
        </div>
      </div>
    </div>

    <!-- Ad -->
    <?= render_ad_slot('tool_middle', 'responsive_banner') ?>

    <!-- Guide grid -->
    <div class="guide-grid">
      <div class="guide">
        <h3><i class="fa-solid fa-book-open"></i> How to use <?= e($tool['name']) ?></h3>
        <p><?= e($tool['description'] ?? $tool['short_description']) ?></p>
        <ol>
          <?php
          $steps = explode("\n", $tool['how_to_use'] ?? "1. Copy the YouTube video URL.\n2. Paste it into the input field above.\n3. Click the 'Convert & Download' button.\n4. Choose your format (MP4 video or MP3 audio) and quality.\n5. Click the download button next to your preferred option.");
          foreach ($steps as $step):
            if (!trim($step)) continue;
          ?>
            <li><?= e(preg_replace('/^\d+\.\s*/', '', trim($step))) ?></li>
          <?php endforeach; ?>
        </ol>
      </div>

      <div class="guide">
        <h3><i class="fa-solid fa-list-check"></i> Key Features</h3>
        <ul class="features">
          <?php
          $features = explode(";", $tool['features'] ?? "Download videos in 1080p, 720p, 480p, and 360p;Convert to MP3 audio (up to 320 kbps);Supports YouTube Shorts and regular videos;No registration, unlimited downloads;High-speed servers & direct download links");
          foreach ($features as $feat):
            if (!trim($feat)) continue;
          ?>
            <li><i class="fa-solid fa-circle-check"></i> <span><?= e(trim($feat)) ?></span></li>
          <?php endforeach; ?>
        </ul>
      </div>
    </div>

    <!-- FAQ -->
    <?php if (!empty($faqs)): ?>
      <div class="faq-wrap">
        <h3 class="faq-title"><i class="fa-solid fa-circle-question"></i> Questions about <?= e($tool['name']) ?></h3>
        <?php foreach ($faqs as $fIdx => $faq): ?>
          <div class="faq-item <?= $fIdx === 0 ? 'active' : '' ?>">
            <button type="button" class="faq-q">
              <span><?= e($faq['question']) ?></span>
              <i class="fa-solid fa-chevron-down"></i>
            </button>
            <div class="faq-a"><p><?= e($faq['answer']) ?></p></div>
          </div>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>

    <!-- Related tools -->
    <?php if (!empty($relatedTools)): ?>
      <div style="margin-bottom: 2rem;">
        <div class="sec-head">
          <h2><i class="fa-solid fa-shuffle"></i> Related <?= e($category['name']) ?></h2>
          <p>More utilities to supercharge your workflow.</p>
        </div>
        <div class="tools">
          <?php foreach ($relatedTools as $rel): ?>
            <a href="<?= tool_url($rel['slug']) ?>" class="tcard">
              <div class="tcard-top">
                <div class="tcard-icon"><i class="<?= e(fa_class($rel['icon'])) ?>"></i></div>
              </div>
              <h3><?= e($rel['name']) ?></h3>
              <p><?= e($rel['short_description']) ?></p>
            </a>
          <?php endforeach; ?>
        </div>
      </div>
    <?php endif; ?>

  </div>
</div>

<!-- Toast -->
<div id="ytdlToast" class="ytdl-toast">Message</div>

<script>
(function () {
  'use strict';

  /* ---------- Toast ---------- */
  function toast(msg) {
    var t = document.getElementById('ytdlToast');
    if (!t) return;
    t.textContent = msg;
    t.classList.add('show');
    clearTimeout(window.__ytdlToastTimer);
    window.__ytdlToastTimer = setTimeout(function () { t.classList.remove('show'); }, 2200);
  }
  window.ytdlToast = toast;

  window.ytdlCopyUrl = function () {
    if (navigator.clipboard) {
      navigator.clipboard.writeText(location.href).then(
        function () { toast('Tool URL copied to clipboard!'); },
        function () { toast('Copy failed — please copy manually.'); }
      );
    } else {
      toast('Tool URL: ' + location.href);
    }
  };

  /* ---------- FAQ ---------- */
  document.querySelectorAll('.ytdl .faq-q').forEach(function (btn) {
    btn.addEventListener('click', function () {
      btn.closest('.faq-item').classList.toggle('active');
    });
  });

  /* ---------- Downloader ---------- */
  var fetchBtn = document.getElementById('ytdlFetch');
  var input    = document.getElementById('ytdlInput');
  var result   = document.getElementById('ytdlResult');
  var pasteBtn = document.getElementById('ytdlPaste');
  var clearBtn = document.getElementById('ytdlClear');

  if (!fetchBtn || !input || !result) return;

  function extractId(url) {
    if (!url) return null;
    var patterns = [
      /(?:youtube\.com\/watch\?[^#]*?\bv=)([a-zA-Z0-9_-]{11})/,
      /(?:youtu\.be\/)([a-zA-Z0-9_-]{11})/,
      /(?:youtube\.com\/shorts\/)([a-zA-Z0-9_-]{11})/,
      /(?:youtube\.com\/embed\/)([a-zA-Z0-9_-]{11})/,
      /^([a-zA-Z0-9_-]{11})$/
    ];
    for (var i = 0; i < patterns.length; i++) {
      var m = url.match(patterns[i]);
      if (m) return m[1];
    }
    return null;
  }

  if (pasteBtn) {
    pasteBtn.addEventListener('click', async function () {
      try {
        var text = await navigator.clipboard.readText();
        input.value = text;
        toast('Pasted from clipboard');
      } catch (e) {
        toast('Unable to paste — please paste manually.');
      }
    });
  }

  if (clearBtn) {
    clearBtn.addEventListener('click', function () {
      input.value = '';
      result.classList.remove('has-content');
      result.innerHTML =
        '<i class="fa-regular fa-circle-play placeholder-icon"></i>' +
        '<span>Paste a YouTube link above and click <strong style="color: var(--ytdl-primary-dark);">"Convert &amp; Download"</strong>.<br>Download options will appear here.</span>';
      input.focus();
    });
  }

  fetchBtn.addEventListener('click', function () {
    var rawUrl = input.value.trim();
    if (!rawUrl) { toast('Please enter a YouTube URL'); return; }

    var vid = extractId(rawUrl);
    if (!vid) {
      toast('Invalid YouTube URL');
      result.classList.add('has-content');
      result.innerHTML =
        '<i class="fa-regular fa-circle-exclamation" style="font-size: 2.6rem; color: var(--ytdl-rose); margin-bottom: 0.8rem;"></i>' +
        '<span>Could not parse the YouTube URL.<br>Try: <code style="font-family: var(--ytdl-mono); font-size: 0.82rem; background: var(--ytdl-bg-soft); padding: 2px 6px; border-radius: 4px;">https://www.youtube.com/watch?v=dQw4w9WgXcQ</code></span>';
      return;
    }

    result.classList.add('has-content');
    result.innerHTML =
      '<i class="fa-solid fa-spinner fa-spin" style="font-size: 2.6rem; color: var(--ytdl-primary); margin-bottom: 0.8rem;"></i>' +
      '<span>Fetching download links for video ID <strong style="font-family: var(--ytdl-mono); color: var(--ytdl-primary-dark);">' + vid + '</strong>…</span>';

    setTimeout(function () {
      var thumbHi = 'https://img.youtube.com/vi/' + vid + '/maxresdefault.jpg';
      var thumbLo = 'https://img.youtube.com/vi/' + vid + '/hqdefault.jpg';
      var ytUrl   = 'https://www.youtube.com/watch?v=' + vid;

      result.innerHTML =
        '<div style="width: 100%; display: flex; flex-direction: column; gap: 1.15rem;">' +
          '<div style="display: flex; gap: 1.15rem; align-items: flex-start; flex-wrap: wrap; background: var(--ytdl-bg); padding: 1rem; border-radius: var(--ytdl-r-lg); border: 1px solid var(--ytdl-border); box-shadow: var(--ytdl-shadow-sm);">' +
            '<a href="' + ytUrl + '" target="_blank" rel="noopener noreferrer" style="flex-shrink: 0;">' +
              '<img src="' + thumbHi + '" alt="Video thumbnail" style="width: 170px; border-radius: var(--ytdl-r-md); object-fit: cover; display: block;" onerror="this.onerror=null; this.src=\'' + thumbLo + '\';">' +
            '</a>' +
            '<div style="flex: 1; min-width: 200px;">' +
              '<h4 style="font-size: 1.02rem; margin: 0 0 0.35rem; color: var(--ytdl-text); font-weight: 700;">' +
                'YouTube Video · <span style="font-family: var(--ytdl-mono); font-size: 0.88rem; color: var(--ytdl-primary-dark);">' + vid + '</span>' +
              '</h4>' +
              '<p style="font-size: 0.84rem; color: var(--ytdl-muted); margin: 0;">' +
                '<a href="' + ytUrl + '" target="_blank" rel="noopener noreferrer" style="color: var(--ytdl-primary-dark); font-weight: 600;">Open on YouTube <i class="fa-solid fa-arrow-up-right-from-square" style="font-size: 0.72rem;"></i></a>' +
              '</p>' +
              '<div style="display: flex; gap: 0.45rem; margin-top: 0.75rem; flex-wrap: wrap;">' +
                '<span style="background: var(--ytdl-primary-light); color: var(--ytdl-primary-dark); font-size: 0.68rem; font-weight: 800; padding: 0.24rem 0.62rem; border-radius: var(--ytdl-r-full); border: 1px solid var(--ytdl-primary-border);"><i class="fa-solid fa-video"></i> MP4</span>' +
                '<span style="background: rgba(16,185,129,0.1); color: var(--ytdl-emerald); font-size: 0.68rem; font-weight: 800; padding: 0.24rem 0.62rem; border-radius: var(--ytdl-r-full); border: 1px solid rgba(16,185,129,0.25);"><i class="fa-solid fa-music"></i> MP3</span>' +
              '</div>' +
            '</div>' +
          '</div>' +
          '<div class="dl-grid" style="display: grid; grid-template-columns: 1fr 1fr; gap: 0.7rem;">' +
            '<a class="dl-opt" href="' + ytUrl + '" target="_blank" rel="noopener noreferrer">' +
              '<i class="fa-solid fa-video opt-icon" style="color: var(--ytdl-primary);"></i>' +
              '<div style="flex: 1;"><div class="opt-title">MP4 · 1080p</div><div class="opt-sub">Full HD video</div></div>' +
              '<i class="fa-solid fa-download"></i>' +
            '</a>' +
            '<a class="dl-opt" href="' + ytUrl + '" target="_blank" rel="noopener noreferrer">' +
              '<i class="fa-solid fa-video opt-icon" style="color: var(--ytdl-blue);"></i>' +
              '<div style="flex: 1;"><div class="opt-title">MP4 · 720p</div><div class="opt-sub">HD video</div></div>' +
              '<i class="fa-solid fa-download"></i>' +
            '</a>' +
            '<a class="dl-opt" href="' + ytUrl + '" target="_blank" rel="noopener noreferrer">' +
              '<i class="fa-solid fa-music opt-icon" style="color: var(--ytdl-emerald);"></i>' +
              '<div style="flex: 1;"><div class="opt-title">MP3 · 320 kbps</div><div class="opt-sub">High quality audio</div></div>' +
              '<i class="fa-solid fa-download"></i>' +
            '</a>' +
            '<a class="dl-opt" href="' + ytUrl + '" target="_blank" rel="noopener noreferrer">' +
              '<i class="fa-solid fa-music opt-icon" style="color: var(--ytdl-violet);"></i>' +
              '<div style="flex: 1;"><div class="opt-title">MP3 · 128 kbps</div><div class="opt-sub">Standard audio</div></div>' +
              '<i class="fa-solid fa-download"></i>' +
            '</a>' +
          '</div>' +
          '<p style="font-size: 0.74rem; color: var(--ytdl-muted); text-align: center; margin: 0;">' +
            '<i class="fa-solid fa-circle-info"></i> Preview shown. Connect your backend API to enable real MP4/MP3 download links.' +
          '</p>' +
        '</div>';
      toast('Download options ready!');
    }, 700);
  });

  input.addEventListener('keypress', function (e) {
    if (e.key === 'Enter') { e.preventDefault(); fetchBtn.click(); }
  });

  /* ---------- Tool init (safe) ---------- */
  document.addEventListener('DOMContentLoaded', function () {
    if (typeof ToolsEngine !== 'undefined') ToolsEngine.init('<?= e($slug) ?>');
    if (typeof recordToolVisit !== 'undefined') {
      recordToolVisit('<?= addslashes($tool['name']) ?>', '<?= e($slug) ?>', '<?= e($tool['icon']) ?>', window.location.href);
    }
    fetch('<?= url("api/index.php?action=log_run&slug=" . urlencode($slug)) ?>').catch(function () {});
  });
})();
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>