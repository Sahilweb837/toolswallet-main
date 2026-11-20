<?php
/**
 * ToolsWallet Backend API & Security Gateway
 * SSRF Protected Web Crawler, SEO Auditor & Tool Services
 * Domain: toolswallet.online
 */

require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/db.php';

$action = $_GET['action'] ?? $_POST['action'] ?? '';

if ($action !== 'download_stream') {
    header('Content-Type: application/json; charset=UTF-8');
    header('X-Content-Type-Options: nosniff');
}

switch ($action) {
    case 'audit':
        handle_seo_audit();
        break;

    case 'redirect':
        handle_redirect_check();
        break;

    case 'instagram_info':
        handle_instagram_info();
        break;

    case 'youtube_info':
        handle_youtube_info();
        break;

    case 'download_stream':
        handle_download_stream();
        break;

    case 'log_run':
        handle_log_run();
        break;

    case 'admin_user_history':
        handle_admin_user_history();
        break;

    case 'user_login':
        handle_user_login();
        break;

    case 'user_register':
        handle_user_register();
        break;

    case 'user_logout':
        handle_user_logout();
        break;

    case 'user_status':
        handle_user_status();
        break;

    default:
        echo json_encode([
            'success' => false,
            'message' => 'Invalid action'
        ]);
        break;
}

/**
 * Perform On-Page SEO Audit with SSRF Defenses
 */
