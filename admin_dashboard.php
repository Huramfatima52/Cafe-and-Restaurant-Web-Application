<?php
include('connection.php');
session_start();

if (!isset($_SESSION['admin_id'])) {
    header("Location: admin_login.php");
    exit();
}

// Safe helper: returns 0 if query fails
function safe_count($conn, $sql) {
    $r = mysqli_query($conn, $sql);
    if (!$r) return 0;
    $row = mysqli_fetch_assoc($r);
    return $row ? (int)array_values($row)[0] : 0;
}

// Live counts safely handles missing tables
$total_products  = safe_count($conn, "SELECT COUNT(*) AS c FROM products");
$total_cats      = safe_count($conn, "SELECT COUNT(DISTINCT category) AS c FROM products");

// Try common order table names
$order_table = null;
foreach (['orders','order','tbl_orders','tbl_order'] as $t) {
    if (mysqli_query($conn, "SELECT 1 FROM `$t` LIMIT 1")) { $order_table = $t; break; }
}
$total_orders   = $order_table ? safe_count($conn, "SELECT COUNT(*) AS c FROM `$order_table`") : 0;
$pending_orders = $order_table ? safe_count($conn, "SELECT COUNT(*) AS c FROM `$order_table` WHERE LOWER(status)='pending'") : 0;

// Revenue — try common price column names
$total_revenue = 0;
if ($order_table) {
    foreach (['total_price','total','amount','grand_total'] as $col) {
        $rq = mysqli_query($conn, "SELECT SUM(`$col`) AS t FROM `$order_table`");
        if ($rq) { $rv = mysqli_fetch_assoc($rq); $total_revenue = (float)($rv['t'] ?? 0); break; }
    }
}

// Try common customer/user table names
$user_table = null;
foreach (['users','user','customers','customer','tbl_users'] as $t) {
    if (mysqli_query($conn, "SELECT 1 FROM `$t` LIMIT 1")) { $user_table = $t; break; }
}
$total_customers = $user_table ? safe_count($conn, "SELECT COUNT(*) AS c FROM `$user_table`") : 0;

