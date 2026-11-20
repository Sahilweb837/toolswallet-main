<?php
/**
 * ToolsWallet SEO Engine
 * Open Graph, Twitter Cards, Canonical URLs & Schema.org JSON-LD Generator
 * Domain: toolswallet.online
 */

function render_seo_tags(array $pageSeo = []): string {
    $defaultTitle = 'ToolsWallet - Free Online Tools Platform | PDF, SEO, Social, AI & Devs';
    $defaultDesc = 'Access 50+ free online tools on ToolsWallet. Convert PDFs, audit SEO, crop social graphics, format JSON, compress images, and calculate loan EMIs instantly with zero registration.';
    $defaultKeywords = 'free online tools, pdf tools, seo tools, social media tools, image compressor, json formatter, qr code generator, online calculators';
    
    $siteName = 'ToolsWallet';
    $siteUrl = rtrim(BASE_URL, '/');
    $currentUrl = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://" . ($_SERVER['HTTP_HOST'] ?? 'toolswallet.online') . ($_SERVER['REQUEST_URI'] ?? '');

    $title = !empty($pageSeo['title']) ? $pageSeo['title'] : $defaultTitle;
    $desc = !empty($pageSeo['description']) ? $pageSeo['description'] : $defaultDesc;
    $keywords = !empty($pageSeo['keywords']) ? $pageSeo['keywords'] : $defaultKeywords;
    $canonical = !empty($pageSeo['canonical']) ? $pageSeo['canonical'] : $currentUrl;
    $ogImage = !empty($pageSeo['image']) ? $pageSeo['image'] : asset('images/toolswallet-og.png');
    $ogType = !empty($pageSeo['type']) ? $pageSeo['type'] : 'website';

    ob_start();
    ?>
    <title><?= e($title) ?></title>
    <meta name="description" content="<?= e($desc) ?>">
    <meta name="keywords" content="<?= e($keywords) ?>">
    <link rel="canonical" href="<?= e($canonical) ?>">
    <meta name="robots" content="index, follow, max-snippet:-1, max-image-preview:large, max-video-preview:-1">
    
    <!-- Open Graph / Facebook -->
    <meta property="og:type" content="<?= e($ogType) ?>">
    <meta property="og:url" content="<?= e($canonical) ?>">
    <meta property="og:site_name" content="<?= e($siteName) ?>">
    <meta property="og:title" content="<?= e($title) ?>">
    <meta property="og:description" content="<?= e($desc) ?>">
    <meta property="og:image" content="<?= e($ogImage) ?>">
    <meta property="og:locale" content="en_US">

    <!-- Twitter -->
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:url" content="<?= e($canonical) ?>">
    <meta name="twitter:title" content="<?= e($title) ?>">
    <meta name="twitter:description" content="<?= e($desc) ?>">
    <meta name="twitter:image" content="<?= e($ogImage) ?>">

    <!-- Schema.org JSON-LD Structured Data -->
    <script type="application/ld+json">
    {
        "@context": "https://schema.org",
        "@graph": [
            {
                "@type": "WebSite",
                "@id": "<?= e($siteUrl) ?>/#website",
                "url": "<?= e($siteUrl) ?>",
                "name": "<?= e($siteName) ?>",
                "description": "<?= e($defaultDesc) ?>",
                "potentialAction": [
                    {
                        "@type": "SearchAction",
                        "target": {
                            "@type": "EntryPoint",
                            "urlTemplate": "<?= e($siteUrl) ?>/?q={search_term_string}"
                        },
                        "query-input": "required name=search_term_string"
                    }
                ]
            }
            <?php if (!empty($pageSeo['tool'])): 
                $tool = $pageSeo['tool'];
            ?>
            ,{
                "@type": "WebApplication",
                "@id": "<?= e($canonical) ?>/#webapp",
                "name": "<?= e($tool['name']) ?>",
                "url": "<?= e($canonical) ?>",
                "applicationCategory": "UtilitiesApplication",
                "operatingSystem": "All",
                "browserRequirements": "Requires JavaScript. Requires HTML5.",
                "offers": {
                    "@type": "Offer",
                    "price": "0",
                    "priceCurrency": "USD"
                },
                "aggregateRating": {
                    "@type": "AggregateRating",
                    "ratingValue": "<?= e((string)($tool['rating'] ?? '4.9')) ?>",
                    "reviewCount": "<?= e((string)($tool['run_count'] ?? '1500')) ?>"
                },
                "description": "<?= e($tool['description'] ?? $desc) ?>"
            }
            <?php endif; ?>
            <?php if (!empty($pageSeo['breadcrumbs'])): ?>
            ,{
                "@type": "BreadcrumbList",
                "itemListElement": [
                    <?php 
                    $bList = [];
                    foreach ($pageSeo['breadcrumbs'] as $idx => $bc) {
                        $bList[] = json_encode([
                            '@type' => 'ListItem',
                            'position' => $idx + 1,
                            'name' => $bc['name'],
                            'item' => $bc['url']
                        ], JSON_UNESCAPED_SLASHES);
                    }
                    echo implode(",\n", $bList);
                    ?>
                ]
            }
            <?php endif; ?>
            <?php if (!empty($pageSeo['faqs'])): ?>
            ,{
                "@type": "FAQPage",
                "mainEntity": [
                    <?php
                    $faqList = [];
                    foreach ($pageSeo['faqs'] as $faq) {
                        $faqList[] = json_encode([
                            '@type' => 'Question',
                            'name' => $faq['question'],
                            'acceptedAnswer' => [
                                '@type' => 'Answer',
                                'text' => $faq['answer']
                            ]
                        ], JSON_UNESCAPED_SLASHES);
                    }
                    echo implode(",\n", $faqList);
                    ?>
                ]
            }
            <?php endif; ?>
        ]
    }
    </script>
    <?php
    return ob_get_clean();
}