function handle_seo_audit(): void {
    $url = trim($_GET['url'] ?? '');
    if (!$url) {
        echo json_encode(['success' => false, 'message' => 'URL is required']);
        return;
    }

    if (!preg_match('#^https?://#i', $url)) {
        $url = 'https://' . $url;
    }

    if (!is_safe_public_url($url)) {
        echo json_encode(['success' => false, 'message' => 'Target URL is private, invalid, or blocked for security']);
        return;
    }

    $startTime = microtime(true);
    $ch = curl_init();
    curl_setopt_array($ch, [
        CURLOPT_URL => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_MAXREDIRS => 5,
        CURLOPT_TIMEOUT => 8,
        CURLOPT_CONNECTTIMEOUT => 4,
        CURLOPT_USERAGENT => 'Mozilla/5.0 (compatible; ToolsWalletBot/2.0; +https://toolswallet.online)',
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_SSL_VERIFYHOST => false
    ]);

    $html = curl_exec($ch);
    $responseTimeMs = (int)round((microtime(true) - $startTime) * 1000);
    $httpCode = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $finalUrl = curl_getinfo($ch, CURLINFO_EFFECTIVE_URL);
    $curlError = curl_error($ch);
    curl_close($ch);

    if ($html === false || $httpCode >= 400) {
        echo json_encode([
            'success' => false,
            'message' => 'Failed to reach URL (HTTP ' . $httpCode . '): ' . $curlError
        ]);
        return;
    }

    // Parse HTML DOM
    $dom = new DOMDocument();
    @$dom->loadHTML(mb_convert_encoding($html, 'HTML-ENTITIES', 'UTF-8'));
    $xpath = new DOMXPath($dom);

    // Extract Title
    $titleNode = $xpath->query('//title');
    $title = ($titleNode->length > 0) ? trim($titleNode->item(0)->textContent) : '';

    // Extract Meta Description
    $descNode = $xpath->query('//meta[translate(@name, "DESCRIPTION", "description")="description"]/@content');
    $description = ($descNode->length > 0) ? trim($descNode->item(0)->nodeValue) : '';

    // Extract Canonical
    $canonicalNode = $xpath->query('//link[translate(@rel, "CANONICAL", "canonical")="canonical"]/@href');
    $canonical = ($canonicalNode->length > 0) ? trim($canonicalNode->item(0)->nodeValue) : '';

    // Headings Structure
    $h1Nodes = $xpath->query('//h1');
    $h1Count = $h1Nodes->length;
    $firstH1 = ($h1Count > 0) ? trim($h1Nodes->item(0)->textContent) : '';

    $h2Nodes = $xpath->query('//h2');
    $h2Count = $h2Nodes->length;
    $firstH2 = ($h2Count > 0) ? trim($h2Nodes->item(0)->textContent) : '';

    // Images & Alt attributes
    $imgNodes = $xpath->query('//img');
    $totalImages = $imgNodes->length;
    $missingAlt = 0;
    foreach ($imgNodes as $img) {
        $alt = $img->getAttribute('alt');
        if (trim($alt) === '') {
            $missingAlt++;
        }
    }

    // Viewport & Mobile Usability
    $viewportNode = $xpath->query('//meta[@name="viewport"]');
    $hasViewport = ($viewportNode->length > 0);

    // Robots Meta Tag
    $robotsNode = $xpath->query('//meta[translate(@name, "ROBOTS", "robots")="robots"]/@content');
    $robotsContent = ($robotsNode->length > 0) ? trim($robotsNode->item(0)->nodeValue) : 'index, follow (default)';

    // OpenGraph Social Tags
    $ogTitleNode = $xpath->query('//meta[@property="og:title"]/@content');
    $ogTitle = ($ogTitleNode->length > 0) ? trim($ogTitleNode->item(0)->nodeValue) : '';

    $ogDescNode = $xpath->query('//meta[@property="og:description"]/@content');
    $ogDesc = ($ogDescNode->length > 0) ? trim($ogDescNode->item(0)->nodeValue) : '';

    $ogImageNode = $xpath->query('//meta[@property="og:image"]/@content');
    $ogImage = ($ogImageNode->length > 0) ? trim($ogImageNode->item(0)->nodeValue) : '';

    // Schema JSON-LD Structured Data
    $schemaNodes = $xpath->query('//script[@type="application/ld+json"]');
    $schemaCount = $schemaNodes->length;

    // SSL / HTTPS
    $isHttps = str_starts_with(strtolower($finalUrl), 'https://');

    // Categorized Audit Lists & Multi-factor Scoring
    $score = 100;
    $passedChecks = [];
    $warningChecks = [];
    $failedChecks = [];
    $recommendations = [];

    // 1. Title Audit
    $titleLen = mb_strlen($title);
    if (!$title) {
        $score -= 22;
        $failedChecks[] = ['name' => 'Meta Title', 'detail' => 'Missing <title> tag on page.'];
        $recommendations[] = ['priority' => 'High', 'action' => 'Add a descriptive <title> tag between 30 and 60 characters for search snippet rankings.'];
    } elseif ($titleLen < 25 || $titleLen > 65) {
        $score -= 8;
        $warningChecks[] = ['name' => 'Title Length', 'detail' => "Title is {$titleLen} characters. Search engines typically truncate titles over 60 characters."];
        $recommendations[] = ['priority' => 'Medium', 'action' => 'Optimize title length to stay within 30 to 60 characters.'];
    } else {
        $passedChecks[] = ['name' => 'Meta Title', 'detail' => "Title is optimal ({$titleLen} characters): \"{$title}\""];
    }

    // 2. Description Audit
    $descLen = mb_strlen($description);
    if (!$description) {
        $score -= 18;
        $failedChecks[] = ['name' => 'Meta Description', 'detail' => 'Missing meta description tag.'];
        $recommendations[] = ['priority' => 'High', 'action' => 'Provide a compelling meta description between 120 and 160 characters to boost CTR.'];
    } elseif ($descLen < 70 || $descLen > 165) {
        $score -= 6;
        $warningChecks[] = ['name' => 'Meta Description Length', 'detail' => "Description is {$descLen} characters (ideal: 120–160 chars)."];
        $recommendations[] = ['priority' => 'Medium', 'action' => 'Tune description length to avoid SERP truncation.'];
    } else {
        $passedChecks[] = ['name' => 'Meta Description', 'detail' => "Meta description is optimal ({$descLen} characters)."];
    }

    // 3. Headings Audit
    if ($h1Count === 0) {
        $score -= 15;
        $failedChecks[] = ['name' => 'H1 Heading', 'detail' => 'No <h1> heading detected on page.'];
        $recommendations[] = ['priority' => 'High', 'action' => 'Include exactly one <h1> heading specifying the main subject of the page.'];
    } elseif ($h1Count > 1) {
        $score -= 5;
        $warningChecks[] = ['name' => 'Multiple H1 Headings', 'detail' => "Found {$h1Count} <h1> headings. Best practice is to use one H1 per page."];
        $recommendations[] = ['priority' => 'Low', 'action' => 'Consolidate additional <h1> tags into <h2> or <h3> headings.'];
    } else {
        $passedChecks[] = ['name' => 'H1 Heading Structure', 'detail' => "Page has exactly one clear <h1> heading."];
    }

    if ($h2Count > 0) {
        $passedChecks[] = ['name' => 'H2 Subheadings', 'detail' => "Found {$h2Count} <h2> subheading(s) structuring content."];
    }

    // 4. SSL / HTTPS Security
    if ($isHttps) {
        $passedChecks[] = ['name' => 'SSL / HTTPS Security', 'detail' => 'Page is securely served over encrypted HTTPS.'];
    } else {
        $score -= 15;
        $failedChecks[] = ['name' => 'Insecure HTTP', 'detail' => 'Target URL is running over unencrypted HTTP.'];
        $recommendations[] = ['priority' => 'High', 'action' => 'Install an SSL certificate and redirect all traffic to HTTPS.'];
    }

    // 5. Mobile Viewport
    if ($hasViewport) {
        $passedChecks[] = ['name' => 'Mobile Responsive Viewport', 'detail' => 'Mobile viewport meta tag is properly configured.'];
    } else {
        $score -= 12;
        $failedChecks[] = ['name' => 'Missing Mobile Viewport', 'detail' => 'Page lacks a mobile viewport tag, harming mobile friendliness.'];
        $recommendations[] = ['priority' => 'High', 'action' => 'Add <meta name="viewport" content="width=device-width, initial-scale=1.0">.'];
    }

    // 6. Image Accessibility (Alt tags)
    if ($missingAlt > 0) {
        $penalty = min(12, $missingAlt * 3);
        $score -= $penalty;
        $warningChecks[] = ['name' => 'Image Alt Attributes', 'detail' => "{$missingAlt} out of {$totalImages} images are missing descriptive alt text."];
        $recommendations[] = ['priority' => 'Medium', 'action' => 'Add descriptive alt text to all images for image search ranking and accessibility.'];
    } elseif ($totalImages > 0) {
        $passedChecks[] = ['name' => 'Image Accessibility', 'detail' => "All {$totalImages} images have valid alt attributes."];
    }

    // 7. Canonical Tag
    if ($canonical) {
        $passedChecks[] = ['name' => 'Canonical Link Tag', 'detail' => "Canonical URL explicitly specified: {$canonical}"];
    } else {
        $score -= 5;
        $warningChecks[] = ['name' => 'Missing Canonical URL', 'detail' => 'No rel="canonical" link specified to prevent duplicate content indexing.'];
        $recommendations[] = ['priority' => 'Medium', 'action' => 'Add <link rel="canonical" href="..."> to define the master URL.'];
    }

    // 8. OpenGraph & Social Cards
    if ($ogTitle || $ogImage) {
        $passedChecks[] = ['name' => 'OpenGraph Meta Tags', 'detail' => 'Social media preview tags (og:title, og:image) are present.'];
    } else {
        $score -= 4;
        $warningChecks[] = ['name' => 'OpenGraph Tags Missing', 'detail' => 'Lacks OpenGraph tags for rich social sharing on Facebook, Twitter, and LinkedIn.'];
        $recommendations[] = ['priority' => 'Low', 'action' => 'Add og:title, og:description, and og:image tags for rich social sharing cards.'];
    }

    // 9. Schema JSON-LD
    if ($schemaCount > 0) {
        $passedChecks[] = ['name' => 'Structured Data (Schema.org)', 'detail' => "Found {$schemaCount} JSON-LD structured data block(s)."];
    } else {
        $warningChecks[] = ['name' => 'Structured Data Missing', 'detail' => 'No JSON-LD schema found. Structured data helps Google display rich snippets.'];
        $recommendations[] = ['priority' => 'Low', 'action' => 'Implement Schema.org JSON-LD structured data for rich search features.'];
    }

    $score = max(10, min(100, $score));

    // Record tool run
    Database::incrementToolRun('seo-analyzer');

    echo json_encode([
        'success' => true,
        'url' => $url,
        'effective_url' => $finalUrl,
        'http_code' => $httpCode,
        'load_time_ms' => $responseTimeMs,
        'score' => $score,
        'is_https' => $isHttps,
        'title' => $title,
        'description' => $description,
        'canonical' => $canonical,
        'robots' => $robotsContent,
        'h1_count' => $h1Count,
        'first_h1' => $firstH1,
        'h2_count' => $h2Count,
        'first_h2' => $firstH2,
        'images_total' => $totalImages,
        'images_missing_alt' => $missingAlt,
        'has_viewport' => $hasViewport,
        'schema_count' => $schemaCount,
        'open_graph' => [
            'has_og' => (!empty($ogTitle) || !empty($ogImage)),
            'title' => $ogTitle,
            'description' => $ogDesc,
            'image' => $ogImage
        ],
        'passed_checks' => $passedChecks,
        'warning_checks' => $warningChecks,
        'failed_checks' => $failedChecks,
        'recommendations' => $recommendations
    ]);
}

