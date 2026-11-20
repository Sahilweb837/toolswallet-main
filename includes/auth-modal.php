<?php
/**
 * ToolsWallet User Authentication Modal Popup
 * Enables users to sign in or create a free account to unlock tool history, favorites, and unlimited runs
 * Domain: toolswallet.online
 */
?>
<div id="authModal" class="tw-modal-overlay" style="display: none;">
    <div class="tw-auth-modal">
        <button type="button" class="tw-modal-close" onclick="closeAuthModal()" aria-label="Close">&times;</button>
        
        <div class="tw-auth-header">
            <div class="brand-icon" style="margin: 0 auto 0.75rem;">
                <svg width="24" height="24" viewBox="0 0 44 44" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <rect x="7" y="11" width="30" height="23" rx="4.5" fill="#FFFFFF" />
                    <path d="M7 15C7 13 9 11 12 11H32C35 11 37 13 37 15V18C33 16.5 27 16 22 16C17 16 11 16.5 7 18V15Z" fill="#FFF3EB" />
                    <rect x="24" y="20" width="13" height="8" rx="3" fill="#FF5400" />
                    <circle cx="28" cy="24" r="1.6" fill="#FFFFFF" />
                    <path d="M17 17L13 23H17L15 28L21 21H17L19 17H17Z" fill="#EA580C" />
                </svg>
            </div>
            <h3 id="authModalTitle" style="font-size: 1.35rem; font-weight: 800; color: var(--text-primary); margin-bottom: 0.25rem;">
                Explore Tools with Full Access
            </h3>
            <p id="authModalSubtitle" style="font-size: 0.85rem; color: var(--text-secondary); max-width: 320px; margin: 0 auto;">
                Sign in free to save favorites, sync tool history, and enjoy unlimited 1080p downloads.
            </p>
        </div>

        <!-- Auth Mode Tabs -->
        <div class="tw-auth-tabs">
            <button type="button" class="tw-auth-tab active" id="tabBtnSignIn" onclick="switchAuthTab('signin')">
                <i class="fa-solid fa-arrow-right-to-bracket"></i> Sign In
            </button>
            <button type="button" class="tw-auth-tab" id="tabBtnRegister" onclick="switchAuthTab('register')">
                <i class="fa-solid fa-user-plus"></i> Free Account
            </button>
        </div>

        <div id="authAlertBox" style="display: none; padding: 0.65rem 0.85rem; border-radius: var(--radius-md); font-size: 0.85rem; margin-bottom: 1rem;"></div>

        <!-- Sign In Form -->
        <form id="formSignIn" onsubmit="handleAuthSubmit(event, 'signin')">
            <div class="form-group" style="margin-bottom: 1rem;">
                <label style="display: block; font-size: 0.82rem; font-weight: 700; margin-bottom: 0.35rem; color: var(--text-primary);">Email Address</label>
                <div style="position: relative;">
                    <i class="fa-regular fa-envelope" style="position: absolute; left: 1rem; top: 50%; transform: translateY(-50%); color: var(--text-muted); font-size: 0.9rem;"></i>
                    <input type="email" id="signInEmail" class="form-control" style="padding-left: 2.6rem; height: 46px; border-radius: var(--radius-md); font-size: 0.9rem;" placeholder="name@example.com" required>
                </div>
            </div>

            <div class="form-group" style="margin-bottom: 1.25rem;">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.35rem;">
                    <label style="font-size: 0.82rem; font-weight: 700; color: var(--text-primary); margin: 0;">Password</label>
                    <a href="javascript:void(0)" onclick="alert('Demo account: demo@toolswallet.online / demo123')" style="font-size: 0.75rem; color: var(--primary);">Need Demo Pass?</a>
                </div>
                <div style="position: relative;">
                    <i class="fa-solid fa-lock" style="position: absolute; left: 1rem; top: 50%; transform: translateY(-50%); color: var(--text-muted); font-size: 0.9rem;"></i>
                    <input type="password" id="signInPassword" class="form-control" style="padding-left: 2.6rem; height: 46px; border-radius: var(--radius-md); font-size: 0.9rem;" placeholder="Enter password" required>
                </div>
            </div>

            <button type="submit" id="btnSubmitSignIn" class="btn-primary-gradient" style="width: 100%; height: 48px; font-size: 0.95rem; font-weight: 700; border-radius: var(--radius-md);">
                <i class="fa-solid fa-bolt"></i> Sign In & Continue Exploring
            </button>
        </form>

        <!-- Register Form -->
        <form id="formRegister" style="display: none;" onsubmit="handleAuthSubmit(event, 'register')">
            <div class="form-group" style="margin-bottom: 0.85rem;">
                <label style="display: block; font-size: 0.82rem; font-weight: 700; margin-bottom: 0.35rem; color: var(--text-primary);">Full Name</label>
                <div style="position: relative;">
                    <i class="fa-regular fa-user" style="position: absolute; left: 1rem; top: 50%; transform: translateY(-50%); color: var(--text-muted); font-size: 0.9rem;"></i>
                    <input type="text" id="regName" class="form-control" style="padding-left: 2.6rem; height: 46px; border-radius: var(--radius-md); font-size: 0.9rem;" placeholder="Your Name" required>
                </div>
            </div>

            <div class="form-group" style="margin-bottom: 0.85rem;">
                <label style="display: block; font-size: 0.82rem; font-weight: 700; margin-bottom: 0.35rem; color: var(--text-primary);">Email Address</label>
                <div style="position: relative;">
                    <i class="fa-regular fa-envelope" style="position: absolute; left: 1rem; top: 50%; transform: translateY(-50%); color: var(--text-muted); font-size: 0.9rem;"></i>
                    <input type="email" id="regEmail" class="form-control" style="padding-left: 2.6rem; height: 46px; border-radius: var(--radius-md); font-size: 0.9rem;" placeholder="name@example.com" required>
                </div>
            </div>

            <div class="form-group" style="margin-bottom: 1.25rem;">
                <label style="display: block; font-size: 0.82rem; font-weight: 700; margin-bottom: 0.35rem; color: var(--text-primary);">Create Password</label>
                <div style="position: relative;">
                    <i class="fa-solid fa-lock" style="position: absolute; left: 1rem; top: 50%; transform: translateY(-50%); color: var(--text-muted); font-size: 0.9rem;"></i>
                    <input type="password" id="regPassword" class="form-control" style="padding-left: 2.6rem; height: 46px; border-radius: var(--radius-md); font-size: 0.9rem;" placeholder="Minimum 4 characters" minlength="4" required>
                </div>
            </div>

            <button type="submit" id="btnSubmitRegister" class="btn-primary-gradient" style="width: 100%; height: 48px; font-size: 0.95rem; font-weight: 700; border-radius: var(--radius-md);">
                <i class="fa-solid fa-user-check"></i> Create Account & Unlock Tools
            </button>
        </form>

        <!-- Demo credentials quick fill -->
        <div style="margin-top: 1rem; text-align: center; font-size: 0.8rem; color: var(--text-muted);">
            <span>Quick Demo: </span>
            <button type="button" onclick="fillDemoUser()" style="background: none; border: none; color: var(--primary); font-weight: 700; cursor: pointer; text-decoration: underline;">
                Use demo@toolswallet.online
            </button>
        </div>

        <div style="display: flex; align-items: center; justify-content: center; gap: 1rem; margin-top: 1.25rem; padding-top: 1rem; border-top: 1px solid var(--border-subtle); font-size: 0.75rem; color: var(--text-muted);">
            <span><i class="fa-solid fa-shield-halved" style="color: #10b981;"></i> 100% Free Forever</span>
            <span><i class="fa-solid fa-lock" style="color: var(--primary);"></i> Privacy Guaranteed</span>
        </div>
    </div>
