    </main>

    <?php $footerAd = render_ad_slot('footer_banner', 'leaderboard'); ?>
    <?php if (!empty($footerAd)): ?>
    <div class="container" style="margin-top: 2rem;">
        <?= $footerAd ?>
    </div>
    <?php endif; ?>

    <!-- Master Footer -->
    <footer class="footer-master">
        <div class="container footer-grid">
            <!-- Brand Column -->
            <div class="footer-brand">
                <a href="<?= url() ?>" class="brand-logo" aria-label="ToolsWallet">
                    <div class="brand-icon">
                        <svg width="24" height="24" viewBox="0 0 44 44" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <rect x="7" y="11" width="30" height="23" rx="4.5" fill="#FFFFFF" />
                            <path d="M7 15C7 13 9 11 12 11H32C35 11 37 13 37 15V18C33 16.5 27 16 22 16C17 16 11 16.5 7 18V15Z" fill="#ECFDF5" />
                            <path d="M14 7C14 6.45 14.45 6 15 6H29C29.55 6 30 6.45 30 7V11H14V7Z" fill="#D1FAE5" />
                            <rect x="24" y="20" width="13" height="8" rx="3" fill="#10B981" />
                            <circle cx="28" cy="24" r="1.6" fill="#FFFFFF" />
                            <path d="M17 17L13 23H17L15 28L21 21H17L19 17H17Z" fill="#059669" />
                        </svg>
                    </div>
                    <div class="brand-text-wrap">
                        <span class="brand-name">Tools<span class="highlight">Wallet</span></span>
                    </div>
                </a>
                <p>
                    ToolsWallet is an all-in-one suite of 50+ free online developer, SEO, image, PDF, and productivity utilities. Designed for speed, privacy, and zero sign-ups.
                </p>
                <div style="display: flex; gap: 0.75rem; color: var(--text-muted); font-size: 1.1rem; margin-top: 0.5rem;">
                    <a href="https://github.com" target="_blank" rel="noopener" aria-label="GitHub" style="color: inherit;"><i class="fa-brands fa-github"></i></a>
                    <a href="https://twitter.com" target="_blank" rel="noopener" aria-label="Twitter" style="color: inherit;"><i class="fa-brands fa-x-twitter"></i></a>
                    <a href="https://linkedin.com" target="_blank" rel="noopener" aria-label="LinkedIn" style="color: inherit;"><i class="fa-brands fa-linkedin"></i></a>
                </div>
            </div>

            <!-- Categories Column 1 -->
            <div class="footer-col">
                <h4>Popular Categories</h4>
                <ul class="footer-links">
                    <li><a href="<?= url('category.php?slug=pdf-tools') ?>">PDF & Documents</a></li>
                    <li><a href="<?= url('category.php?slug=seo-tools') ?>">SEO & Webmaster</a></li>
                    <li><a href="<?= url('category.php?slug=image-tools') ?>">Image Tools</a></li>
                    <li><a href="<?= url('category.php?slug=developer-tools') ?>">Developer Tools</a></li>
                </ul>
            </div>

            <!-- Categories Column 2 -->
            <div class="footer-col">
                <h4>More Utilities</h4>
                <ul class="footer-links">
                    <li><a href="<?= tool_url('youtube-downloader') ?>">YouTube Downloader</a></li>
                    <li><a href="<?= tool_url('instagram-downloader') ?>">Instagram Downloader</a></li>
                    <li><a href="<?= tool_url('qr-code-generator') ?>">QR Code Generator</a></li>
                    <li><a href="<?= url('category.php?slug=ai-writing') ?>">AI Writing & Text</a></li>
                    <li><a href="<?= url('category.php?slug=calculators') ?>">Calculators & Math</a></li>
                </ul>
            </div>

            <!-- Trust & Legal Column -->
            <div class="footer-col">
                <h4>Platform & Legal</h4>
                <ul class="footer-links">
                    <li><a href="<?= url() ?>#faqs">Platform FAQs</a></li>
                    <li><a href="<?= url('about.php') ?>">About ToolsWallet</a></li>
                    <li><a href="<?= url('contact.php') ?>">Contact Support</a></li>
                    <li><a href="<?= url('privacy.php') ?>">Privacy Policy</a></li>
                    <li><a href="<?= url('terms.php') ?>">Terms of Service</a></li>
                    <li><a href="<?= url('sitemap.xml') ?>">XML Sitemap</a></li>
                    <li><a href="<?= url('robots.txt') ?>">Robots.txt</a></li>
                </ul>
            </div>
        </div>

        <!-- Footer Bottom Bar -->
        <div class="container footer-bottom">
            <div>
                &copy; <?= date('Y') ?> <strong>ToolsWallet</strong> (toolswallet.online). All rights reserved.
            </div>
            <div style="display: flex; align-items: center; gap: 1rem;">
                <span><i class="fa-solid fa-bolt" style="color: #f59e0b;"></i> Lightning Fast</span>
                <span><i class="fa-solid fa-shield-halved" style="color: #10b981;"></i> 100% Secure & Private</span>
                <span><i class="fa-solid fa-lock-open" style="color: #6366f1;"></i> No Registration</span>
            </div>
    </footer>

    <!-- Global User Auth Modal Popup -->
    <?php require_once __DIR__ . '/auth-modal.php'; ?>

    <!-- ToolsWallet Core Scripts -->
    <script src="<?= asset('js/main.js') ?>"></script>
    <script src="<?= asset('js/tools-engine.js') ?>"></script>
</body>
</html>