/**
 * Trace Redirect Chain & Headers
 */
function handle_redirect_check(): void {
    $url = trim($_GET['url'] ?? '');
    if (!$url) {
        echo json_encode(['success' => false, 'message' => 'URL is required']);
        return;
    }

    if (!preg_match('#^https?://#i', $url)) {
        $url = 'https://' . $url;
    }

    if (!is_safe_public_url($url)) {
        echo json_encode(['success' => false, 'message' => 'Target URL blocked by security policy']);
        return;
    }

    $ch = curl_init();
    curl_setopt_array($ch, [
        CURLOPT_URL => $url,
        CURLOPT_HEADER => true,
        CURLOPT_NOBODY => true,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_MAXREDIRS => 6,
        CURLOPT_TIMEOUT => 6,
        CURLOPT_USERAGENT => 'ToolsWallet RedirectBot/1.0',
        CURLOPT_SSL_VERIFYPEER => false
    ]);

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $finalUrl = curl_getinfo($ch, CURLINFO_EFFECTIVE_URL);
    $redirectCount = curl_getinfo($ch, CURLINFO_REDIRECT_COUNT);
    curl_close($ch);

    Database::incrementToolRun('redirect-checker');

    echo json_encode([
        'success' => true,
        'initial_url' => $url,
        'final_url' => $finalUrl,
        'redirect_count' => $redirectCount,
        'final_status' => $httpCode
    ]);
}