</div>

<style>
.tw-modal-overlay {
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(15, 23, 42, 0.65);
    backdrop-filter: blur(8px);
    -webkit-backdrop-filter: blur(8px);
    z-index: 9999;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 1.25rem;
}
.tw-auth-modal {
    background: var(--bg-surface);
    border: 1px solid var(--border-subtle);
    border-radius: var(--radius-xl);
    width: 100%;
    max-width: 420px;
    padding: 2.25rem 2rem;
    position: relative;
    box-shadow: 0 25px 50px -12px rgba(234, 88, 12, 0.25);
    animation: authModalPop 0.25s cubic-bezier(0.16, 1, 0.3, 1);
}
@keyframes authModalPop {
    from { opacity: 0; transform: scale(0.92) translateY(10px); }
    to { opacity: 1; transform: scale(1) translateY(0); }
}
.tw-modal-close {
    position: absolute;
    right: 1.25rem;
    top: 1.25rem;
    background: var(--bg-secondary);
    border: 1px solid var(--border-subtle);
    width: 32px;
    height: 32px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.25rem;
    color: var(--text-muted);
    cursor: pointer;
    line-height: 1;
    transition: all var(--transition-fast);
}
.tw-modal-close:hover {
    color: #ef4444;
    border-color: #ef4444;
}
.tw-auth-header {
    text-align: center;
    margin-bottom: 1.5rem;
}
.tw-auth-tabs {
    display: flex;
    background: var(--bg-secondary);
    border-radius: var(--radius-full);
    padding: 4px;
    margin-bottom: 1.25rem;
    border: 1px solid var(--border-subtle);
}
.tw-auth-tab {
    flex: 1;
    background: transparent;
    border: none;
    padding: 0.5rem;
    border-radius: var(--radius-full);
    font-size: 0.85rem;
    font-weight: 700;
    color: var(--text-secondary);
    cursor: pointer;
    transition: all var(--transition-fast);
}
.tw-auth-tab.active {
    background: var(--primary);
    color: #fff;
    box-shadow: 0 2px 8px rgba(234, 88, 12, 0.3);
}
</style>
