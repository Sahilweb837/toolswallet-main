/**
 * ToolsWallet Master JavaScript Application
 * Global UI, Search, Theme, Local Storage & Utilities
 * Domain: toolswallet.online
 */

document.addEventListener('DOMContentLoaded', () => {
    initTheme();
    initSearch();
    initCategoryTabs();
    initRecentTools();
    initFaqAccordion();
    initMobileNav();
});

// ==============================================================
// 1. Theme Switcher (Dark / Light Mode)
// ==============================================================
function initTheme() {
    const themeBtn = document.getElementById('themeToggleBtn');
    const savedTheme = localStorage.getItem('tw_theme');
    const prefersDark = window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches;
    
    const activeTheme = savedTheme ? savedTheme : (prefersDark ? 'dark' : 'light');
    applyTheme(activeTheme);

    if (themeBtn) {
        themeBtn.addEventListener('click', () => {
            const currentTheme = document.documentElement.getAttribute('data-theme') || 'light';
            const newTheme = currentTheme === 'dark' ? 'light' : 'dark';
            applyTheme(newTheme);
            localStorage.setItem('tw_theme', newTheme);
            showToast(`Switched to ${newTheme} mode`, 'info');
        });
    }
}

function applyTheme(theme) {
    document.documentElement.setAttribute('data-theme', theme);
    const themeBtn = document.getElementById('themeToggleBtn');
    if (themeBtn) {
        themeBtn.innerHTML = theme === 'dark' 
            ? '<i class="fa-solid fa-sun" style="color: #f59e0b;"></i>' 
            : '<i class="fa-solid fa-moon"></i>';
    }
}

// ==============================================================
// 2. Global Universal Search & Shortcut (Ctrl + K)
// ==============================================================
function initSearch() {
    const heroInput = document.getElementById('heroSearchInput');
    const navInput = document.getElementById('navSearchInput');
    const dropdown = document.getElementById('searchResultsDropdown');
    const toolCards = document.querySelectorAll('.tool-card');

    // Global Keyboard Shortcut: Ctrl+K or Cmd+K
    window.addEventListener('keydown', (e) => {
        if ((e.ctrlKey || e.metaKey) && e.key.toLowerCase() === 'k') {
            e.preventDefault();
            if (heroInput) {
                heroInput.focus();
                heroInput.scrollIntoView({ behavior: 'smooth', block: 'center' });
            } else if (navInput) {
                navInput.focus();
            }
        }
    });

    function handleLiveFilter(query) {
        query = query.trim().toLowerCase();
        
        // 1. Filter tools grid if on homepage
        if (toolCards.length > 0) {
            let visibleCount = 0;
            toolCards.forEach(card => {
                const name = (card.getAttribute('data-name') || '').toLowerCase();
                const desc = (card.getAttribute('data-desc') || '').toLowerCase();
                const category = (card.getAttribute('data-category') || '').toLowerCase();
                const keywords = (card.getAttribute('data-keywords') || '').toLowerCase();

                if (!query || name.includes(query) || desc.includes(query) || category.includes(query) || keywords.includes(query)) {
                    card.style.display = 'flex';
                    visibleCount++;
                } else {
                    card.style.display = 'none';
                }
            });

            const emptyState = document.getElementById('searchEmptyState');
            if (emptyState) {
                emptyState.style.display = (visibleCount === 0 && query !== '') ? 'block' : 'none';
            }
        }

        // 2. Populate Autocomplete Dropdown if query has at least 2 characters
        if (dropdown) {
            if (query.length < 2) {
                dropdown.classList.remove('active');
                dropdown.innerHTML = '';
                return;
            }

            // Gather matching tools from data attributes
            const matches = [];
            document.querySelectorAll('.tool-card').forEach(card => {
                const name = card.getAttribute('data-name') || '';
                const slug = card.getAttribute('data-slug') || '';
                const desc = card.getAttribute('data-desc') || '';
                const icon = card.getAttribute('data-icon') || 'fa-wrench';
                const url = card.getAttribute('data-url') || `tools/${slug}.php`;

                if (name.toLowerCase().includes(query) || desc.toLowerCase().includes(query)) {
                    matches.push({ name, slug, desc, icon, url });
                }
            });

            if (matches.length > 0) {
                dropdown.innerHTML = matches.slice(0, 6).map(item => `
                    <a href="${item.url}" class="search-result-item">
                        <div class="search-item-icon"><i class="fa-solid ${item.icon}"></i></div>
                        <div>
                            <div style="font-weight: 700; font-size: 0.95rem;">${escapeHtml(item.name)}</div>
                            <div style="font-size: 0.8rem; color: var(--text-muted);">${escapeHtml(item.desc.substring(0, 60))}...</div>
                        </div>
                    </a>
                `).join('');
                dropdown.classList.add('active');
            } else {
                dropdown.innerHTML = `<div style="padding: 1.25rem; text-align: center; color: var(--text-muted); font-size: 0.9rem;">No tools found matching "<strong>${escapeHtml(query)}</strong>"</div>`;
                dropdown.classList.add('active');
            }
        }
    }

    if (heroInput) {
        heroInput.addEventListener('input', (e) => handleLiveFilter(e.target.value));
        heroInput.addEventListener('focus', (e) => {
            if (e.target.value.trim().length >= 2) handleLiveFilter(e.target.value);
        });
    }

    if (navInput) {
        navInput.addEventListener('input', (e) => handleLiveFilter(e.target.value));
    }

    // Close search dropdown on click outside
    document.addEventListener('click', (e) => {
        if (dropdown && !dropdown.contains(e.target) && (!heroInput || !heroInput.contains(e.target))) {
            dropdown.classList.remove('active');
        }
    });

    // Quick tag buttons
    document.querySelectorAll('.quick-tag-btn').forEach(btn => {
        btn.addEventListener('click', () => {
            const term = btn.getAttribute('data-search') || btn.innerText;
            if (heroInput) {
                heroInput.value = term;
                handleLiveFilter(term);
                heroInput.focus();
            }
        });
    });
}