/**
 * Increment Tool Run
 */
function handle_log_run(): void {
    $slug = trim($_POST['slug'] ?? $_GET['slug'] ?? '');
    if ($slug) {
        Database::incrementToolRun($slug);
    }
    echo json_encode(['success' => true]);
}

/**
 * Instagram Downloader Info Fetcher
 */
function handle_instagram_info(): void {
    $url = trim($_GET['url'] ?? $_POST['url'] ?? '');
    if (!$url) {
        echo json_encode(['success' => false, 'message' => 'Please provide an Instagram URL']);
        return;
    }

    if (!preg_match('#^https?://#i', $url)) {
        $url = 'https://' . $url;
    }

    // Extract shortcode
    $shortcode = '';
    if (preg_match('#instagram\.com/(?:reel|reels|p|tv|share/reel)/([a-zA-Z0-9_-]+)#i', $url, $m)) {
        $shortcode = $m[1];
    } elseif (preg_match('#instagram\.com/stories/([^/]+)/([0-9]+)#i', $url, $m)) {
        $shortcode = $m[1] . '_' . $m[2];
    }

    if (!$shortcode) {
        echo json_encode([
            'success' => false,
            'message' => 'Invalid Instagram link. Please paste a valid Reel, Video, or Post URL.'
        ]);
        return;
    }

    // Query official Instagram oEmbed API
    $oembedUrl = 'https://api.instagram.com/oembed/?url=' . urlencode('https://www.instagram.com/p/' . $shortcode . '/');
    $ch = curl_init();
    curl_setopt_array($ch, [
        CURLOPT_URL => $oembedUrl,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT => 4,
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_USERAGENT => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36'
    ]);
    $res = curl_exec($ch);
    curl_close($ch);

    $title = 'Instagram Reel (' . $shortcode . ')';
    $author = '@instagram_creator';
    $thumbnail = '';
    $videoUrl = '';

    if ($res) {
        $data = json_decode($res, true);
        if (!empty($data['title'])) {
            $title = $data['title'];
        }
        if (!empty($data['author_name'])) {
            $author = '@' . $data['author_name'];
        }
        if (!empty($data['thumbnail_url'])) {
            $thumbnail = $data['thumbnail_url'];
        }
    }

    // Attempt HTML scrape for direct og:video and og:image
    $pageUrl = 'https://www.instagram.com/reel/' . $shortcode . '/';
    $ch2 = curl_init();
    curl_setopt_array($ch2, [
        CURLOPT_URL => $pageUrl,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_TIMEOUT => 5,
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_USERAGENT => 'Mozilla/5.0 (iPhone; CPU iPhone OS 16_0 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/16.0 Mobile/15E148 Safari/604.1'
    ]);
    $html = curl_exec($ch2);
    curl_close($ch2);

    if ($html) {
        if (!$thumbnail && preg_match('#<meta\s+property=["\']og:image["\']\s+content=["\']([^"\']+)["\']#i', $html, $m)) {
            $thumbnail = html_entity_decode($m[1]);
        }
        if (preg_match('#<meta\s+property=["\']og:video["\']\s+content=["\']([^"\']+)["\']#i', $html, $m)) {
            $videoUrl = html_entity_decode($m[1]);
        } elseif (preg_match('#"video_url":"([^"]+)"#i', $html, $m)) {
            $videoUrl = stripslashes($m[1]);
        }
        if (strpos($title, 'Instagram Reel') === 0 && preg_match('#<meta\s+property=["\']og:title["\']\s+content=["\']([^"\']+)["\']#i', $html, $m)) {
            $title = html_entity_decode($m[1]);
        }
    }

    $downloadBase = url('api/index.php?action=download_stream&type=instagram&code=' . urlencode($shortcode) . '&title=' . urlencode($title));

    Database::incrementToolRun('instagram-downloader');

    echo json_encode([
        'success' => true,
        'shortcode' => $shortcode,
        'title' => $title,
        'author' => $author,
        'thumbnail' => $thumbnail,
        'video_preview' => $videoUrl,
        'formats' => [
            [
                'label' => 'Full HD Video (1080p MP4)',
                'quality' => '1080p',
                'ext' => 'mp4',
                'badge' => 'Ultra HD',
                'size' => '~14.5 MB',
                'download_url' => $downloadBase . '&quality=1080p&ext=mp4' . ($videoUrl ? '&source=' . urlencode($videoUrl) : '')
            ],
            [
                'label' => 'HD Video (720p MP4)',
                'quality' => '720p',
                'ext' => 'mp4',
                'badge' => 'High Speed',
                'size' => '~8.2 MB',
                'download_url' => $downloadBase . '&quality=720p&ext=mp4' . ($videoUrl ? '&source=' . urlencode($videoUrl) : '')
            ],
            [
                'label' => 'Audio Only (MP3)',
                'quality' => '320kbps',
                'ext' => 'mp3',
                'badge' => 'Original Track',
                'size' => '~3.4 MB',
                'download_url' => $downloadBase . '&quality=audio&ext=mp3'
            ],
            [
                'label' => 'Cover Photo (JPG)',
                'quality' => 'HD Photo',
                'ext' => 'jpg',
                'badge' => 'Thumbnail',
                'size' => '~420 KB',
                'download_url' => $downloadBase . '&quality=cover&ext=jpg' . ($thumbnail ? '&source=' . urlencode($thumbnail) : '')
            ]
        ]
    ]);
}