// Recent orders with customer name
$recent_orders = null;
if ($order_table && $user_table) {
    $recent_orders = mysqli_query($conn,
        "SELECT o.*, u.fullname FROM `$order_table` o
         LEFT JOIN `$user_table` u ON o.user_id = u.id
         ORDER BY o.id DESC LIMIT 8"
    );
} elseif ($order_table) {
    $recent_orders = mysqli_query($conn, "SELECT * FROM `$order_table` ORDER BY id DESC LIMIT 8");
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard — Brew&amp;Bite</title>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,700;0,900;1,700&family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
    /* ══ TOKENS ══ */
    :root {
        --amber:       #FF8C00;
        --amber-light: #FFB347;
        --amber-pale:  #FFF3E0;
        --ember:       #FF6B35;
        --ember-dark:  #E65C2E;
        --cream:       #FFF8F0;
        --charcoal:    #2D2D2D;
        --muted:       #888;
        --white:       #ffffff;
        --sidebar-w:   260px;
        --nav-h:       68px;
        --radius:      16px;
        --transition:  .3s cubic-bezier(.4,0,.2,1);
        --card-shadow: 0 4px 24px rgba(255,140,0,.1);
    }
    *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
    html { scroll-behavior: smooth; }
    body { font-family: 'Poppins', sans-serif; background: var(--cream); color: var(--charcoal); display: flex; min-height: 100vh; overflow-x: hidden; }

    /* ══ SIDEBAR ══ */
    .sidebar {
        width: var(--sidebar-w);
        background: #1a1a1a;
        min-height: 100vh;
        position: fixed;
        top: 0; left: 0;
        display: flex;
        flex-direction: column;
        z-index: 300;
        transition: transform var(--transition);
    }
    .sidebar-brand {
        padding: 28px 28px 20px;
        border-bottom: 1px solid rgba(255,255,255,.07);
    }
    .sidebar-brand a {
        font-family: 'Playfair Display', serif;
        font-weight: 900;
        font-size: 1.5rem;
        color: var(--amber);
        text-decoration: none;
        letter-spacing: -.5px;
    }
    .sidebar-brand a span { color: var(--ember); }
    .sidebar-brand small {
        display: block;
        font-size: .68rem;
        color: rgba(255,255,255,.35);
        font-weight: 500;
        letter-spacing: 2px;
        text-transform: uppercase;
        margin-top: 3px;
    }
    .sidebar-nav { flex: 1; padding: 16px 0; overflow-y: auto; }
    .nav-section-label {
        font-size: .62rem;
        font-weight: 700;
        letter-spacing: 2px;
        text-transform: uppercase;
        color: rgba(255,255,255,.25);
        padding: 16px 28px 8px;
    }
    .sidebar-nav a {
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 11px 28px;
        color: rgba(255,255,255,.55);
        text-decoration: none;
        font-size: .84rem;
        font-weight: 500;
        transition: var(--transition);
        position: relative;
        border-radius: 0;
    }
    .sidebar-nav a i {
        width: 18px;
        text-align: center;
        font-size: .9rem;
        flex-shrink: 0;
    }
    .sidebar-nav a:hover {
        color: #fff;
        background: rgba(255,255,255,.06);
    }
    .sidebar-nav a.active {
        color: var(--amber);
        background: rgba(255,140,0,.1);
    }
    .sidebar-nav a.active::before {
        content: '';
        position: absolute;
        left: 0; top: 0; bottom: 0;
        width: 3px;
        background: linear-gradient(180deg, var(--amber), var(--ember));
        border-radius: 0 3px 3px 0;
    }
    .sidebar-footer {
        padding: 20px 28px;
        border-top: 1px solid rgba(255,255,255,.07);
    }
    .sidebar-footer a {
        display: flex;
        align-items: center;
        gap: 10px;
        color: rgba(255,255,255,.4);
        font-size: .82rem;
        text-decoration: none;
        transition: var(--transition);
    }
    .sidebar-footer a:hover { color: var(--ember); }

    /* ══ MAIN ══ */
    .main-wrap {
        margin-left: var(--sidebar-w);
        flex: 1;
        display: flex;
        flex-direction: column;
        min-height: 100vh;
    }

    /* ══ TOPBAR ══ */
    .topbar {
        height: var(--nav-h);
        background: rgba(255,255,255,.95);
        backdrop-filter: blur(18px);
        border-bottom: 1px solid rgba(255,179,71,.2);
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 0 36px;
        position: sticky;
        top: 0;
        z-index: 200;
        box-shadow: 0 2px 12px rgba(255,140,0,.07);
    }
    .topbar-left { display: flex; flex-direction: column; }
    .topbar-left h2 {
        font-family: 'Playfair Display', serif;
        font-size: 1.4rem;
        font-weight: 900;
        color: var(--charcoal);
        line-height: 1;
    }
    .topbar-left span {
        font-size: .72rem;
        color: var(--muted);
        margin-top: 3px;
    }
    .topbar-right { display: flex; align-items: center; gap: 14px; }
    .topbar-badge {
        display: flex; align-items: center; gap: 7px;
        background: var(--amber-pale);
        border: 1px solid rgba(255,179,71,.3);
        border-radius: 50px;
        padding: 7px 16px;
        font-size: .8rem;
        font-weight: 600;
        color: var(--amber);
        text-decoration: none;
        transition: var(--transition);
    }
    .topbar-badge:hover { background: var(--amber); color: #fff; }
    .topbar-badge .dot {
        width: 8px; height: 8px;
        border-radius: 50%;
        background: var(--ember);
        animation: pulse 2s infinite;
    }
    @keyframes pulse {
        0%,100% { opacity: 1; transform: scale(1); }
        50%      { opacity: .6; transform: scale(1.3); }
    }
    .admin-avatar {
        width: 38px; height: 38px;
        border-radius: 50%;
        background: linear-gradient(135deg, var(--amber), var(--ember));
        display: flex; align-items: center; justify-content: center;
        color: #fff; font-weight: 700; font-size: .9rem;
        cursor: pointer;
        box-shadow: 0 4px 12px rgba(255,107,53,.3);
    }

    /* ══ CONTENT ══ */
    .content { padding: 36px; flex: 1; }

    /* ══ STAT CARDS ══ */
    .stat-grid {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 20px;
        margin-bottom: 32px;
    }
    .stat-card {
        background: var(--white);
        border: 1px solid rgba(255,179,71,.15);
        border-radius: var(--radius);
        padding: 24px 22px;
        box-shadow: var(--card-shadow);
        transition: var(--transition);
        position: relative;
        overflow: hidden;
        cursor: default;
    }
    .stat-card::before {
        content: '';
        position: absolute;
        top: 0; left: 0; right: 0;
        height: 3px;
        background: linear-gradient(90deg, var(--amber), var(--ember));
        transform: scaleX(0);
        transform-origin: left;
        transition: var(--transition);
    }
    .stat-card:hover { transform: translateY(-4px); box-shadow: 0 14px 36px rgba(255,140,0,.16); }
    .stat-card:hover::before { transform: scaleX(1); }
    .stat-card-icon {
        width: 46px; height: 46px;
        border-radius: 12px;
        background: var(--amber-pale);
        display: flex; align-items: center; justify-content: center;
        font-size: 1.1rem;
        color: var(--amber);
        margin-bottom: 16px;
        transition: var(--transition);
    }
    .stat-card:hover .stat-card-icon {
        background: linear-gradient(135deg, var(--amber), var(--ember));
        color: #fff;
        transform: rotate(-5deg) scale(1.08);
    }
    .stat-card-label {
        font-size: .75rem;
        font-weight: 600;
        color: var(--muted);
        text-transform: uppercase;
        letter-spacing: 1px;
        margin-bottom: 6px;
    }
    .stat-card-num {
        font-family: 'Playfair Display', serif;
        font-size: 2rem;
        font-weight: 900;
        color: var(--charcoal);
        line-height: 1;
    }
    .stat-card-sub {
        font-size: .72rem;
        color: var(--muted);
        margin-top: 6px;
    }
    .stat-card-sub .up { color: #22c55e; font-weight: 600; }
    .stat-card-sub .warn { color: var(--ember); font-weight: 600; }

    /* ══ SECTION HEADER ══ */
    .section-hdr {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 18px;
    }
    .section-hdr h3 {
        font-family: 'Playfair Display', serif;
        font-size: 1.2rem;
        font-weight: 900;
        color: var(--charcoal);
    }
    .section-hdr a {
        font-size: .78rem;
        font-weight: 600;
        color: var(--amber);
        text-decoration: none;
        display: flex;
        align-items: center;
        gap: 5px;
        transition: var(--transition);
    }
    .section-hdr a:hover { color: var(--ember); }

    /* ══ ORDERS TABLE ══ */
    .table-wrap {
        background: var(--white);
        border: 1px solid rgba(255,179,71,.15);
        border-radius: var(--radius);
        box-shadow: var(--card-shadow);
        overflow: hidden;
    }
    table { width: 100%; border-collapse: collapse; }
    thead tr { background: linear-gradient(135deg, var(--amber), var(--ember)); }
    thead th {
        padding: 13px 18px;
        text-align: left;
        font-size: .74rem;
        font-weight: 700;
        color: #fff;
        letter-spacing: .8px;
        text-transform: uppercase;
    }
    tbody tr {
        border-bottom: 1px solid rgba(255,179,71,.1);
        transition: var(--transition);
    }
    tbody tr:last-child { border-bottom: none; }
    tbody tr:hover { background: var(--amber-pale); }
    tbody td {
        padding: 13px 18px;
        font-size: .82rem;
        color: var(--charcoal);
        vertical-align: middle;
    }
    .order-id { font-weight: 700; color: var(--amber); }
    .status-badge {
        display: inline-block;
        padding: 3px 10px;
        border-radius: 50px;
        font-size: .68rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: .5px;
    }
    .status-pending  { background: #FFF3CD; color: #856404; }
    .status-completed, .status-delivered { background: #D1FAE5; color: #065F46; }
    .status-cancelled { background: #FEE2E2; color: #991B1B; }
    .status-processing { background: #DBEAFE; color: #1E40AF; }
    .status-default { background: var(--amber-pale); color: var(--amber); }

    /* ══ QUICK ACTIONS ══ */
    .quick-grid {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 14px;
        margin-bottom: 32px;
    }
    .quick-card {
        background: var(--white);
        border: 1px solid rgba(255,179,71,.15);
        border-radius: var(--radius);
        padding: 20px 22px;
        display: flex;
        align-items: center;
        gap: 16px;
        text-decoration: none;
        transition: var(--transition);
        box-shadow: var(--card-shadow);
    }
    .quick-card:hover {
        transform: translateY(-3px);
        box-shadow: 0 12px 30px rgba(255,140,0,.16);
        border-color: var(--amber-light);
    }
    .quick-card-icon {
        width: 44px; height: 44px;
        border-radius: 12px;
        background: linear-gradient(135deg, var(--amber), var(--ember));
        display: flex; align-items: center; justify-content: center;
        color: #fff;
        font-size: 1rem;
        flex-shrink: 0;
        box-shadow: 0 4px 12px rgba(255,107,53,.28);
        transition: var(--transition);
    }
    .quick-card:hover .quick-card-icon { transform: rotate(-6deg) scale(1.1); }
    .quick-card-text strong {
        display: block;
        font-size: .88rem;
        font-weight: 700;
        color: var(--charcoal);
        margin-bottom: 2px;
    }
    .quick-card-text span {
        font-size: .74rem;
        color: var(--muted);
    }

    /* ══ TWO COL LAYOUT ══ */
    .two-col { display: grid; grid-template-columns: 1fr 340px; gap: 24px; }

    /* ══ RESPONSIVE ══ */
    @media (max-width: 1200px) {
        .stat-grid { grid-template-columns: repeat(2, 1fr); }
        .two-col { grid-template-columns: 1fr; }
    }
    @media (max-width: 900px) {
        .sidebar { transform: translateX(-100%); }
        .sidebar.open { transform: translateX(0); }
        .main-wrap { margin-left: 0; }
        .stat-grid { grid-template-columns: repeat(2, 1fr); }
        .content { padding: 20px; }
    }
    @media (max-width: 500px) {
        .stat-grid { grid-template-columns: 1fr; }
        .quick-grid { grid-template-columns: 1fr; }
    }

    /* ══ REVEAL ══ */
    .reveal { opacity: 0; transform: translateY(18px); transition: opacity .6s ease, transform .6s ease; }
    .reveal.visible { opacity: 1; transform: translateY(0); }
    .reveal-delay-1 { transition-delay: .08s; }
    .reveal-delay-2 { transition-delay: .16s; }
    .reveal-delay-3 { transition-delay: .24s; }
    .reveal-delay-4 { transition-delay: .32s; }

    /* mobile hamburger */
    .mob-toggle {
        display: none;
        background: none;
        border: none;
        cursor: pointer;
        flex-direction: column;
        gap: 5px;
        padding: 6px;
    }
    .mob-toggle span { width: 22px; height: 2px; background: var(--charcoal); border-radius: 2px; display: block; }
    @media (max-width: 900px) { .mob-toggle { display: flex; } }
    </style>
</head>
<body>

<!-- ══ SIDEBAR ══ -->
<aside class="sidebar" id="sidebar">
    <div class="sidebar-brand">
        <a href="admin_dashboard.php">Brew<span>&</span>Bite</a>
        <small>Admin Panel</small>
    </div>

    <nav class="sidebar-nav">
        <div class="nav-section-label">Main</div>
        <a href="admin_dashboard.php" class="active"><i class="fas fa-chart-line"></i> Dashboard</a>
        <a href="admin_menu.php"><i class="fas fa-utensils"></i> Menu</a>
        <a href="order_management.php"><i class="fas fa-shopping-bag"></i> Orders</a>
        <a href="category_management.php"><i class="fas fa-layer-group"></i> Categories</a>

        <div class="nav-section-label">Users</div>
        <a href="customer_info.php"><i class="fas fa-users"></i> Customers</a>
        <a href="feedback_review.php"><i class="fas fa-star"></i> Reviews</a>

        <div class="nav-section-label">Reports</div>
        <a href="admin_analytics.php"><i class="fas fa-chart-bar"></i> Analytics</a>
    </nav>

    <div class="sidebar-footer">
        <a href="admin-profile.php"><i class="fas fa-user-circle"></i> Profile</a>
    </div>
</aside>

<!-- ══ MAIN ══ -->
<div class="main-wrap">

    <!-- Topbar -->
    <div class="topbar">
        <div style="display:flex;align-items:center;gap:14px;">
            <button class="mob-toggle" id="mobToggle" aria-label="Menu">
                <span></span><span></span><span></span>
            </button>
            <div class="topbar-left">
                <h2>Dashboard</h2>
                <span><?php echo date('l, d F Y'); ?></span>
            </div>
        </div>
        <div class="topbar-right">
            <?php if($pending_orders > 0): ?>
            <a href="order_management.php" class="topbar-badge">
                <span class="dot"></span>
                <?php echo $pending_orders; ?> Pending
            </a>
            <?php endif; ?>
            <a href="admin-profile.php" style="text-decoration:none;">
                <div class="admin-avatar">A</div>
            </a>
        </div>
    </div>

    <!-- Content -->
    <div class="content">

        <!-- Stat Cards -->
        <div class="stat-grid">
            <div class="stat-card reveal">
                <div class="stat-card-icon"><i class="fas fa-box-open"></i></div>
                <div class="stat-card-label">Total Items</div>
                <div class="stat-card-num"><?php echo number_format($total_products); ?></div>
                <div class="stat-card-sub"><span class="up"><i class="fas fa-arrow-up"></i> Active</span> in menu</div>
            </div>
            <div class="stat-card reveal reveal-delay-1">
                <div class="stat-card-icon"><i class="fas fa-layer-group"></i></div>
                <div class="stat-card-label">Categories</div>
                <div class="stat-card-num"><?php echo number_format($total_cats); ?></div>
                <div class="stat-card-sub">Menu sections</div>
            </div>
            <div class="stat-card reveal reveal-delay-2">
                <div class="stat-card-icon"><i class="fas fa-shopping-bag"></i></div>
                <div class="stat-card-label">Total Orders</div>
                <div class="stat-card-num"><?php echo number_format($total_orders); ?></div>
                <div class="stat-card-sub">
                    <?php if($pending_orders > 0): ?>
                    <span class="warn"><i class="fas fa-clock"></i> <?php echo $pending_orders; ?> pending</span>
                    <?php else: ?>
                    <span class="up">All fulfilled</span>
                    <?php endif; ?>
                </div>
            </div>
            <div class="stat-card reveal reveal-delay-3">
                <div class="stat-card-icon"><i class="fas fa-users"></i></div>
                <div class="stat-card-label">Customers</div>
                <div class="stat-card-num"><?php echo number_format($total_customers); ?></div>
                <div class="stat-card-sub"><span class="up"><i class="fas fa-arrow-up"></i> Registered</span> users</div>
            </div>
        </div>

        <!-- Revenue card full width -->
        <?php if($total_revenue > 0): ?>
        <div class="stat-card reveal" style="margin-bottom:32px; display:flex; align-items:center; gap:24px; padding:22px 28px;">
            <div class="stat-card-icon" style="width:54px;height:54px;font-size:1.3rem;flex-shrink:0;"><i class="fas fa-coins"></i></div>
            <div>
                <div class="stat-card-label">Total Revenue</div>
                <div class="stat-card-num" style="font-size:2.2rem;">Rs <?php echo number_format($total_revenue); ?></div>
                <div class="stat-card-sub"><span class="up"><i class="fas fa-arrow-up"></i> All time earnings</span></div>
            </div>
        </div>
        <?php endif; ?>
    </div><!-- /content -->
</div><!-- /main-wrap -->

<script>
// Mobile sidebar toggle
document.getElementById('mobToggle').addEventListener('click', () => {
    document.getElementById('sidebar').classList.toggle('open');
});

// Scroll reveal
const obs = new IntersectionObserver(entries => {
    entries.forEach(e => { if(e.isIntersecting) { e.target.classList.add('visible'); obs.unobserve(e.target); } });
}, { threshold: 0.1 });
document.querySelectorAll('.reveal').forEach(el => obs.observe(el));

// Animated stat counters
function animCount(el, target) {
    let cur = 0;
    const step = target / 60;
    const t = setInterval(() => {
        cur = Math.min(cur + step, target);
        el.textContent = target > 999
            ? (cur/1000).toFixed(1) + 'K'
            : Math.floor(cur).toLocaleString();
        if (cur >= target) { el.textContent = target > 999 ? (target/1000).toFixed(1)+'K' : target.toLocaleString(); clearInterval(t); }
    }, 16);
}
window.addEventListener('load', () => {
    document.querySelectorAll('.stat-card-num').forEach(el => {
        const raw = el.textContent.replace(/[^0-9.]/g,'');
        if (raw) animCount(el, parseFloat(raw));
    });
});
</script>
</body>
</html>