// ==============================================================
// 3. Category Filter Tabs
// ==============================================================
function initCategoryTabs() {
    const tabs = document.querySelectorAll('.filter-tab-btn');
    const toolCards = document.querySelectorAll('.tool-card');

    tabs.forEach(tab => {
        tab.addEventListener('click', () => {
            tabs.forEach(t => t.classList.remove('active'));
            tab.classList.add('active');

            const category = tab.getAttribute('data-category');
            toolCards.forEach(card => {
                const cardCat = card.getAttribute('data-category');
                if (category === 'all' || cardCat === category) {
                    card.style.display = 'flex';
                } else {
                    card.style.display = 'none';
                }
            });
        });
    });
}

// ==============================================================
// 4. Recently Used Tools (localStorage)
// ==============================================================
function initRecentTools() {
    const recentSection = document.getElementById('recentToolsSection');
    const recentPills = document.getElementById('recentToolsPills');
    if (!recentSection || !recentPills) return;

    let recents = [];
    try {
        recents = JSON.parse(localStorage.getItem('tw_recent_tools') || '[]');
    } catch (e) {
        recents = [];
    }

    if (recents.length > 0) {
        recentPills.innerHTML = recents.map(item => `
            <a href="${item.url}" class="recent-pill">
                <i class="fa-solid ${item.icon || 'fa-wrench'}" style="color: var(--primary);"></i>
                <span>${escapeHtml(item.name)}</span>
            </a>
        `).join('') + `
            <button id="clearRecentBtn" class="recent-pill" style="color: var(--text-muted); cursor: pointer; background: transparent; border-style: dashed;">
                <i class="fa-solid fa-trash-can"></i> Clear
            </button>
        `;
        recentSection.style.display = 'block';

        const clearBtn = document.getElementById('clearRecentBtn');
        if (clearBtn) {
            clearBtn.addEventListener('click', () => {
                localStorage.removeItem('tw_recent_tools');
                recentSection.style.display = 'none';
                showToast('Recently used tools cleared', 'info');
            });
        }
    }
}

/**
 * Record a tool visit to recent list
 */
function recordToolVisit(name, slug, icon, url) {
    try {
        let recents = JSON.parse(localStorage.getItem('tw_recent_tools') || '[]');
        recents = recents.filter(item => item.slug !== slug);
        recents.unshift({ name, slug, icon, url });
        if (recents.length > 6) recents = recents.slice(0, 6);
        localStorage.setItem('tw_recent_tools', JSON.stringify(recents));
    } catch (e) {}
}

