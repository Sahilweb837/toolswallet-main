 <!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>YouTube Thumbnail Grabber · Base Design</title>
  <!-- Font Awesome 6 (Free) -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
  <style>
    /* ----- RESET & VARIABLES ----- */
    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
    }

    :root {
      --primary: #6366f1;
      --primary-dark: #4f52e0;
      --bg-body: #f9fafc;
      --bg-surface: #ffffff;
      --text-primary: #0b1b2b;
      --text-secondary: #4b5b6b;
      --border-subtle: #e2e8f0;
      --radius-md: 12px;
      --radius-lg: 20px;
      --shadow-sm: 0 8px 20px rgba(0, 0, 0, 0.02), 0 4px 12px rgba(0, 0, 0, 0.03);
      --shadow-card: 0 12px 32px -8px rgba(0, 0, 0, 0.08);
      --font-sans: 'Inter', system-ui, -apple-system, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
    }

    body {
      font-family: var(--font-sans);
      background: var(--bg-body);
      color: var(--text-primary);
      line-height: 1.5;
      padding: 2rem 1.5rem;
    }

    .container {
      max-width: 1280px;
      margin: 0 auto;
    }

    /* ----- BREADCRUMBS ----- */
    .breadcrumbs {
      display: flex;
      align-items: center;
      flex-wrap: wrap;
      gap: 0.5rem;
      font-size: 0.9rem;
      color: var(--text-secondary);
      margin-bottom: 2rem;
    }

    .breadcrumbs a {
      color: var(--text-secondary);
      text-decoration: none;
      transition: color 0.15s;
    }

    .breadcrumbs a:hover {
      color: var(--primary);
    }

    .breadcrumbs .separator {
      color: #b0bed0;
      font-size: 0.8rem;
    }

    .breadcrumbs .current {
      color: var(--text-primary);
      font-weight: 500;
    }

    /* ----- TOOL PAGE HEADER ----- */
    .tool-page-header {
      display: flex;
      align-items: flex-start;
      justify-content: space-between;
      flex-wrap: wrap;
      gap: 1.5rem;
      margin-bottom: 2.5rem;
    }

    .tool-header-left {
      display: flex;
      gap: 1.25rem;
      align-items: flex-start;
    }

    .tool-page-icon {
      width: 64px;
      height: 64px;
      background: linear-gradient(145deg, #ffffff, #f2f5ff);
      border-radius: 18px;
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 2rem;
      color: var(--primary);
      box-shadow: var(--shadow-sm);
      border: 1px solid var(--border-subtle);
      flex-shrink: 0;
    }

    .tool-page-title {
      font-size: 2rem;
      font-weight: 800;
      letter-spacing: -0.02em;
      line-height: 1.2;
      color: var(--text-primary);
    }

    .tool-page-tagline {
      font-size: 1.05rem;
      color: var(--text-secondary);
      margin-top: 0.4rem;
      max-width: 550px;
    }

    .tool-header-actions {
      display: flex;
      align-items: center;
      gap: 0.75rem;
      flex-wrap: wrap;
    }

    .tool-rating {
      display: flex;
      align-items: center;
      gap: 0.4rem;
      background: var(--bg-surface);
      padding: 0.5rem 0.9rem;
      border-radius: var(--radius-md);
      border: 1px solid var(--border-subtle);
      font-weight: 600;
      font-size: 0.95rem;
      color: #b45309;
    }

    .tool-rating i {
      color: #fbbf24;
    }

    .tool-action-btn {
      background: var(--bg-surface);
      border: 1px solid var(--border-subtle);
      border-radius: var(--radius-md);
      padding: 0.5rem 1.1rem;
      font-size: 0.9rem;
      font-weight: 500;
      color: var(--text-primary);
      display: inline-flex;
      align-items: center;
      gap: 0.5rem;
      cursor: pointer;
      transition: all 0.15s;
      font-family: inherit;
    }

    .tool-action-btn:hover {
      background: #f8faff;
      border-color: var(--primary);
      color: var(--primary);
    }

    /* ----- TOOL WORKSPACE CARD (main interactive area) ----- */
    .tool-workspace-card {
      background: var(--bg-surface);
      border-radius: var(--radius-lg);
      padding: 2.5rem 2rem;
      box-shadow: var(--shadow-card);
      border: 1px solid var(--border-subtle);
      margin-bottom: 3rem;
      transition: box-shadow 0.2s;
    }

    /* ----- FORM ELEMENTS (inside workspace) ----- */
    .form-group {
      margin-bottom: 1.5rem;
    }

    .form-label {
      display: block;
      font-weight: 600;
      margin-bottom: 0.6rem;
      font-size: 0.95rem;
      color: var(--text-primary);
    }

    .form-control {
      width: 100%;
      padding: 0.9rem 1.2rem;
      font-size: 1rem;
      border: 1px solid var(--border-subtle);
      border-radius: var(--radius-md);
      background: #ffffff;
      transition: border 0.15s, box-shadow 0.15s;
      font-family: inherit;
    }

    .form-control:focus {
      outline: none;
      border-color: var(--primary);
      box-shadow: 0 0 0 4px rgba(99, 102, 241, 0.12);
    }

    .btn-primary-gradient {
      background: linear-gradient(145deg, var(--primary), #7c82f5);
      color: white;
      border: none;
      padding: 0.9rem 1.8rem;
      border-radius: var(--radius-md);
      font-weight: 600;
      font-size: 1rem;
      display: inline-flex;
      align-items: center;
      gap: 0.6rem;
      cursor: pointer;
      transition: all 0.15s;
      box-shadow: 0 6px 14px rgba(99, 102, 241, 0.25);
      font-family: inherit;
    }

    .btn-primary-gradient:hover {
      background: linear-gradient(145deg, #5256e0, #6b71f0);
      transform: translateY(-1px);
      box-shadow: 0 10px 20px rgba(99, 102, 241, 0.3);
    }

    .btn-primary-gradient i {
      font-size: 1rem;
    }

    /* Thumbnail preview area (dynamic) */
    #ytResults {
      margin-top: 2rem;
      padding: 1.5rem;
      background: #f8faff;
      border-radius: var(--radius-lg);
      border: 1px dashed var(--border-subtle);
      min-height: 140px;
      display: flex;
      flex-direction: column;
      align-items: center;
      justify-content: center;
      color: var(--text-secondary);
      font-size: 0.95rem;
      transition: all 0.2s;
    }

    #ytResults i {
      font-size: 2.5rem;
      color: #cbd5e1;
      margin-bottom: 0.75rem;
    }

    /* Placeholder ad slot */
    .ad-placeholder {
      background: #eef2f6;
      border-radius: var(--radius-md);
      padding: 1rem 1.5rem;
      text-align: center;
      color: #8b9eb0;
      font-size: 0.85rem;
      letter-spacing: 0.3px;
      border: 1px dashed #cbd5e1;
      margin: 2rem 0;
    }

    /* ----- GUIDE CONTENT (How to / Features) ----- */
    .guide-content-grid {
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: 2rem;
      margin: 2.5rem 0 3rem;
    }

    .guide-card {
      background: var(--bg-surface);
      border-radius: var(--radius-lg);
      padding: 1.8rem 2rem;
      border: 1px solid var(--border-subtle);
      box-shadow: var(--shadow-sm);
    }

    .guide-card h3 {
      font-size: 1.35rem;
      font-weight: 700;
      display: flex;
      align-items: center;
      gap: 0.65rem;
      margin-bottom: 1.2rem;
      color: var(--text-primary);
    }

    .guide-card h3 i {
      color: var(--primary);
      font-size: 1.3rem;
    }

    .guide-card ol,
    .guide-card ul {
      padding-left: 1.2rem;
    }

    .guide-card ol li,
    .guide-card ul li {
      margin-bottom: 0.7rem;
      color: var(--text-secondary);
      font-size: 0.98rem;
    }

    .features-list {
      list-style: none;
      padding-left: 0 !important;
    }

    .features-list li {
      display: flex;
      align-items: flex-start;
      gap: 0.7rem;
      margin-bottom: 0.8rem;
      color: var(--text-secondary);
      font-size: 0.98rem;
    }

    .features-list li i {
      color: var(--primary);
      font-size: 1.1rem;
      margin-top: 0.15rem;
    }

    /* ----- FAQ ACCORDION ----- */
    .faq-section {
      margin: 2.5rem 0;
    }

    .faq-item {
      background: var(--bg-surface);
      border: 1px solid var(--border-subtle);
      border-radius: var(--radius-md);
      margin-bottom: 0.75rem;
      overflow: hidden;
      transition: border 0.15s;
    }

    .faq-item.active {
      border-color: var(--primary);
      box-shadow: 0 6px 16px rgba(99, 102, 241, 0.08);
    }

    .faq-question {
      width: 100%;
      text-align: left;
      background: transparent;
      border: none;
      padding: 1.1rem 1.5rem;
      font-size: 1.05rem;
      font-weight: 600;
      color: var(--text-primary);
      display: flex;
      align-items: center;
      justify-content: space-between;
      cursor: pointer;
      font-family: inherit;
      transition: background 0.15s;
    }

    .faq-question:hover {
      background: #fafcff;
    }

    .faq-question i {
      color: var(--text-secondary);
      transition: transform 0.2s;
      font-size: 0.9rem;
    }

    .faq-item.active .faq-question i {
      transform: rotate(180deg);
      color: var(--primary);
    }

    .faq-answer {
      max-height: 0;
      overflow: hidden;
      transition: max-height 0.3s ease, padding 0.2s ease;
      padding: 0 1.5rem;
      color: var(--text-secondary);
      background: #fbfdff;
    }

    .faq-item.active .faq-answer {
      max-height: 200px;
      padding: 0 1.5rem 1.4rem 1.5rem;
    }

    .faq-answer p {
      margin: 0;
      font-size: 0.98rem;
      line-height: 1.6;
    }

    /* ----- RELATED TOOLS GRID ----- */
    .section-header {
      margin-bottom: 1.5rem;
    }

    .section-title-wrap h2 {
      font-size: 1.75rem;
      font-weight: 800;
      display: flex;
      align-items: center;
      gap: 0.6rem;
    }

    .section-title-wrap h2 i {
      color: var(--primary);
    }

    .section-subtitle {
      color: var(--text-secondary);
      margin-top: 0.2rem;
      font-size: 1rem;
    }

    .tools-grid {
      display: grid;
      grid-template-columns: repeat(auto-fill, minmax(240px, 1fr));
      gap: 1.5rem;
    }

    .tool-card {
      background: var(--bg-surface);
      border-radius: var(--radius-lg);
      padding: 1.5rem 1.5rem 1.8rem;
      border: 1px solid var(--border-subtle);
      text-decoration: none;
      color: inherit;
      transition: all 0.2s ease;
      box-shadow: var(--shadow-sm);
      display: flex;
      flex-direction: column;
    }

    .tool-card:hover {
      border-color: var(--primary);
      transform: translateY(-3px);
      box-shadow: 0 20px 30px -12px rgba(0, 0, 0, 0.1);
    }

    .tool-card-top {
      display: flex;
      align-items: center;
      margin-bottom: 1.1rem;
    }

    .tool-icon-wrapper {
      width: 52px;
      height: 52px;
      background: #f2f5ff;
      border-radius: 14px;
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 1.6rem;
      color: var(--primary);
    }

    .tool-title {
      font-size: 1.2rem;
      font-weight: 700;
      margin-bottom: 0.4rem;
      color: var(--text-primary);
    }

    .tool-desc {
      font-size: 0.9rem;
      color: var(--text-secondary);
      line-height: 1.5;
      flex: 1;
    }

    /* ----- TOAST (utility) ----- */
    .toast-message {
      position: fixed;
      bottom: 2rem;
      right: 2rem;
      background: #1e293b;
      color: white;
      padding: 0.9rem 1.6rem;
      border-radius: 40px;
      font-size: 0.9rem;
      font-weight: 500;
      box-shadow: 0 12px 30px rgba(0, 0, 0, 0.2);
      opacity: 0;
      transform: translateY(16px);
      transition: all 0.2s;
      pointer-events: none;
      z-index: 999;
    }

    .toast-message.show {
      opacity: 1;
      transform: translateY(0);
    }

    /* ----- RESPONSIVE TWEAKS ----- */
    @media (max-width: 768px) {
      body { padding: 1.5rem 1rem; }
      .tool-page-header { flex-direction: column; }
      .tool-header-left { width: 100%; }
      .tool-page-title { font-size: 1.6rem; }
      .tool-header-actions { width: 100%; justify-content: flex-start; }
      .tool-workspace-card { padding: 1.8rem 1.2rem; }
      .guide-content-grid { grid-template-columns: 1fr; }
      .tools-grid { grid-template-columns: 1fr 1fr; }
    }

    @media (max-width: 480px) {
      .tool-header-left { flex-direction: column; align-items: flex-start; }
      .tools-grid { grid-template-columns: 1fr; }
      .btn-primary-gradient { width: 100%; justify-content: center; }
      .tool-header-actions .tool-action-btn { flex: 1; justify-content: center; }
    }

    /* Simulated icon classes – using Font Awesome */
    .fa-solid, .fa-regular { font-family: "Font Awesome 6 Free"; }
    /* ensure fa-class fallback */
    .tool-page-icon i, .tool-icon-wrapper i { font-style: normal; }
  </style>
</head>
<body>
  <div class="container">
    <!-- Breadcrumbs (static) -->
    <nav class="breadcrumbs" aria-label="Breadcrumb">
      <a href="#"><i class="fa-solid fa-house"></i> Home</a>
      <span class="separator">/</span>
      <a href="#">Developer Tools</a>
      <span class="separator">/</span>
      <span class="current">YouTube Thumbnail Grabber</span>
    </nav>

    <!-- Tool Page Header -->
    <div class="tool-page-header">
      <div class="tool-header-left">
        <div class="tool-page-icon">
          <i class="fa-brands fa-youtube"></i>
        </div>
        <div>
          <div style="display: flex; align-items: center; gap: 0.6rem; flex-wrap: wrap;">
            <h1 class="tool-page-title">YouTube Thumbnail Grabber</h1>
            <span style="background: #fef3c7; color: #92400e; font-size: 0.7rem; font-weight: 700; padding: 0.2rem 0.7rem; border-radius: 20px; letter-spacing: 0.3px;">POPULAR</span>
          </div>
          <p class="tool-page-tagline">Download high-quality YouTube thumbnails in one click. Free, no registration.</p>
        </div>
      </div>

      <div class="tool-header-actions">
        <span class="tool-rating">
          <i class="fa-solid fa-star"></i> 4.9
        </span>
        <button type="button" class="tool-action-btn" onclick="showToast('Tool URL copied!')">
          <i class="fa-solid fa-share-nodes"></i> Share
        </button>
        <button type="button" class="tool-action-btn" onclick="showToast('Added to favorites!')">
          <i class="fa-regular fa-bookmark"></i> Save
        </button>
      </div>
    </div>

    <!-- ====== TOOL INTERACTIVE WORKSPACE ====== -->
    <div class="tool-workspace-card">
      <div style="max-width: 680px; margin: 0 auto;">
        <div class="form-group">
          <label class="form-label">Enter YouTube Video Link or Shorts URL:</label>
          <div style="display: flex; gap: 0.5rem; flex-wrap: wrap;">
            <input type="url" id="ytUrlInput" class="form-control" style="flex: 1; min-width: 200px;" placeholder="https://www.youtube.com/watch?v=dQw4w9WgXcQ">
            <button type="button" id="btnFetchThumbnail" class="btn-primary-gradient" style="white-space: nowrap;">
              <i class="fa-solid fa-image"></i> Fetch Thumbnail
            </button>
          </div>
        </div>

        <!-- Thumbnail result / placeholder -->
        <div id="ytResults">
          <i class="fa-regular fa-image"></i>
          <span style="text-align: center;">Paste a YouTube link above and click “Fetch Thumbnail”.<br>Preview will appear here.</span>
        </div>
      </div>
    </div>

    <!-- In-Content Ad Placeholder (static) -->
    <div class="ad-placeholder">
      <i class="fa-regular fa-rectangle-ad" style="margin-right: 8px;"></i> Advertisement · Responsive Banner
    </div>

    <!-- ====== GUIDE CONTENT (How To & Features) ====== -->
    <div class="guide-content-grid">
      <!-- How to Use Card -->
      <div class="guide-card">
        <h3><i class="fa-solid fa-book-open"></i> How to use YouTube Thumbnail Grabber</h3>
        <p style="color: var(--text-secondary); margin-bottom: 1rem;">Extract thumbnail images from any YouTube video or Shorts in seconds.</p>
        <ol>
          <li>Copy the YouTube video URL from your browser or the share button.</li>
          <li>Paste the link into the input field above.</li>
          <li>Click the “Fetch Thumbnail” button.</li>
          <li>Preview the thumbnail and download it in high resolution.</li>
        </ol>
      </div>

      <!-- Key Features Card -->
      <div class="guide-card">
        <h3><i class="fa-solid fa-list-check"></i> Key Features</h3>
        <ul class="features-list">
          <li><i class="fa-solid fa-circle-check"></i> <span>100% free, no registration required</span></li>
          <li><i class="fa-solid fa-circle-check"></i> <span>Supports YouTube videos and Shorts</span></li>
          <li><i class="fa-solid fa-circle-check"></i> <span>High-quality maxresdefault thumbnails</span></li>
          <li><i class="fa-solid fa-circle-check"></i> <span>Instant preview and direct download</span></li>
          <li><i class="fa-solid fa-circle-check"></i> <span>Privacy-friendly – no data stored</span></li>
        </ul>
      </div>
    </div>

    <!-- ====== FAQ ACCORDION ====== -->
    <div style="margin-bottom: 3.5rem;">
      <h3 style="font-size: 1.5rem; font-weight: 800; margin-bottom: 1.5rem; color: var(--text-primary);">
        <i class="fa-solid fa-circle-question" style="color: var(--primary);"></i> Questions about YouTube Thumbnail Grabber
      </h3>
      <div class="faq-section">
        <div class="faq-item active">
          <button type="button" class="faq-question">
            <span>Is this tool completely free to use?</span>
            <i class="fa-solid fa-chevron-down"></i>
          </button>
          <div class="faq-answer">
            <p>Yes! Our YouTube Thumbnail Grabber is 100% free and requires no registration or sign-up. You can extract as many thumbnails as you need.</p>
          </div>
        </div>
        <div class="faq-item">
          <button type="button" class="faq-question">
            <span>Can I download thumbnails from YouTube Shorts?</span>
            <i class="fa-solid fa-chevron-down"></i>
          </button>
          <div class="faq-answer">
            <p>Absolutely. Just paste the Shorts URL (e.g., youtube.com/shorts/VIDEO_ID) into the input field and click Fetch Thumbnail.</p>
          </div>
        </div>
        <div class="faq-item">
          <button type="button" class="faq-question">
            <span>What thumbnail resolutions are available?</span>
            <i class="fa-solid fa-chevron-down"></i>
          </button>
          <div class="faq-answer">
            <p>We provide the maximum resolution available (usually 1280×720 or higher). You can also see lower-res versions if available.</p>
          </div>
        </div>
      </div>
    </div>

    <!-- ====== RELATED TOOLS ====== -->
    <div style="margin-bottom: 2rem;">
      <div class="section-header">
        <div class="section-title-wrap">
          <h2><i class="fa-solid fa-shuffle"></i> Related Developer Tools</h2>
          <p class="section-subtitle">More utilities to supercharge your workflow.</p>
        </div>
      </div>
      <div class="tools-grid">
        <!-- Tool 1 -->
        <a href="#" class="tool-card">
          <div class="tool-card-top">
            <div class="tool-icon-wrapper"><i class="fa-solid fa-code"></i></div>
          </div>
          <h3 class="tool-title">JSON Formatter</h3>
          <p class="tool-desc">Beautify, minify and validate JSON instantly.</p>
        </a>
        <!-- Tool 2 -->
        <a href="#" class="tool-card">
          <div class="tool-card-top">
            <div class="tool-icon-wrapper"><i class="fa-solid fa-qrcode"></i></div>
          </div>
          <h3 class="tool-title">QR Code Generator</h3>
          <p class="tool-desc">Create custom QR codes for links, text and more.</p>
        </a>
        <!-- Tool 3 -->
        <a href="#" class="tool-card">
          <div class="tool-card-top">
            <div class="tool-icon-wrapper"><i class="fa-solid fa-image"></i></div>
          </div>
          <h3 class="tool-title">Image Compressor</h3>
          <p class="tool-desc">Reduce image file size without losing quality.</p>
        </a>
        <!-- Tool 4 -->
        <a href="#" class="tool-card">
          <div class="tool-card-top">
            <div class="tool-icon-wrapper"><i class="fa-solid fa-key"></i></div>
          </div>
          <h3 class="tool-title">Password Generator</h3>
          <p class="tool-desc">Generate strong, random passwords in seconds.</p>
        </a>
      </div>
    </div>
  </div>

  <!-- Toast notification container (hidden by default) -->
  <div id="toast" class="toast-message">Message</div>

  <script>
    // ---------- SIMPLE TOAST ----------
    function showToast(message) {
      const toast = document.getElementById('toast');
      if (!toast) return;
      toast.textContent = message;
      toast.classList.add('show');
      setTimeout(() => toast.classList.remove('show'), 2200);
    }

    // ---------- FAQ ACCORDION ----------
    document.querySelectorAll('.faq-question').forEach(button => {
      button.addEventListener('click', function() {
        const item = this.closest('.faq-item');
        // optional: close others (uncomment next 3 lines for exclusive accordion)
        // document.querySelectorAll('.faq-item').forEach(i => {
        //   if (i !== item) i.classList.remove('active');
        // });
        item.classList.toggle('active');
      });
    });

    // ---------- YOUTUBE THUMBNAIL FETCH (DEMO / SIMULATION) ----------
    // This is a front-end simulation to match the design. In production you'd extract the ID.
    const fetchBtn = document.getElementById('btnFetchThumbnail');
    const urlInput = document.getElementById('ytUrlInput');
    const resultsDiv = document.getElementById('ytResults');

    function extractVideoId(url) {
      // Basic regex for YouTube video ID (works for watch?v=, youtu.be/, shorts/)
      const patterns = [
        /(?:youtube\.com\/watch\?v=|youtu\.be\/|youtube\.com\/shorts\/)([a-zA-Z0-9_-]{11})/,
        /^([a-zA-Z0-9_-]{11})$/  // if only ID is pasted
      ];
      for (let p of patterns) {
        const match = url.match(p);
        if (match) return match[1];
      }
      return null;
    }

    fetchBtn.addEventListener('click', function() {
      const rawUrl = urlInput.value.trim();
      if (!rawUrl) {
        showToast('Please enter a YouTube URL');
        resultsDiv.innerHTML = `<i class="fa-regular fa-circle-exclamation"></i><span style="text-align: center;">Please enter a valid YouTube link.</span>`;
        return;
      }

      const videoId = extractVideoId(rawUrl);
      if (!videoId) {
        showToast('Could not extract video ID. Check the URL.');
        resultsDiv.innerHTML = `<i class="fa-regular fa-circle-exclamation"></i><span style="text-align: center;">Invalid YouTube URL. Try something like:<br><code>https://www.youtube.com/watch?v=dQw4w9WgXcQ</code></span>`;
        return;
      }

      // Simulate loading / then show thumbnail preview
      resultsDiv.innerHTML = `<i class="fa-solid fa-spinner fa-spin"></i><span style="text-align: center;">Fetching thumbnail…</span>`;

      // Simulate network delay (remove in production)
      setTimeout(() => {
        // Build maxresdefault thumbnail URL (will fallback to hqdefault if needed, but we show maxres in img with onerror fallback)
        const thumbUrl = `https://img.youtube.com/vi/${videoId}/maxresdefault.jpg`;
        const fallbackUrl = `https://img.youtube.com/vi/${videoId}/hqdefault.jpg`;

        resultsDiv.innerHTML = `
          <div style="display: flex; flex-direction: column; align-items: center; width: 100%; gap: 1rem;">
            <div style="display: flex; align-items: center; gap: 0.5rem; color: var(--text-primary); font-weight: 500;">
              <i class="fa-brands fa-youtube" style="color: #ff0000;"></i> Video ID: ${videoId}
            </div>
            <img src="${thumbUrl}" alt="YouTube Thumbnail" 
                 style="max-width: 100%; border-radius: 12px; box-shadow: 0 8px 20px rgba(0,0,0,0.1); border: 1px solid var(--border-subtle);"
                 onerror="this.onerror=null; this.src='${fallbackUrl}';">
            <div style="display: flex; gap: 1rem; flex-wrap: wrap; justify-content: center; margin-top: 0.5rem;">
              <a href="${thumbUrl}" target="_blank" download class="tool-action-btn" style="text-decoration: none; background: var(--primary); color: white; border: none;">
                <i class="fa-solid fa-download"></i> Download HD
              </a>
              <a href="${fallbackUrl}" target="_blank" download class="tool-action-btn" style="text-decoration: none;">
                <i class="fa-solid fa-download"></i> Download SQ
              </a>
            </div>
          </div>
        `;
        showToast('Thumbnail loaded!');
      }, 600);
    });

    // Copy link handler for share button (already calls showToast)
    // but also to copy URL
    document.querySelector('.tool-action-btn[onclick*="Tool URL copied"]')?.addEventListener('click', function(e) {
      // The onclick already calls showToast, we also copy URL
      navigator.clipboard?.writeText(window.location.href).catch(() => {});
    });

    // bonus: pressing Enter in input triggers fetch
    urlInput.addEventListener('keypress', function(e) {
      if (e.key === 'Enter') {
        e.preventDefault();
        fetchBtn.click();
      }
    });
  </script>
</body>
</html>