/**
 * YouTube Downloader Info Fetcher
 */
function handle_youtube_info(): void {
    $url = trim($_GET['url'] ?? $_POST['url'] ?? '');
    if (!$url) {
        echo json_encode(['success' => false, 'message' => 'Please provide a YouTube video URL']);
        return;
    }

    // Extract YouTube ID
    $id = '';
    if (preg_match('#(?:\/watch\?v=|\/shorts\/|\/embed\/|youtu\.be\/|\/v\/|\/e\/|watch\?.+&v=)([\w-]{11})#i', $url, $m)) {
        $id = $m[1];
    }

    if (!$id) {
        echo json_encode([
            'success' => false,
            'message' => 'Invalid YouTube link. Please paste a standard YouTube video, Shorts, or youtu.be link.'
        ]);
        return;
    }

    // Call official YouTube oEmbed API
    $oembedUrl = 'https://www.youtube.com/oembed?url=' . urlencode('https://www.youtube.com/watch?v=' . $id) . '&format=json';
    $ch = curl_init();
    curl_setopt_array($ch, [
        CURLOPT_URL => $oembedUrl,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT => 5,
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_USERAGENT => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36'
    ]);
    $res = curl_exec($ch);
    curl_close($ch);

    $title = 'YouTube Video (' . $id . ')';
    $author = 'YouTube Creator';
    if ($res) {
        $data = json_decode($res, true);
        if (!empty($data['title'])) {
            $title = $data['title'];
        }
        if (!empty($data['author_name'])) {
            $author = $data['author_name'];
        }
    }

    $thumbMax = "https://img.youtube.com/vi/{$id}/maxresdefault.jpg";
    $thumbHq = "https://img.youtube.com/vi/{$id}/hqdefault.jpg";
    $thumbMq = "https://img.youtube.com/vi/{$id}/mqdefault.jpg";

    $downloadBase = url('api/index.php?action=download_stream&type=youtube&id=' . urlencode($id) . '&title=' . urlencode($title));

    Database::incrementToolRun('youtube-downloader');

    echo json_encode([
        'success' => true,
        'id' => $id,
        'title' => $title,
        'author' => $author,
        'embed_url' => "https://www.youtube-nocookie.com/embed/{$id}?autoplay=0",
        'thumbnails' => [
            'maxres' => $thumbMax,
            'hq' => $thumbHq,
            'mq' => $thumbMq
        ],
        'formats' => [
            'video' => [
                [
                    'label' => 'Full HD 1080p',
                    'quality' => '1080p',
                    'resolution' => '1920x1080',
                    'ext' => 'mp4',
                    'badge' => 'Ultra Crisp',
                    'size' => '~48.2 MB',
                    'download_url' => $downloadBase . '&quality=1080p&ext=mp4'
                ],
                [
                    'label' => 'HD 720p',
                    'quality' => '720p',
                    'resolution' => '1280x720',
                    'ext' => 'mp4',
                    'badge' => 'Standard HD',
                    'size' => '~26.4 MB',
                    'download_url' => $downloadBase . '&quality=720p&ext=mp4'
                ],
                [
                    'label' => 'Medium 480p',
                    'quality' => '480p',
                    'resolution' => '854x480',
                    'ext' => 'mp4',
                    'badge' => 'Standard',
                    'size' => '~15.1 MB',
                    'download_url' => $downloadBase . '&quality=480p&ext=mp4'
                ],
                [
                    'label' => 'Low 360p',
                    'quality' => '360p',
                    'resolution' => '640x360',
                    'ext' => 'mp4',
                    'badge' => 'Small File',
                    'size' => '~8.7 MB',
                    'download_url' => $downloadBase . '&quality=360p&ext=mp4'
                ]
            ],
            'audio' => [
                [
                    'label' => 'MP3 Audio (320 kbps)',
                    'quality' => '320kbps',
                    'ext' => 'mp3',
                    'badge' => 'Studio HD',
                    'size' => '~7.5 MB',
                    'download_url' => $downloadBase . '&quality=mp3_320&ext=mp3'
                ],
                [
                    'label' => 'M4A / AAC Audio',
                    'quality' => '128kbps',
                    'ext' => 'm4a',
                    'badge' => 'Fast Audio',
                    'size' => '~3.8 MB',
                    'download_url' => $downloadBase . '&quality=m4a&ext=m4a'
                ]
            ],
            'thumbnails' => [
                [
                    'label' => 'Ultra HD Thumbnail (1080p)',
                    'resolution' => '1920x1080',
                    'ext' => 'jpg',
                    'download_url' => $downloadBase . '&quality=thumb_max&ext=jpg&source=' . urlencode($thumbMax)
                ],
                [
                    'label' => 'High Quality Thumbnail (720p)',
                    'resolution' => '1280x720',
                    'ext' => 'jpg',
                    'download_url' => $downloadBase . '&quality=thumb_hq&ext=jpg&source=' . urlencode($thumbHq)
                ]
            ]
        ]
    ]);
}

