<?php
/**
 * YouTube Download API Handler
 * Supports: RapidAPI (ytstream / youtube-media-downloader) OR Cobalt public API
 * Save as: /includes/yt-downloader-api.php
 * Call from: /api/yt-download.php
 */

header('Content-Type: application/json');

// Rate limiting — simple per-IP
session_start();
$ip = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
$key = 'yt_dl_' . md5($ip);
$_SESSION[$key] = $_SESSION[$key] ?? ['count' => 0, 'time' => time()];
if (time() - $_SESSION[$key]['time'] > 60) {
    $_SESSION[$key] = ['count' => 0, 'time' => time()];
}
$_SESSION[$key]['count']++;
if ($_SESSION[$key]['count'] > 15) {
    http_response_code(429);
    echo json_encode(['error' => 'Too many requests. Please wait a minute.']);
    exit;
}

// ---------- Read input ----------
$input = json_decode(file_get_contents('php://input'), true);
$url   = trim($input['url'] ?? '');

if (!$url) {
    http_response_code(400);
    echo json_encode(['error' => 'No URL provided']);
    exit;
}

// ---------- Extract video ID ----------
$patterns = [
    '/(?:youtube\.com\/watch\?[^#]*?\bv=)([a-zA-Z0-9_-]{11})/',
    '/(?:youtu\.be\/)([a-zA-Z0-9_-]{11})/',
    '/(?:youtube\.com\/shorts\/)([a-zA-Z0-9_-]{11})/',
    '/(?:youtube\.com\/embed\/)([a-zA-Z0-9_-]{11})/',
    '/^([a-zA-Z0-9_-]{11})$/'
];
$videoId = null;
foreach ($patterns as $p) {
    if (preg_match($p, $url, $m)) { $videoId = $m[1]; break; }
}
if (!$videoId) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid YouTube URL']);
    exit;
}

// ============================================================
// METHOD 1: Cobalt API (free, no key needed for public instance)
// ============================================================
function fetchViaCobalt(string $videoId, string $type = 'video'): ?array {
    $ytUrl = 'https://www.youtube.com/watch?v=' . $videoId;

    $payload = [
        'url'          => $ytUrl,
        'videoQuality' => $type === 'audio' ? '720' : '1080',
        'downloadMode' => $type === 'audio' ? 'audio' : 'auto',
        'audioFormat'  => 'mp3',
        'filenameStyle'=> 'basic',
    ];

    $ch = curl_init('https://api.cobalt.tools/');
    curl_setopt_array($ch, [
        CURLOPT_POST           => true,
        CURLOPT_POSTFIELDS     => json_encode($payload),
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT        => 20,
        CURLOPT_HTTPHEADER     => [
            'Accept: application/json',
            'Content-Type: application/json',
            'User-Agent: Mozilla/5.0',
        ],
    ]);
    $res = curl_exec($ch);
    $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($code !== 200 || !$res) return null;
    $data = json_decode($res, true);
    if (!is_array($data)) return null;

    // Cobalt returns { status: "tunnel"|"redirect"|"stream"|"picker", url: "..." }
    if (in_array($data['status'] ?? '', ['tunnel', 'redirect', 'stream']) && !empty($data['url'])) {
        return ['url' => $data['url'], 'source' => 'cobalt'];
    }
    if (($data['status'] ?? '') === 'picker' && !empty($data['picker'][0]['url'])) {
        return ['url' => $data['picker'][0]['url'], 'source' => 'cobalt'];
    }
    return null;
}

// ============================================================
// METHOD 2: yt-dlp fallback (if installed on server)
// ============================================================
function fetchViaYtDlp(string $videoId, string $type = 'video'): ?array {
    if (!function_exists('shell_exec')) return null;

    $ytUrl = 'https://www.youtube.com/watch?v=' . $videoId;
    if ($type === 'audio') {
        $cmd = 'yt-dlp -f "bestaudio[ext=m4a]/bestaudio" -g --no-playlist ' . escapeshellarg($ytUrl) . ' 2>/dev/null';
    } else {
        $cmd = 'yt-dlp -f "best[height<=1080][ext=mp4]/best[height<=1080]/best" -g --no-playlist ' . escapeshellarg($ytUrl) . ' 2>/dev/null';
    }
    $out = @shell_exec($cmd);
    if (!$out) return null;
    $lines = array_filter(array_map('trim', explode("\n", $out)));
    if (empty($lines)) return null;
    return ['url' => end($lines), 'source' => 'yt-dlp'];
}