// ==============================================================
// 5. FAQ Accordion
// ==============================================================
function initFaqAccordion() {
    const faqItems = document.querySelectorAll('.faq-item');
    faqItems.forEach(item => {
        const question = item.querySelector('.faq-question');
        if (question) {
            question.addEventListener('click', () => {
                const isActive = item.classList.contains('active');
                faqItems.forEach(i => i.classList.remove('active'));
                if (!isActive) {
                    item.classList.add('active');
                }
            });
        }
    });
}

// ==============================================================
// 6. Mobile Navigation
// ==============================================================
function initMobileNav() {
    const mobileBtn = document.getElementById('mobileMenuBtn');
    const mobileDrawer = document.getElementById('mobileNavDrawer');
    if (mobileBtn && mobileDrawer) {
        mobileBtn.addEventListener('click', () => {
            mobileDrawer.classList.toggle('active');
        });
    }
}

// ==============================================================
// 7. Global Toast Notification System
// ==============================================================
function showToast(message, type = 'success') {
    let container = document.getElementById('toastContainer');
    if (!container) {
        container = document.createElement('div');
        container.id = 'toastContainer';
        container.className = 'toast-container';
        document.body.appendChild(container);
    }

    const toast = document.createElement('div');
    toast.className = `toast toast-${type}`;
    
    let iconClass = 'fa-circle-check';
    if (type === 'error') iconClass = 'fa-circle-xmark';
    if (type === 'info') iconClass = 'fa-circle-info';

    toast.innerHTML = `<i class="fa-solid ${iconClass}"></i><span>${escapeHtml(message)}</span>`;
    container.appendChild(toast);

    setTimeout(() => toast.classList.add('show'), 10);

    setTimeout(() => {
        toast.classList.remove('show');
        setTimeout(() => toast.remove(), 250);
    }, 3200);
}

// ==============================================================
// 8. Clipboard Copy Helper
// ==============================================================
function copyToClipboard(text, successMsg = 'Copied to clipboard!') {
    if (navigator.clipboard && window.isSecureContext) {
        navigator.clipboard.writeText(text).then(() => {
            showToast(successMsg, 'success');
        }).catch(() => {
            fallbackCopyText(text, successMsg);
        });
    } else {
        fallbackCopyText(text, successMsg);
    }
}

function fallbackCopyText(text, successMsg) {
    const textArea = document.createElement('textarea');
    textArea.value = text;
    textArea.style.position = 'fixed';
    textArea.style.left = '-999999px';
    document.body.appendChild(textArea);
    textArea.focus();
    textArea.select();
    try {
        document.execCommand('copy');
        showToast(successMsg, 'success');
    } catch (err) {
        showToast('Unable to copy', 'error');
    }
    document.body.removeChild(textArea);
}

// ==============================================================
// 9. File Download Helper
// ==============================================================
function downloadFile(content, fileName, mimeType = 'text/plain') {
    const blob = new Blob([content], { type: mimeType });
    const url = URL.createObjectURL(blob);
    const a = document.createElement('a');
    a.href = url;
    a.download = fileName;
    document.body.appendChild(a);
    a.click();
    setTimeout(() => {
        document.body.removeChild(a);
        URL.revokeObjectURL(url);
    }, 100);
}