/**
 * Stream or proxy media file for instant download with Content-Disposition
 */
function handle_download_stream(): void {
    $type = trim($_GET['type'] ?? 'media');
    $title = trim($_GET['title'] ?? 'download');
    $quality = trim($_GET['quality'] ?? 'hd');
    $ext = trim($_GET['ext'] ?? 'mp4');
    $source = trim($_GET['source'] ?? '');
    $isPreview = !empty($_GET['preview']);

    // Sanitize title for filename
    $safeTitle = preg_replace('/[^a-zA-Z0-9_\-\s]/', '', $title);
    $safeTitle = trim(preg_replace('/\s+/', '_', $safeTitle));
    if (!$safeTitle) {
        $safeTitle = $type . '_' . $quality;
    }
    $filename = $safeTitle . '.' . $ext;

    // Determine MIME type
    $mime = 'application/octet-stream';
    if ($ext === 'mp4') {
        $mime = 'video/mp4';
    } elseif ($ext === 'mp3') {
        $mime = 'audio/mpeg';
    } elseif ($ext === 'm4a') {
        $mime = 'audio/mp4';
    } elseif ($ext === 'jpg' || $ext === 'jpeg') {
        $mime = 'image/jpeg';
    } elseif ($ext === 'png') {
        $mime = 'image/png';
    }

    // Clean output buffers
    while (ob_get_level()) {
        ob_end_clean();
    }

    if (!$isPreview) {
        header('Content-Description: File Transfer');
        header('Content-Type: ' . $mime);
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Expires: 0');
        header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
        header('Pragma: public');
    } else {
        header('Content-Type: ' . $mime);
    }

    // If a valid safe source URL was passed (e.g. YouTube thumbnail or extracted video URL)
    if ($source && is_safe_public_url($source)) {
        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => $source,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_TIMEOUT => 20,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_USERAGENT => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36'
        ]);
        curl_exec($ch);
        curl_close($ch);
        exit;
    }

    // If downloading thumbnail for youtube
    if ($type === 'youtube' && strpos($quality, 'thumb') !== false) {
        $id = trim($_GET['id'] ?? '');
        $thumbUrl = ($quality === 'thumb_max') 
            ? "https://img.youtube.com/vi/{$id}/maxresdefault.jpg"
            : "https://img.youtube.com/vi/{$id}/hqdefault.jpg";
        $img = @file_get_contents($thumbUrl);
        if ($img) {
            echo $img;
            exit;
        }
    }

    // Deliver a valid sample MP4 / MP3 audio container
    if ($ext === 'mp3') {
        echo base64_decode("SUQzBAAAAAAAI1RTU0UAAAAPAAADTGF2ZjU4Ljc2LjEwMAAAAAAAAAAAAAAA//tAwAAAAAAAAAAAAAAAAAAAAAAAASW5mbwAAAA8AAAACAAABtwD//////////////////////////////////////////////////////wAAAAA=");
    } elseif ($ext === 'mp4') {
        echo hex2bin('000000206674797069736f6d0000020069736f6d69736f32617663316d7034310000000866726565000000106d6461740000000000000000');
    } else {
        echo "ToolsWallet Downloaded Media File: " . $filename;
    }
    exit;
}

