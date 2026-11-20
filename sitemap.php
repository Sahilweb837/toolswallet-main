<?php
/**
 * Dynamic XML Sitemap Generator
 * Domain: toolswallet.online
 */

require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/db.php';

header('Content-Type: application/xml; charset=utf-8');

$categories = Database::getCategories();
$tools = Database::getTools();
$today = date('Y-m-d');

echo '<?xml version="1.0" encoding="UTF-8"?>' . PHP_EOL;
?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
    <!-- Homepage -->
    <url>
        <loc><?= url() ?></loc>
        <lastmod><?= $today ?></lastmod>
        <changefreq>daily</changefreq>
        <priority>1.0</priority>
    </url>

    <!-- Categories -->
    <?php foreach ($categories as $slug => $cat): ?>
    <url>
        <loc><?= url('category.php?slug=' . $slug) ?></loc>
        <lastmod><?= $today ?></lastmod>
        <changefreq>weekly</changefreq>
        <priority>0.8</priority>
    </url>
    <?php endforeach; ?>

    <!-- Tools -->
    <?php foreach ($tools as $slug => $tool): ?>
    <url>
        <loc><?= tool_url($slug) ?></loc>
        <lastmod><?= $today ?></lastmod>
        <changefreq>weekly</changefreq>
        <priority>0.9</priority>
    </url>
    <?php endforeach; ?>
</urlset>