function escapeHtml(text) {
    if (!text) return '';
    return text
        .replace(/&/g, "&amp;")
        .replace(/</g, "&lt;")
        .replace(/>/g, "&gt;")
        .replace(/"/g, "&quot;")
        .replace(/'/g, "&#039;");
}

// ==============================================================
// 10. User Authentication Modal & Session Controls
// ==============================================================
function openAuthModal(tab = 'signin') {
    const modal = document.getElementById('authModal');
    if (!modal) return;
    switchAuthTab(tab);
    modal.style.display = 'flex';
    const emailIn = tab === 'signin' ? document.getElementById('signInEmail') : document.getElementById('regName');
    emailIn?.focus();
}

function closeAuthModal() {
    const modal = document.getElementById('authModal');
    if (modal) modal.style.display = 'none';
    const alertBox = document.getElementById('authAlertBox');
    if (alertBox) alertBox.style.display = 'none';
}

function switchAuthTab(tab) {
    const btnSign = document.getElementById('tabBtnSignIn');
    const btnReg = document.getElementById('tabBtnRegister');
    const formSign = document.getElementById('formSignIn');
    const formReg = document.getElementById('formRegister');
    const title = document.getElementById('authModalTitle');
    const alertBox = document.getElementById('authAlertBox');
    if (alertBox) alertBox.style.display = 'none';

    if (tab === 'signin') {
        btnSign?.classList.add('active');
        btnReg?.classList.remove('active');
        if (formSign) formSign.style.display = 'block';
        if (formReg) formReg.style.display = 'none';
        if (title) title.innerText = 'Welcome Back! Sign In to ToolsWallet';
    } else {
        btnReg?.classList.add('active');
        btnSign?.classList.remove('active');
        if (formReg) formReg.style.display = 'block';
        if (formSign) formSign.style.display = 'none';
        if (title) title.innerText = 'Create Free Account & Unlock Tools';
    }
}

function fillDemoUser() {
    switchAuthTab('signin');
    const email = document.getElementById('signInEmail');
    const pass = document.getElementById('signInPassword');
    if (email) email.value = 'demo@toolswallet.online';
    if (pass) pass.value = 'demo123';
}

function toggleUserDropdown() {
    const menu = document.getElementById('userDropdownMenu');
    if (menu) {
        menu.style.display = menu.style.display === 'block' ? 'none' : 'block';
    }
}

// Close dropdown on click outside
document.addEventListener('click', (e) => {
    const dd = document.getElementById('userDropdownMenu');
    const btn = document.querySelector('.user-nav-btn');
    if (dd && !dd.contains(e.target) && (!btn || !btn.contains(e.target))) {
        dd.style.display = 'none';
    }
    const modal = document.getElementById('authModal');
    if (modal && e.target === modal) {
        closeAuthModal();
    }
});

async function handleAuthSubmit(e, mode) {
    e.preventDefault();
    const alertBox = document.getElementById('authAlertBox');
    const submitBtn = mode === 'signin' ? document.getElementById('btnSubmitSignIn') : document.getElementById('btnSubmitRegister');
    const apiUrl = (window.APP_CONFIG && window.APP_CONFIG.apiUrl) ? window.APP_CONFIG.apiUrl : 'api/index.php';

    let payload = {};
    if (mode === 'signin') {
        payload = {
            action: 'user_login',
            email: document.getElementById('signInEmail')?.value.trim(),
            password: document.getElementById('signInPassword')?.value.trim()
        };
    } else {
        payload = {
            action: 'user_register',
            name: document.getElementById('regName')?.value.trim(),
            email: document.getElementById('regEmail')?.value.trim(),
            password: document.getElementById('regPassword')?.value.trim()
        };
    }

    if (submitBtn) {
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Processing...';
    }

    try {
        const res = await fetch(`${apiUrl}?action=${payload.action}`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(payload)
        });
        const data = await res.json();

        if (data.success) {
            showToast(data.message || 'Success!', 'success');
            closeAuthModal();
            setTimeout(() => window.location.reload(), 600);
        } else {
            if (alertBox) {
                alertBox.style.display = 'block';
                alertBox.style.background = 'rgba(239, 68, 68, 0.1)';
                alertBox.style.border = '1px solid rgba(239, 68, 68, 0.3)';
                alertBox.style.color = '#ef4444';
                alertBox.innerHTML = `<i class="fa-solid fa-triangle-exclamation"></i> ${escapeHtml(data.message || 'Action failed')}`;
            }
        }
    } catch (err) {
        if (alertBox) {
            alertBox.style.display = 'block';
            alertBox.style.color = '#ef4444';
            alertBox.innerText = 'Failed to connect to authentication server.';
        }
    } finally {
        if (submitBtn) {
            submitBtn.disabled = false;
            submitBtn.innerHTML = mode === 'signin' 
                ? '<i class="fa-solid fa-bolt"></i> Sign In & Continue Exploring'
                : '<i class="fa-solid fa-user-check"></i> Create Account & Unlock Tools';
        }
    }
}

async function handleUserLogout() {
    const apiUrl = (window.APP_CONFIG && window.APP_CONFIG.apiUrl) ? window.APP_CONFIG.apiUrl : 'api/index.php';
    try {
        await fetch(`${apiUrl}?action=user_logout`);
        showToast('Logged out successfully', 'info');
        setTimeout(() => window.location.reload(), 500);
    } catch (e) {
        window.location.reload();
    }
}