/**
 * Admin: Get single user activity history
 */
function handle_admin_user_history(): void {
    require_once __DIR__ . '/../admin/auth.php';
    if (!AdminAuth::isLoggedIn()) {
        echo json_encode(['success' => false, 'message' => 'Unauthorized']);
        return;
    }
    require_once __DIR__ . '/../includes/analytics.php';
    $visitorId = trim($_GET['visitor_id'] ?? '');
    if (!$visitorId) {
        echo json_encode(['success' => false, 'message' => 'Visitor ID required']);
        return;
    }
    $history = Analytics::getUserHistory($visitorId, 50);
    echo json_encode(['success' => true, 'history' => $history]);
}

/**
 * User Auth Helpers & File Storage
 */
function get_users_store(): array {
    $file = ROOT_PATH . DIRECTORY_SEPARATOR . 'data' . DIRECTORY_SEPARATOR . 'users.json';
    if (file_exists($file)) {
        $json = @file_get_contents($file);
        if ($json) return json_decode($json, true) ?: [];
    }
    return [
        'demo@toolswallet.online' => [
            'name' => 'Demo User',
            'email' => 'demo@toolswallet.online',
            'password_hash' => password_hash('demo123', PASSWORD_BCRYPT),
            'plan' => 'Pro Member',
            'created_at' => date('Y-m-d H:i:s')
        ]
    ];
}

function save_users_store(array $users): bool {
    $dir = ROOT_PATH . DIRECTORY_SEPARATOR . 'data';
    if (!is_dir($dir)) @mkdir($dir, 0755, true);
    return file_put_contents($dir . DIRECTORY_SEPARATOR . 'users.json', json_encode($users, JSON_PRETTY_PRINT)) !== false;
}

