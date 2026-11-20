<?php
/**
 * ToolsWallet Ad Placements (CLS-safe responsive containers)
 * Domain: toolswallet.online
 */

function render_ad_slot(string $slot = 'in_content', string $format = 'responsive'): string {
    // Sponsored Ads disabled across platform
    $adSettingsEnabled = false;
    if (!$adSettingsEnabled) {
        return '';
    }

    $dimensions = [
        'leaderboard' => 'min-height: 90px; max-width: 728px;',
        'medium_rectangle' => 'min-height: 250px; max-width: 336px;',
        'responsive_banner' => 'min-height: 100px; max-width: 970px;',
        'sidebar' => 'min-height: 280px; max-width: 320px;',
        'in_content' => 'min-height: 120px; max-width: 100%;'
    ];

    $style = $dimensions[$format] ?? $dimensions['in_content'];

    ob_start();
    ?>
    <div class="ad-slot-wrapper ad-slot-<?= e($slot) ?>" style="margin: 1.5rem auto; text-align: center; overflow: hidden;">
        <span class="ad-disclaimer">SPONSORED / ADVERTISEMENT</span>
        <div class="ad-container-box" style="<?= $style ?> margin: 0 auto; display: flex; align-items: center; justify-content: center; background: rgba(99, 102, 241, 0.03); border: 1px dashed rgba(99, 102, 241, 0.2); border-radius: 12px; padding: 10px;">
            <div class="ad-placeholder-inner" style="color: var(--text-muted); font-size: 0.8rem; display: flex; flex-direction: column; align-items: center; gap: 4px;">
                <i class="fa-solid fa-rectangle-ad" style="font-size: 1.4rem; color: var(--primary);"></i>
                <span>Responsive Ad Space &bull; Reserved for AdSense (<?= e($format) ?>)</span>
            </div>
        </div>
    </div>
    <?php
    return ob_get_clean();
}
