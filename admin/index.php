<?php
/**
 * ToolsWallet Master Admin Dashboard
 * Traffic Analytics & Single-User Tracking Center
 * Domain: toolswallet.online
 */

require_once __DIR__ . '/auth.php';
AdminAuth::requireLogin();

require_once __DIR__ . '/../includes/analytics.php';
require_once __DIR__ . '/../includes/db.php';

$filter = $_GET['filter'] ?? 'all';
if (!in_array($filter, ['all', 'today', '7d', '30d'])) {
    $filter = 'all';
}

$kpi = Analytics::getKpiStats();
$pageTraffic = Analytics::getPageTraffic(60, $filter);
$singleUsers = Analytics::getSingleUsers(30, 0);
$recentVisits = Analytics::getRecentVisits(20);
$deviceStats = Analytics::getDeviceStats();

// Calculate total views for percentage bars
$sumViews = 0;
foreach ($pageTraffic as $row) {
    $sumViews += (int)$row['total_views'];
}
$sumViews = max(1, $sumViews);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard & Traffic Analytics | ToolsWallet</title>
    <link rel="icon" type="image/svg+xml" href="<?= asset('images/favicon.svg') ?>">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="<?= asset('css/style.css') ?>">
    <style>
        :root {
            --admin-sidebar-w: 260px;
        }
        body {
            background-color: var(--bg-body);
            color: var(--text-primary);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }
        .admin-nav {
            background: var(--bg-surface);
            border-bottom: 1px solid var(--border-subtle);
            position: sticky;
            top: 0;
            z-index: 100;
        }
        .admin-nav-inner {
            display: flex;
            align-items: center;
            justify-content: space-between;
            height: 70px;
            padding: 0 1.5rem;
        }
        .admin-kpi-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 1.25rem;
            margin-bottom: 2rem;
        }
        .admin-kpi-card {
            background: var(--bg-surface);
            border: 1px solid var(--border-subtle);
            border-radius: var(--radius-lg);
            padding: 1.5rem;
            box-shadow: var(--shadow-sm);
            position: relative;
            overflow: hidden;
            transition: transform var(--transition-fast), border-color var(--transition-fast);
        }
        .admin-kpi-card:hover {
            transform: translateY(-2px);
            border-color: var(--primary-border);
        }
        .admin-kpi-card::after {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 4px;
            height: 100%;
            background: var(--kpi-color, var(--primary));
        }
        .admin-table-card {
            background: var(--bg-surface);
            border: 1px solid var(--border-subtle);
            border-radius: var(--radius-lg);
            padding: 1.5rem;
            box-shadow: var(--shadow-sm);
            margin-bottom: 2rem;
        }
        .admin-table {
            width: 100%;
            border-collapse: collapse;
            text-align: left;
            font-size: 0.9rem;
        }
        .admin-table th {
            padding: 0.85rem 1rem;
            border-bottom: 2px solid var(--border-subtle);
            color: var(--text-secondary);
            font-weight: 700;
            font-size: 0.8rem;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }
        .admin-table td {
            padding: 1rem;
            border-bottom: 1px solid var(--border-subtle);
            vertical-align: middle;
        }
        .admin-table tr:hover td {
            background: var(--bg-secondary);
        }
        .progress-bar-bg {
            background: var(--bg-secondary);
            height: 8px;
            border-radius: var(--radius-full);
            overflow: hidden;
            width: 120px;
        }
        .progress-bar-fill {
            height: 100%;
            background: linear-gradient(90deg, #ff7a00, #ea580c);
            border-radius: var(--radius-full);
        }
        .user-journey-tag {
            display: inline-block;
            background: var(--bg-body);
            border: 1px solid var(--border-subtle);
            border-radius: var(--radius-sm);
            padding: 2px 6px;
            font-size: 0.75rem;
            margin: 2px;
            color: var(--text-secondary);
        }
        /* Modal for User Journey */
        .admin-modal-overlay {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0, 0, 0, 0.6);
            backdrop-filter: blur(4px);
            z-index: 1000;
            display: none;
            align-items: center;
            justify-content: center;
            padding: 1rem;
        }
        .admin-modal-overlay.active {
            display: flex;
        }
        .admin-modal {
            background: var(--bg-surface);
            border-radius: var(--radius-xl);
            max-width: 650px;
            width: 100%;
            max-height: 85vh;
            overflow-y: auto;
            border: 1px solid var(--border-subtle);
            box-shadow: var(--shadow-xl);
            padding: 2rem;
            position: relative;
        }
    </style>