// ============================================================
// METHOD 3: RapidAPI (requires key in config.php)
// ============================================================
function fetchViaRapidAPI(string $videoId, string $type = 'video'): ?array {
    // Define RAPIDAPI_KEY in includes/config.php
    if (!defined('RAPIDAPI_KEY') || empty(RAPIDAPI_KEY)) return null;

    $ytUrl = 'https://www.youtube.com/watch?v=' . $videoId;

    // Example: 'youtube-media-downloader.p.rapidapi.com' (subscribe on RapidAPI)
    $ch = curl_init('https://youtube-media-downloader.p.rapidapi.com/v2/video/details?videoId=' . urlencode($videoId));
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT        => 20,
        CURLOPT_HTTPHEADER     => [
            'X-RapidAPI-Key: '  . RAPIDAPI_KEY,
            'X-RapidAPI-Host: youtube-media-downloader.p.rapidapi.com',
        ],
    ]);
    $res = curl_exec($ch);
    $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($code !== 200 || !$res) return null;
    $data = json_decode($res, true);
    if (empty($data)) return null;

    if ($type === 'audio') {
        $audios = $data['audios']['items'] ?? [];
        if (!empty($audios)) {
            // Prefer highest bitrate
            usort($audios, fn($a, $b) => ($b['bitrate'] ?? 0) <=> ($a['bitrate'] ?? 0));
            return ['url' => $audios[0]['url'], 'source' => 'rapidapi', 'quality' => ($audios[0]['bitrate'] ?? 0) . 'kbps'];
        }
    } else {
        $videos = $data['videos']['items'] ?? [];
        if (!empty($videos)) {
            // Prefer 1080p
            usort($videos, fn($a, $b) => ($b['height'] ?? 0) <=> ($a['height'] ?? 0));
            $pick = $videos[0];
            return ['url' => $pick['url'], 'source' => 'rapidapi', 'quality' => ($pick['height'] ?? '') . 'p'];
        }
    }
    return null;
}

// ============================================================
// Try each method in order
// ============================================================
$sources = [
    'rapidapi' => 'fetchViaRapidAPI',
    'cobalt'   => 'fetchViaCobalt',
    'ytdlp'    => 'fetchViaYtDlp',
];

$output = [
    'success'  => true,
    'videoId'  => $videoId,
    'title'    => null,
    'thumbnail'=> "https://img.youtube.com/vi/{$videoId}/maxresdefault.jpg",
    'formats'  => [],
];

// Fetch video
$videoResult = null;
$audioResult = null;

foreach ($sources as $name => $fn) {
    if (!$videoResult) {
        $r = @$fn($videoId, 'video');
        if ($r && !empty($r['url'])) {
            $videoResult = $r;
        }
    }
}
foreach ($sources as $name => $fn) {
    if (!$audioResult) {
        $r = @$fn($videoId, 'audio');
        if ($r && !empty($r['url'])) {
            $audioResult = $r;
        }
    }
}

if ($videoResult) {
    $output['formats'][] = [
        'type'     => 'video',
        'quality'  => $videoResult['quality'] ?? 'HD',
        'label'    => 'MP4 Video',
        'url'      => $videoResult['url'],
        'source'   => $videoResult['source'],
        'icon'     => 'fa-video',
        'color'    => '#ff6b00',
    ];
}
if ($audioResult) {
    $output['formats'][] = [
        'type'     => 'audio',
        'quality'  => $audioResult['quality'] ?? '320 kbps',
        'label'    => 'MP3 Audio',
        'url'      => $audioResult['url'],
        'source'   => $audioResult['source'],
        'icon'     => 'fa-music',
        'color'    => '#10b981',
    ];
}

if (empty($output['formats'])) {
    http_response_code(503);
    echo json_encode([
        'error' => 'Could not fetch download links. Please try again or use a different video.',
        'hint'  => 'Configure RAPIDAPI_KEY in config.php or install yt-dlp on server.'
    ]);
    exit;
}

echo json_encode($output);