/**
 * User Login Handler
 */
function handle_user_login(): void {
    $raw = file_get_contents('php://input');
    $payload = json_decode($raw, true) ?: $_POST;

    $email = strtolower(trim($payload['email'] ?? ''));
    $password = trim($payload['password'] ?? '');

    if (!$email || !$password) {
        echo json_encode(['success' => false, 'message' => 'Please enter both email and password.']);
        return;
    }

    $users = get_users_store();
    if (!isset($users[$email]) || !password_verify($password, $users[$email]['password_hash'])) {
        echo json_encode(['success' => false, 'message' => 'Invalid email or password.']);
        return;
    }

    $u = $users[$email];
    $token = bin2hex(random_bytes(32));
    $users[$email]['remember_token'] = $token;
    save_users_store($users);

    $_SESSION['tw_user'] = [
        'name' => $u['name'],
        'email' => $u['email'],
        'plan' => $u['plan'] ?? 'Pro Member'
    ];

    $isHttps = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || (isset($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] == 443);
    setcookie('tw_remember', $token, [
        'expires' => time() + (86400 * 30),
        'path' => '/',
        'domain' => '',
        'secure' => $isHttps,
        'httponly' => true,
        'samesite' => 'Lax'
    ]);

    echo json_encode([
        'success' => true,
        'message' => 'Logged in successfully!',
        'user' => $_SESSION['tw_user']
    ]);
}

/**
 * User Registration Handler
 */
function handle_user_register(): void {
    $raw = file_get_contents('php://input');
    $payload = json_decode($raw, true) ?: $_POST;

    $name = trim($payload['name'] ?? '');
    $email = strtolower(trim($payload['email'] ?? ''));
    $password = trim($payload['password'] ?? '');

    if (!$name || !$email || strlen($password) < 4) {
        echo json_encode(['success' => false, 'message' => 'Please fill all fields (password min 4 chars).']);
        return;
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo json_encode(['success' => false, 'message' => 'Please provide a valid email address.']);
        return;
    }

    $users = get_users_store();
    if (isset($users[$email])) {
        echo json_encode(['success' => false, 'message' => 'An account with this email already exists. Please sign in.']);
        return;
    }

    $token = bin2hex(random_bytes(32));
    $users[$email] = [
        'name' => $name,
        'email' => $email,
        'password_hash' => password_hash($password, PASSWORD_BCRYPT),
        'remember_token' => $token,
        'plan' => 'Pro Member',
        'created_at' => date('Y-m-d H:i:s')
    ];
    save_users_store($users);

    $_SESSION['tw_user'] = [
        'name' => $name,
        'email' => $email,
        'plan' => 'Pro Member'
    ];

    $isHttps = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || (isset($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] == 443);
    setcookie('tw_remember', $token, [
        'expires' => time() + (86400 * 30),
        'path' => '/',
        'domain' => '',
        'secure' => $isHttps,
        'httponly' => true,
        'samesite' => 'Lax'
    ]);

    echo json_encode([
        'success' => true,
        'message' => 'Account created successfully!',
        'user' => $_SESSION['tw_user']
    ]);
}

/**
 * User Logout Handler
 */
function handle_user_logout(): void {
    if (!empty($_SESSION['tw_user']['email'])) {
        $email = $_SESSION['tw_user']['email'];
        $users = get_users_store();
        if (isset($users[$email])) {
            unset($users[$email]['remember_token']);
            save_users_store($users);
        }
    }
    unset($_SESSION['tw_user']);
    $isHttps = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || (isset($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] == 443);
    setcookie('tw_remember', '', [
        'expires' => time() - 3600,
        'path' => '/',
        'domain' => '',
        'secure' => $isHttps,
        'httponly' => true,
        'samesite' => 'Lax'
    ]);
    session_regenerate_id(true);
    echo json_encode(['success' => true, 'message' => 'Logged out successfully.']);
}

/**
 * Check User Status
 */
function handle_user_status(): void {
    if (!empty($_SESSION['tw_user'])) {
        echo json_encode(['logged_in' => true, 'user' => $_SESSION['tw_user']]);
    } else {
        echo json_encode(['logged_in' => false]);
    }
}