</head>
<body>

    <!-- Admin Navbar -->
    <header class="admin-nav">
        <div class="container-wide admin-nav-inner">
            <div style="display: flex; align-items: center; gap: 1.5rem;">
                <a href="<?= url('admin/index.php') ?>" class="brand-logo">
                    <div class="brand-icon">
                        <svg width="24" height="24" viewBox="0 0 44 44" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <rect x="7" y="11" width="30" height="23" rx="4.5" fill="#FFFFFF" />
                            <path d="M7 15C7 13 9 11 12 11H32C35 11 37 13 37 15V18C33 16.5 27 16 22 16C17 16 11 16.5 7 18V15Z" fill="#FFF3EB" />
                            <rect x="24" y="20" width="13" height="8" rx="3" fill="#FF5400" />
                            <circle cx="28" cy="24" r="1.6" fill="#FFFFFF" />
                            <path d="M17 17L13 23H17L15 28L21 21H17L19 17H17Z" fill="#EA580C" />
                        </svg>
                    </div>
                    <div class="brand-text-wrap">
                        <span class="brand-name">Tools<span class="highlight">Wallet</span></span>
                        <span class="brand-badge-tag" style="background: rgba(234, 88, 12, 0.15); color: #ea580c;">ADMIN</span>
                    </div>
                </a>

                <span style="display: inline-flex; align-items: center; gap: 0.4rem; font-size: 0.8rem; font-weight: 700; color: #10b981; background: rgba(16, 185, 129, 0.1); padding: 4px 10px; border-radius: var(--radius-full);">
                    <span style="width: 8px; height: 8px; border-radius: 50%; background: #10b981; animation: pulseDot 2s infinite;"></span>
                    Live Tracking Active
                </span>
            </div>

            <div style="display: flex; align-items: center; gap: 1rem;">
                <a href="<?= url() ?>" target="_blank" class="btn-secondary btn-sm" style="display: inline-flex; align-items: center; gap: 0.4rem;">
                    <i class="fa-solid fa-arrow-up-right-from-square"></i> Visit Public Website
                </a>
                <div style="display: flex; align-items: center; gap: 0.5rem; background: var(--bg-secondary); padding: 0.35rem 0.75rem; border-radius: var(--radius-full); font-size: 0.85rem; font-weight: 700;">
                    <i class="fa-solid fa-user-shield" style="color: var(--primary);"></i>
                    <span><?= e($_SESSION['admin_user'] ?? 'Admin') ?></span>
                </div>
                <a href="<?= url('admin/logout.php') ?>" class="btn-secondary btn-sm" style="color: #ef4444; border-color: rgba(239, 68, 68, 0.2);">
                    <i class="fa-solid fa-right-from-bracket"></i> Logout
                </a>
            </div>
        </div>
    </header>

    <!-- Main Admin Container -->
    <main class="container-wide" style="padding-top: 2rem; padding-bottom: 4rem;">
        
        <!-- Header Banner & Timeframe Filters -->
        <div style="display: flex; justify-content: space-between; align-items: flex-end; flex-wrap: wrap; gap: 1rem; margin-bottom: 2rem;">
            <div>
                <h1 style="font-size: 1.85rem; font-weight: 800; color: var(--text-primary); margin-bottom: 0.3rem;">
                    <i class="fa-solid fa-chart-pie" style="color: var(--primary);"></i> Traffic Analytics & Single-User Breakdown
                </h1>
                <p style="color: var(--text-secondary); font-size: 0.95rem;">
                    Har page par kitna traffic hai aur single users ne kaun kaun se tools explore kiye hain, sab real-time data yahan track hota hai.
                </p>
            </div>

            <!-- Timeframe Filter Pills -->
            <div style="display: flex; gap: 0.4rem; background: var(--bg-surface); padding: 4px; border-radius: var(--radius-full); border: 1px solid var(--border-subtle);">
                <a href="?filter=all" class="filter-tab-btn <?= $filter === 'all' ? 'active' : '' ?>" style="font-size: 0.82rem; padding: 0.4rem 0.9rem;">All Time</a>
                <a href="?filter=today" class="filter-tab-btn <?= $filter === 'today' ? 'active' : '' ?>" style="font-size: 0.82rem; padding: 0.4rem 0.9rem;">Today</a>
                <a href="?filter=7d" class="filter-tab-btn <?= $filter === '7d' ? 'active' : '' ?>" style="font-size: 0.82rem; padding: 0.4rem 0.9rem;">Last 7 Days</a>
                <a href="?filter=30d" class="filter-tab-btn <?= $filter === '30d' ? 'active' : '' ?>" style="font-size: 0.82rem; padding: 0.4rem 0.9rem;">Last 30 Days</a>
            </div>
        </div>

        <!-- KPI Summary Cards -->
        <div class="admin-kpi-grid">
            <div class="admin-kpi-card" style="--kpi-color: #ff6b00;">
                <div style="font-size: 0.8rem; font-weight: 700; text-transform: uppercase; color: var(--text-muted); margin-bottom: 0.4rem;">
                    Total Page Views (Hits)
                </div>
                <div style="font-size: 2.2rem; font-weight: 800; color: var(--text-primary);">
                    <?= number_format($kpi['total_views']) ?>
                </div>
                <div style="font-size: 0.8rem; color: #10b981; margin-top: 0.4rem; font-weight: 600;">
                    <i class="fa-solid fa-arrow-trend-up"></i> <?= number_format($kpi['today_views']) ?> views today
                </div>
            </div>

            <div class="admin-kpi-card" style="--kpi-color: #3b82f6;">
                <div style="font-size: 0.8rem; font-weight: 700; text-transform: uppercase; color: var(--text-muted); margin-bottom: 0.4rem;">
                    Unique Single Users
                </div>
                <div style="font-size: 2.2rem; font-weight: 800; color: var(--text-primary);">
                    <?= number_format($kpi['total_visitors']) ?>
                </div>
                <div style="font-size: 0.8rem; color: #3b82f6; margin-top: 0.4rem; font-weight: 600;">
                    <i class="fa-solid fa-users"></i> <?= number_format($kpi['today_visitors']) ?> unique visitors today
                </div>
            </div>

            <div class="admin-kpi-card" style="--kpi-color: #10b981;">
                <div style="font-size: 0.8rem; font-weight: 700; text-transform: uppercase; color: var(--text-muted); margin-bottom: 0.4rem;">
                    Active Single Users Now
                </div>
                <div style="font-size: 2.2rem; font-weight: 800; color: #10b981;">
                    <?= number_format($kpi['live_now']) ?>
                </div>
                <div style="font-size: 0.8rem; color: var(--text-muted); margin-top: 0.4rem;">
                    Active in past 15 minutes
                </div>
            </div>

            <div class="admin-kpi-card" style="--kpi-color: #8b5cf6;">
                <div style="font-size: 0.8rem; font-weight: 700; text-transform: uppercase; color: var(--text-muted); margin-bottom: 0.4rem;">
                    Avg. Views Per User
                </div>
                <div style="font-size: 2.2rem; font-weight: 800; color: var(--text-primary);">
                    <?= $kpi['total_visitors'] > 0 ? round($kpi['total_views'] / $kpi['total_visitors'], 1) : '1.0' ?>
                </div>
                <div style="font-size: 0.8rem; color: var(--text-muted); margin-top: 0.4rem;">
                    Deep user engagement ratio
                </div>
            </div>
        </div>

        <!-- ==============================================================
             SECTION 1: Page-by-Page Traffic Breakdown (Core Requirement)
             ============================================================== -->
        <div class="admin-table-card">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.25rem; flex-wrap: wrap; gap: 1rem;">
                <div>
                    <h2 style="font-size: 1.25rem; font-weight: 800; color: var(--text-primary);">
                        <i class="fa-solid fa-table-list" style="color: var(--primary);"></i> Kis Page Par Kitna Traffic Hai (Single User Breakdown)
                    </h2>
                    <p style="font-size: 0.85rem; color: var(--text-muted);">
                        Ranking of all tool pages and URLs with exact hit counts, single unique visitors, and view percentage.
                    </p>
                </div>

                <!-- Page Filter Search Box -->
                <div style="position: relative; width: 280px;">
                    <i class="fa-solid fa-magnifying-glass" style="position: absolute; left: 1rem; top: 50%; transform: translateY(-50%); color: var(--text-muted); font-size: 0.85rem;"></i>
                    <input type="text" id="pageSearchInput" placeholder="Filter pages or tools..." style="width: 100%; height: 38px; padding: 0 1rem 0 2.5rem; border: 1px solid var(--border-subtle); background: var(--bg-body); border-radius: var(--radius-full); font-size: 0.85rem;">
                </div>
            </div>

            <div style="overflow-x: auto;">
                <table class="admin-table" id="pageTrafficTable">
                    <thead>
                        <tr>
                            <th style="width: 35%;">Page / Dedicated Tool</th>
                            <th style="width: 15%;">Total Views</th>
                            <th style="width: 18%;">Single Unique Users</th>
                            <th style="width: 12%;">Views / User</th>
                            <th style="width: 20%;">Share & Last Visited</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($pageTraffic)): ?>
                            <tr>
                                <td colspan="5" style="text-align: center; padding: 3rem; color: var(--text-muted);">
                                    <i class="fa-solid fa-chart-simple" style="font-size: 2.5rem; margin-bottom: 1rem; color: var(--primary);"></i>
                                    <div>No page traffic logged yet. Visit pages on the website to generate live analytics.</div>
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($pageTraffic as $p): 
                                $pct = round(((int)$p['total_views'] / $sumViews) * 100, 1);
                                $isTool = !empty($p['tool_slug']);
                            ?>
                                <tr class="page-row" data-page="<?= strtolower(e($p['page_url'] . ' ' . ($p['tool_slug'] ?? ''))) ?>">
                                    <td>
                                        <div style="display: flex; align-items: center; gap: 0.75rem;">
                                            <div style="width: 34px; height: 34px; border-radius: 8px; background: <?= $isTool ? 'rgba(255, 107, 0, 0.1)' : 'var(--bg-secondary)' ?>; color: <?= $isTool ? 'var(--primary)' : 'var(--text-secondary)' ?>; display: flex; align-items: center; justify-content: center; font-size: 0.95rem;">
                                                <i class="fa-solid <?= $isTool ? 'fa-screwdriver-wrench' : 'fa-globe' ?>"></i>
                                            </div>
                                            <div>
                                                <div style="font-weight: 700; color: var(--text-primary); display: flex; align-items: center; gap: 0.4rem;">
                                                    <span><?= e($p['page_url']) ?></span>
                                                    <?php if ($isTool): ?>
                                                        <span class="badge" style="background: rgba(255, 107, 0, 0.1); color: #ea580c; border: 1px solid rgba(255, 107, 0, 0.2); font-size: 0.68rem; padding: 1px 6px;">TOOL</span>
                                                    <?php endif; ?>
                                                </div>
                                                <div style="font-size: 0.78rem; color: var(--text-muted);">
                                                    <a href="<?= e($p['page_url']) ?>" target="_blank" style="color: inherit; text-decoration: underline;">Open Link <i class="fa-solid fa-arrow-up-right-from-square" style="font-size: 0.7rem;"></i></a>
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <div style="font-size: 1.15rem; font-weight: 800; color: var(--text-primary);">
                                            <?= number_format((int)$p['total_views']) ?>
                                        </div>
                                        <span style="font-size: 0.75rem; color: var(--text-muted);">hits recorded</span>
                                    </td>
                                    <td>
                                        <div style="display: inline-flex; align-items: center; gap: 0.4rem; background: rgba(59, 130, 246, 0.1); color: #2563eb; padding: 4px 10px; border-radius: var(--radius-full); font-weight: 700; font-size: 0.85rem;">
                                            <i class="fa-solid fa-user"></i> <?= number_format((int)$p['unique_users']) ?> Single Users
                                        </div>
                                    </td>
                                    <td>
                                        <span style="font-weight: 700; font-size: 0.95rem; color: var(--text-primary);"><?= $p['views_per_user'] ?>x</span>
                                    </td>
                                    <td>
                                        <div style="display: flex; align-items: center; gap: 0.75rem;">
                                            <div class="progress-bar-bg">
                                                <div class="progress-bar-fill" style="width: <?= min(100, max(5, $pct)) ?>%;"></div>
                                            </div>
                                            <span style="font-size: 0.8rem; font-weight: 700;"><?= $pct ?>%</span>
                                        </div>
                                        <div style="font-size: 0.75rem; color: var(--text-muted); margin-top: 0.25rem;">
                                            Last: <?= date('M d, H:i', strtotime($p['last_visited'])) ?>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- ==============================================================
             SECTION 2: Single User Explorer (Individual Visitor Journeys)
             ============================================================== -->
        <div class="admin-table-card">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.25rem; flex-wrap: wrap; gap: 1rem;">
                <div>
                    <h2 style="font-size: 1.25rem; font-weight: 800; color: var(--text-primary);">
                        <i class="fa-solid fa-fingerprint" style="color: var(--primary);"></i> Single User Visitors Explorer
                    </h2>
                    <p style="font-size: 0.85rem; color: var(--text-muted);">
                        Har individual user (Single User) ka footprint, unka device/browser, kitne tools use kiye aur kaun kaun se page dekhe.
                    </p>
                </div>
            </div>

            <div style="overflow-x: auto;">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>Single User ID</th>
                            <th>Device & OS</th>
                            <th>Browser</th>
                            <th>Total Page Views</th>
                            <th>Tools Explored</th>
                            <th>Pages Visited (Path)</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($singleUsers)): ?>
                            <tr>
                                <td colspan="7" style="text-align: center; padding: 2.5rem; color: var(--text-muted);">
                                    No unique visitor profiles logged yet.
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($singleUsers as $user): 
                                $pages = explode(',', $user['visited_pages'] ?? '');
                            ?>
                                <tr>
                                    <td>
                                        <div style="display: flex; align-items: center; gap: 0.5rem;">
                                            <div style="width: 32px; height: 32px; border-radius: 50%; background: linear-gradient(135deg, #ff7a00, #ea580c); color: #fff; display: flex; align-items: center; justify-content: center; font-size: 0.8rem; font-weight: 700;">
                                                <i class="fa-solid fa-user"></i>
                                            </div>
                                            <div>
                                                <div style="font-family: var(--font-mono); font-size: 0.85rem; font-weight: 700; color: var(--text-primary);"><?= e($user['visitor_id']) ?></div>
                                                <div style="font-size: 0.72rem; color: var(--text-muted);">First seen: <?= date('M d, H:i', strtotime($user['first_seen'])) ?></div>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <div style="font-weight: 600; font-size: 0.85rem; display: flex; align-items: center; gap: 0.4rem;">
                                            <i class="fa-solid <?= $user['device_type'] === 'Mobile' ? 'fa-mobile-screen' : ($user['device_type'] === 'Tablet' ? 'fa-tablet-screen-button' : 'fa-laptop') ?>" style="color: var(--primary);"></i>
                                            <?= e($user['device_type']) ?> (<?= e($user['os']) ?>)
                                        </div>
                                    </td>
                                    <td>
                                        <span style="font-size: 0.85rem; font-weight: 600;"><?= e($user['browser']) ?></span>
                                    </td>
                                    <td>
                                        <div style="font-weight: 800; font-size: 1rem; color: var(--text-primary);"><?= (int)$user['total_views'] ?></div>
                                        <span style="font-size: 0.72rem; color: var(--text-muted);">views</span>
                                    </td>
                                    <td>
                                        <span class="badge" style="background: rgba(16, 185, 129, 0.1); color: #10b981; border: 1px solid rgba(16, 185, 129, 0.2); font-weight: 700; font-size: 0.8rem; padding: 3px 8px;">
                                            <?= (int)$user['tools_used'] ?> tools
                                        </span>
                                    </td>
                                    <td>
                                        <div style="max-width: 320px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                                            <?php foreach (array_slice($pages, 0, 3) as $pg): if (!trim($pg)) continue; ?>
                                                <span class="user-journey-tag"><?= e($pg) ?></span>
                                            <?php endforeach; ?>
                                            <?php if (count($pages) > 3): ?>
                                                <span class="user-journey-tag" style="background: var(--primary-light); color: var(--primary);">+<?= count($pages) - 3 ?> more</span>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                    <td>
                                        <button type="button" class="btn-secondary btn-sm" onclick="showUserJourney('<?= e($user['visitor_id']) ?>')">
                                            <i class="fa-solid fa-list-check"></i> Details
                                        </button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- ==============================================================
             SECTION 3: Technology & Real-Time Stream Grid
             ============================================================== -->
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(320px, 1fr)); gap: 1.5rem;">
            <!-- Devices & Browsers Card -->
            <div class="admin-table-card" style="margin-bottom: 0;">
                <h3 style="font-size: 1.1rem; font-weight: 800; margin-bottom: 1.25rem; color: var(--text-primary);">
                    <i class="fa-solid fa-mobile-screen-button" style="color: var(--primary);"></i> Device & Browser Distribution
                </h3>
                
                <div style="margin-bottom: 1.5rem;">
                    <div style="font-size: 0.8rem; font-weight: 700; color: var(--text-muted); margin-bottom: 0.5rem; text-transform: uppercase;">Devices</div>
                    <?php foreach ($deviceStats['devices'] as $d): ?>
                        <div style="display: flex; justify-content: space-between; align-items: center; padding: 0.5rem 0; border-bottom: 1px solid var(--border-subtle); font-size: 0.88rem;">
                            <span><i class="fa-solid <?= $d['device_type'] === 'Mobile' ? 'fa-mobile-screen' : 'fa-laptop' ?>" style="color: var(--primary); margin-right: 0.5rem;"></i> <?= e($d['device_type']) ?></span>
                            <strong><?= number_format((int)$d['count']) ?> hits</strong>
                        </div>
                    <?php endforeach; ?>
                </div>

                <div>
                    <div style="font-size: 0.8rem; font-weight: 700; color: var(--text-muted); margin-bottom: 0.5rem; text-transform: uppercase;">Top Browsers</div>
                    <?php foreach ($deviceStats['browsers'] as $b): ?>
                        <div style="display: flex; justify-content: space-between; align-items: center; padding: 0.5rem 0; border-bottom: 1px solid var(--border-subtle); font-size: 0.88rem;">
                            <span><i class="fa-brands fa-chrome" style="color: #f59e0b; margin-right: 0.5rem;"></i> <?= e($b['browser']) ?></span>
                            <strong><?= number_format((int)$b['count']) ?> hits</strong>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- Recent Live Visits Feed -->
            <div class="admin-table-card" style="margin-bottom: 0;">
                <h3 style="font-size: 1.1rem; font-weight: 800; margin-bottom: 1.25rem; color: var(--text-primary); display: flex; align-items: center; justify-content: space-between;">
                    <span><i class="fa-solid fa-clock-rotate-left" style="color: var(--primary);"></i> Live Real-Time Visits</span>
                    <span style="font-size: 0.75rem; font-weight: 700; color: #10b981;">Auto-synced</span>
                </h3>

                <div style="max-height: 380px; overflow-y: auto;">
                    <?php foreach ($recentVisits as $rv): ?>
                        <div style="display: flex; justify-content: space-between; align-items: flex-start; padding: 0.65rem 0; border-bottom: 1px solid var(--border-subtle); font-size: 0.85rem;">
                            <div>
                                <div style="font-weight: 700; color: var(--text-primary);"><?= e($rv['page_url']) ?></div>
                                <div style="font-size: 0.75rem; color: var(--text-muted);">
                                    <span style="font-family: var(--font-mono);"><?= substr(e($rv['visitor_id']), 0, 10) ?>...</span> • <?= e($rv['device_type']) ?> (<?= e($rv['browser']) ?>)
                                </div>
                            </div>
                            <span style="font-size: 0.72rem; color: var(--text-muted); white-space: nowrap;">
                                <?= date('H:i:s', strtotime($rv['created_at'])) ?>
                            </span>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>

    </main>

    <!-- Modal for User Journey Details -->
    <div id="userJourneyModal" class="admin-modal-overlay">
        <div class="admin-modal">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem; border-bottom: 1px solid var(--border-subtle); padding-bottom: 1rem;">
                <h3 style="font-size: 1.25rem; font-weight: 800; color: var(--text-primary);">
                    <i class="fa-solid fa-route" style="color: var(--primary);"></i> Single User Activity Path
                </h3>
                <button type="button" onclick="closeUserJourneyModal()" style="background: none; border: none; font-size: 1.2rem; cursor: pointer; color: var(--text-muted);">&times;</button>
            </div>
            
            <div id="userModalContent">
                <div style="text-align: center; padding: 2rem;">
                    <i class="fa-solid fa-spinner fa-spin" style="font-size: 2rem; color: var(--primary);"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Admin Search Script -->
    <script>
        const searchInput = document.getElementById('pageSearchInput');
        if (searchInput) {
            searchInput.addEventListener('input', (e) => {
                const term = e.target.value.toLowerCase().trim();
                const rows = document.querySelectorAll('.page-row');
                rows.forEach(r => {
                    const text = r.getAttribute('data-page') || '';
                    r.style.display = text.includes(term) ? '' : 'none';
                });
            });
        }

        async function showUserJourney(visitorId) {
            const modal = document.getElementById('userJourneyModal');
            const content = document.getElementById('userModalContent');
            modal.classList.add('active');
            content.innerHTML = '<div style="text-align: center; padding: 2rem;"><i class="fa-solid fa-spinner fa-spin" style="font-size: 2rem; color: var(--primary);"></i><div style="margin-top: 0.5rem; color: var(--text-muted);">Loading user session journey...</div></div>';

            try {
                const res = await fetch(`<?= url("api/index.php") ?>?action=admin_user_history&visitor_id=${encodeURIComponent(visitorId)}`);
                const data = await res.json();
                if (!data.success || !data.history.length) {
                    content.innerHTML = '<div style="padding: 1.5rem; text-align: center; color: var(--text-muted);">No activity records found for this user.</div>';
                    return;
                }

                content.innerHTML = `
                    <div style="margin-bottom: 1rem; font-size: 0.85rem; background: var(--bg-secondary); padding: 0.75rem 1rem; border-radius: var(--radius-md);">
                        <strong>Visitor ID:</strong> <code>${escapeHtml(visitorId)}</code><br>
                        <strong>Total Page Hits:</strong> ${data.history.length} views
                    </div>
                    <div style="display: flex; flex-direction: column; gap: 0.75rem;">
                        ${data.history.map((h, i) => `
                            <div style="display: flex; align-items: flex-start; gap: 0.75rem; padding: 0.75rem; background: var(--bg-body); border: 1px solid var(--border-subtle); border-radius: var(--radius-md);">
                                <span style="font-weight: 800; font-size: 0.85rem; color: var(--primary); width: 22px;">#${i + 1}</span>
                                <div style="flex: 1;">
                                    <div style="font-weight: 700; font-size: 0.9rem; color: var(--text-primary);">${escapeHtml(h.page_url)}</div>
                                    <div style="font-size: 0.75rem; color: var(--text-muted); margin-top: 0.2rem;">
                                        Device: ${escapeHtml(h.device_type)} (${escapeHtml(h.browser)}) • ${escapeHtml(h.created_at)}
                                    </div>
                                </div>
                            </div>
                        `).join('')}
                    </div>
                `;
            } catch (e) {
                content.innerHTML = '<div style="color: #ef4444; padding: 1.5rem; text-align: center;">Failed to load user history.</div>';
            }
        }

        function closeUserJourneyModal() {
            document.getElementById('userJourneyModal').classList.remove('active');
        }

        function escapeHtml(text) {
            const div = document.createElement('div');
            div.innerText = text || '';
            return div.innerHTML;
        }
    </script>
</body>
</html>
