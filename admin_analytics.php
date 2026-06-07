<?php
include('connection.php');
// session_start();

// if (!isset($_SESSION['admin_id'])) {
//     header("Location: admin_login.php");
//     exit();
// }

/* ── Safe query helper ── */
function safe_val($conn, $sql, $default = 0) {
    $r = mysqli_query($conn, $sql);
    if (!$r) return $default;
    $row = mysqli_fetch_assoc($r);
    return $row ? array_values($row)[0] : $default;
}

/* ── Detect table names ── */
$order_table = null;
foreach (['orders','order','tbl_orders'] as $t) {
    if (mysqli_query($conn, "SELECT 1 FROM `$t` LIMIT 1")) { $order_table = $t; break; }
}

$user_table = null;
foreach (['users','customers','tbl_users'] as $t) {
    if (mysqli_query($conn, "SELECT 1 FROM `$t` LIMIT 1")) { $user_table = $t; break; }
}

/* ── KPI Numbers ── */
$total_products  = (int)safe_val($conn, "SELECT COUNT(*) FROM products");
$total_customers = $user_table ? (int)safe_val($conn, "SELECT COUNT(*) FROM `$user_table`") : 0;
$total_orders    = $order_table ? (int)safe_val($conn, "SELECT COUNT(*) FROM `$order_table`") : 0;
$pending_orders  = $order_table ? (int)safe_val($conn, "SELECT COUNT(*) FROM `$order_table` WHERE LOWER(status)='pending'") : 0;

$total_revenue = 0;
if ($order_table) {
    foreach (['total','total_price','amount','grand_total'] as $col) {
        $rq = mysqli_query($conn, "SELECT SUM(`$col`) AS t FROM `$order_table`");
        if ($rq) { $rv = mysqli_fetch_assoc($rq); $total_revenue = (float)($rv['t'] ?? 0); break; }
    }
}

$avg_order = $total_orders > 0 ? $total_revenue / $total_orders : 0;

/* ── Revenue Growth (this month vs last month) ── */
$rev_this  = 0; $rev_last  = 0;
if ($order_table) {
    foreach (['total','total_price','amount','grand_total'] as $col) {
        $q1 = mysqli_query($conn, "SELECT SUM(`$col`) AS t FROM `$order_table` WHERE MONTH(order_date)=MONTH(NOW()) AND YEAR(order_date)=YEAR(NOW())");
        if ($q1) { $rv = mysqli_fetch_assoc($q1); $rev_this = (float)($rv['t'] ?? 0); }
        $q2 = mysqli_query($conn, "SELECT SUM(`$col`) AS t FROM `$order_table` WHERE MONTH(order_date)=MONTH(NOW())-1 AND YEAR(order_date)=YEAR(NOW())");
        if ($q2) { $rv = mysqli_fetch_assoc($q2); $rev_last = (float)($rv['t'] ?? 0); }
        if ($q1) break;
    }
}
$growth_pct = $rev_last > 0 ? round((($rev_this - $rev_last) / $rev_last) * 100, 1) : 0;

/* ── Daily orders last 7 days ── */
$daily_labels  = [];
$daily_revenue = [];
$daily_orders  = [];

for ($i = 6; $i >= 0; $i--) {
    $date  = date('Y-m-d', strtotime("-$i days"));
    $label = date('D', strtotime("-$i days"));
    $daily_labels[] = $label;

    if ($order_table) {
        $cnt = (int)safe_val($conn, "SELECT COUNT(*) FROM `$order_table` WHERE DATE(order_date)='$date'");
        $daily_orders[] = $cnt;

        $rev = 0;
        foreach (['total','total_price','amount'] as $col) {
            $rq = mysqli_query($conn, "SELECT SUM(`$col`) AS t FROM `$order_table` WHERE DATE(order_date)='$date'");
            if ($rq) { $rv = mysqli_fetch_assoc($rq); $rev = (float)($rv['t'] ?? 0); break; }
        }
        $daily_revenue[] = $rev;
    } else {
        $daily_orders[]  = 0;
        $daily_revenue[] = 0;
    }
}

/* ── Monthly revenue last 6 months ── */
$monthly_labels  = [];
$monthly_revenue = [];
for ($i = 5; $i >= 0; $i--) {
    $monthly_labels[]  = date('M', strtotime("-$i months"));
    $rev = 0;
    if ($order_table) {
        $m = date('m', strtotime("-$i months"));
        $y = date('Y', strtotime("-$i months"));
        foreach (['total','total_price','amount'] as $col) {
            $rq = mysqli_query($conn, "SELECT SUM(`$col`) AS t FROM `$order_table` WHERE MONTH(order_date)=$m AND YEAR(order_date)=$y");
            if ($rq) { $rv = mysqli_fetch_assoc($rq); $rev = (float)($rv['t'] ?? 0); break; }
        }
    }
    $monthly_revenue[] = $rev;
}

/* ── Top 5 Products ── */
$top_products = [];
if ($order_table) {
    // items column stores "ProductName (xQty), ..." — parse in PHP
    $all_items_q = mysqli_query($conn, "SELECT items FROM `$order_table`");
    $item_counts = [];
    if ($all_items_q) {
        while ($row = mysqli_fetch_assoc($all_items_q)) {
            preg_match_all('/([^,\(]+)\s*\(x(\d+)\)/', $row['items'] ?? '', $m);
            foreach ($m[1] as $k => $name) {
                $name = trim($name);
                $qty  = (int)$m[2][$k];
                $item_counts[$name] = ($item_counts[$name] ?? 0) + $qty;
            }
        }
    }
    arsort($item_counts);
    $top_products = array_slice($item_counts, 0, 5, true);
}

/* ── Order status breakdown ── */
$status_data = [];
if ($order_table) {
    $sq = mysqli_query($conn, "SELECT status, COUNT(*) as cnt FROM `$order_table` GROUP BY status ORDER BY cnt DESC");
    if ($sq) while ($r = mysqli_fetch_assoc($sq)) $status_data[] = $r;
}

/* ── Recent reviews ── */
$recent_reviews = [];
$rev_table_exists = mysqli_query($conn, "SELECT 1 FROM reviews LIMIT 1");
if ($rev_table_exists) {
    $rvq = mysqli_query($conn,
        "SELECT r.rating, r.comment, r.created_at, u.fullname, p.item_name
         FROM reviews r
         JOIN users u ON r.user_id = u.id
         JOIN products p ON r.product_id = p.id
         ORDER BY r.created_at DESC LIMIT 5"
    );
    if ($rvq) while ($r = mysqli_fetch_assoc($rvq)) $recent_reviews[] = $r;
}

$avg_rating = 0;
if ($rev_table_exists) {
    $avg_rating = round((float)safe_val($conn, "SELECT AVG(rating) FROM reviews"), 1);
}
$total_reviews = $rev_table_exists ? (int)safe_val($conn, "SELECT COUNT(*) FROM reviews") : 0;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Analytics — Brew&amp;Bite Admin</title>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,700;0,900;1,700&family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    <style>
    /* ══ TOKENS — identical to admin_dashboard ══ */
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

    /* ══ SIDEBAR — copy of dashboard ══ */
    .sidebar {
        width: var(--sidebar-w); background: #1a1a1a; min-height: 100vh;
        position: fixed; top: 0; left: 0;
        display: flex; flex-direction: column; z-index: 300;
        transition: transform var(--transition);
    }
    .sidebar-brand { padding: 28px 28px 20px; border-bottom: 1px solid rgba(255,255,255,.07); }
    .sidebar-brand a { font-family: 'Playfair Display', serif; font-weight: 900; font-size: 1.5rem; color: var(--amber); text-decoration: none; letter-spacing: -.5px; }
    .sidebar-brand a span { color: var(--ember); }
    .sidebar-brand small { display: block; font-size: .68rem; color: rgba(255,255,255,.35); font-weight: 500; letter-spacing: 2px; text-transform: uppercase; margin-top: 3px; }
    .sidebar-nav { flex: 1; padding: 16px 0; overflow-y: auto; }
    .nav-section-label { font-size: .62rem; font-weight: 700; letter-spacing: 2px; text-transform: uppercase; color: rgba(255,255,255,.25); padding: 16px 28px 8px; }
    .sidebar-nav a { display: flex; align-items: center; gap: 12px; padding: 11px 28px; color: rgba(255,255,255,.55); text-decoration: none; font-size: .84rem; font-weight: 500; transition: var(--transition); position: relative; }
    .sidebar-nav a i { width: 18px; text-align: center; font-size: .9rem; flex-shrink: 0; }
    .sidebar-nav a:hover { color: #fff; background: rgba(255,255,255,.06); }
    .sidebar-nav a.active { color: var(--amber); background: rgba(255,140,0,.1); }
    .sidebar-nav a.active::before { content: ''; position: absolute; left: 0; top: 0; bottom: 0; width: 3px; background: linear-gradient(180deg, var(--amber), var(--ember)); border-radius: 0 3px 3px 0; }
    .sidebar-footer { padding: 20px 28px; border-top: 1px solid rgba(255,255,255,.07); }
    .sidebar-footer a { display: flex; align-items: center; gap: 10px; color: rgba(255,255,255,.4); font-size: .82rem; text-decoration: none; transition: var(--transition); }
    .sidebar-footer a:hover { color: var(--ember); }

    /* ══ MAIN WRAP ══ */
    .main-wrap { margin-left: var(--sidebar-w); flex: 1; display: flex; flex-direction: column; min-height: 100vh; }

    /* ══ TOPBAR ══ */
    .topbar { height: var(--nav-h); background: rgba(255,255,255,.95); backdrop-filter: blur(18px); border-bottom: 1px solid rgba(255,179,71,.2); display: flex; align-items: center; justify-content: space-between; padding: 0 36px; position: sticky; top: 0; z-index: 200; box-shadow: 0 2px 12px rgba(255,140,0,.07); }
    .topbar-left h2 { font-family: 'Playfair Display', serif; font-size: 1.4rem; font-weight: 900; color: var(--charcoal); line-height: 1; }
    .topbar-left span { font-size: .72rem; color: var(--muted); margin-top: 3px; display: block; }
    .topbar-right { display: flex; align-items: center; gap: 14px; }
    .topbar-badge { display: flex; align-items: center; gap: 7px; background: var(--amber-pale); border: 1px solid rgba(255,179,71,.3); border-radius: 50px; padding: 7px 16px; font-size: .8rem; font-weight: 600; color: var(--amber); text-decoration: none; transition: var(--transition); }
    .topbar-badge:hover { background: var(--amber); color: #fff; }
    .topbar-badge .dot { width: 8px; height: 8px; border-radius: 50%; background: var(--ember); animation: pulse 2s infinite; }
    @keyframes pulse { 0%,100%{opacity:1;transform:scale(1)} 50%{opacity:.6;transform:scale(1.3)} }
    .admin-avatar { width: 38px; height: 38px; border-radius: 50%; background: linear-gradient(135deg,var(--amber),var(--ember)); display: flex; align-items: center; justify-content: center; color: #fff; font-weight: 700; font-size: .9rem; cursor: pointer; box-shadow: 0 4px 12px rgba(255,107,53,.3); }
    .mob-toggle { display: none; background: none; border: none; cursor: pointer; flex-direction: column; gap: 5px; padding: 6px; }
    .mob-toggle span { width: 22px; height: 2px; background: var(--charcoal); border-radius: 2px; display: block; }
    @media(max-width:900px){ .mob-toggle{display:flex} }

    /* ══ CONTENT ══ */
    .content { padding: 36px; flex: 1; }

    /* ══ STAT CARDS ══ */
    .stat-grid { display: grid; grid-template-columns: repeat(4,1fr); gap: 20px; margin-bottom: 32px; }
    .stat-card { background: var(--white); border: 1px solid rgba(255,179,71,.15); border-radius: var(--radius); padding: 22px 20px; box-shadow: var(--card-shadow); transition: var(--transition); position: relative; overflow: hidden; }
    .stat-card::before { content:''; position:absolute; top:0; left:0; right:0; height:3px; background:linear-gradient(90deg,var(--amber),var(--ember)); transform:scaleX(0); transform-origin:left; transition:var(--transition); }
    .stat-card:hover { transform: translateY(-4px); box-shadow: 0 14px 36px rgba(255,140,0,.16); }
    .stat-card:hover::before { transform: scaleX(1); }
    .stat-card-icon { width: 44px; height: 44px; border-radius: 12px; background: var(--amber-pale); display: flex; align-items: center; justify-content: center; font-size: 1.1rem; color: var(--amber); margin-bottom: 14px; transition: var(--transition); }
    .stat-card:hover .stat-card-icon { background: linear-gradient(135deg,var(--amber),var(--ember)); color: #fff; transform: rotate(-5deg) scale(1.08); }
    .stat-card-label { font-size: .72rem; font-weight: 600; color: var(--muted); text-transform: uppercase; letter-spacing: 1px; margin-bottom: 5px; }
    .stat-card-num { font-family: 'Playfair Display', serif; font-size: 1.9rem; font-weight: 900; color: var(--charcoal); line-height: 1; }
    .stat-card-sub { font-size: .71rem; color: var(--muted); margin-top: 5px; }
    .up   { color: #22c55e; font-weight: 600; }
    .down { color: var(--ember); font-weight: 600; }
    .warn { color: #f59e0b; font-weight: 600; }

    /* ══ SECTION HEADER ══ */
    .section-hdr { display: flex; align-items: center; justify-content: space-between; margin-bottom: 16px; }
    .section-hdr h3 { font-family: 'Playfair Display', serif; font-size: 1.15rem; font-weight: 900; color: var(--charcoal); }
    .section-hdr span { font-size: .75rem; color: var(--muted); font-weight: 500; }

    /* ══ CHART CARDS ══ */
    .chart-card { background: var(--white); border: 1px solid rgba(255,179,71,.15); border-radius: var(--radius); padding: 24px; box-shadow: var(--card-shadow); }
    .charts-row { display: grid; grid-template-columns: 1.6fr 1fr; gap: 24px; margin-bottom: 28px; }
    .charts-row-2 { display: grid; grid-template-columns: 1fr 1fr; gap: 24px; margin-bottom: 28px; }

    /* ══ TOP PRODUCTS ══ */
    .product-rank { display: flex; flex-direction: column; gap: 12px; }
    .rank-item { display: flex; align-items: center; gap: 12px; }
    .rank-num { width: 26px; height: 26px; border-radius: 50%; background: linear-gradient(135deg,var(--amber),var(--ember)); color: #fff; font-size: .72rem; font-weight: 700; display: flex; align-items: center; justify-content: center; flex-shrink: 0; }
    .rank-bar-wrap { flex: 1; }
    .rank-name { font-size: .8rem; font-weight: 600; color: var(--charcoal); margin-bottom: 4px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; max-width: 160px; }
    .rank-bar-bg { height: 6px; background: var(--amber-pale); border-radius: 50px; overflow: hidden; }
    .rank-bar-fill { height: 100%; background: linear-gradient(90deg,var(--amber),var(--ember)); border-radius: 50px; transition: width 1s ease; }
    .rank-count { font-size: .78rem; font-weight: 700; color: var(--amber); white-space: nowrap; }

    /* ══ STATUS BREAKDOWN ══ */
    .status-list { display: flex; flex-direction: column; gap: 10px; }
    .status-row { display: flex; align-items: center; justify-content: space-between; padding: 10px 14px; border-radius: 10px; font-size: .82rem; font-weight: 600; }
    .status-row.pending    { background: #FFF3CD; color: #856404; }
    .status-row.completed,
    .status-row.delivered  { background: #D1FAE5; color: #065F46; }
    .status-row.cancelled  { background: #FEE2E2; color: #991B1B; }
    .status-row.processing { background: #DBEAFE; color: #1E40AF; }
    .status-row.default    { background: var(--amber-pale); color: var(--amber); }
    .status-row .count     { font-family: 'Playfair Display', serif; font-size: 1.1rem; }

    /* ══ REVIEWS TABLE ══ */
    .reviews-card { background: var(--white); border: 1px solid rgba(255,179,71,.15); border-radius: var(--radius); box-shadow: var(--card-shadow); overflow: hidden; margin-bottom: 28px; }
    .rv-header { padding: 18px 24px; border-bottom: 1px solid rgba(255,179,71,.12); display: flex; align-items: center; justify-content: space-between; }
    .rv-header h3 { font-family: 'Playfair Display', serif; font-size: 1.1rem; font-weight: 900; color: var(--charcoal); }
    .rv-item { padding: 14px 24px; border-bottom: 1px solid rgba(255,179,71,.08); display: grid; grid-template-columns: 1fr auto; gap: 12px; align-items: start; transition: var(--transition); }
    .rv-item:last-child { border-bottom: none; }
    .rv-item:hover { background: rgba(255,243,224,.4); }
    .rv-product { font-size: .78rem; font-weight: 700; color: var(--amber); margin-bottom: 2px; }
    .rv-user    { font-size: .75rem; color: var(--muted); }
    .rv-comment { font-size: .8rem; color: var(--charcoal); line-height: 1.55; margin-top: 4px; font-style: italic; }
    .rv-stars   { display: flex; gap: 2px; }
    .rv-stars i { font-size: .7rem; color: var(--amber); }
    .rv-stars i.far { color: #ddd; }
    .rv-date    { font-size: .68rem; color: var(--muted); margin-top: 4px; text-align: right; }
    .empty-row  { text-align: center; padding: 36px; color: var(--muted); font-size: .86rem; }

    /* ══ PERFORMANCE SUMMARY ══ */
    .perf-card { background: linear-gradient(135deg,#FF8C00,#FF6B35); border-radius: var(--radius); padding: 28px 32px; color: #fff; position: relative; overflow: hidden; margin-bottom: 28px; }
    .perf-card::before { content:''; position:absolute; right:-40px; top:-40px; width:200px; height:200px; border-radius:50%; background:rgba(255,255,255,.07); pointer-events:none; }
    .perf-card::after  { content:''; position:absolute; left:-20px; bottom:-50px; width:160px; height:160px; border-radius:50%; background:rgba(255,255,255,.05); pointer-events:none; }
    .perf-grid { display: grid; grid-template-columns: repeat(3,1fr); gap: 24px; position: relative; z-index: 1; }
    .perf-item { text-align: center; }
    .perf-num  { font-family: 'Playfair Display', serif; font-size: 2rem; font-weight: 900; color: #fff; line-height: 1; }
    .perf-label{ font-size: .75rem; color: rgba(255,255,255,.8); margin-top: 4px; font-weight: 500; letter-spacing: .5px; }
    .perf-divider { width: 1px; background: rgba(255,255,255,.2); align-self: stretch; }
    .perf-title { font-family: 'Playfair Display', serif; font-size: 1.2rem; font-weight: 900; color: #fff; margin-bottom: 20px; position: relative; z-index: 1; }

    /* ══ RESPONSIVE ══ */
    @media(max-width:1200px){ .stat-grid{grid-template-columns:repeat(2,1fr)} .charts-row{grid-template-columns:1fr} .charts-row-2{grid-template-columns:1fr} .perf-grid{grid-template-columns:repeat(2,1fr)} }
    @media(max-width:900px){ .sidebar{transform:translateX(-100%)} .sidebar.open{transform:translateX(0)} .main-wrap{margin-left:0} .content{padding:20px} }
    @media(max-width:600px){ .stat-grid{grid-template-columns:1fr} .perf-grid{grid-template-columns:1fr} }

    /* ══ REVEAL ══ */
    .reveal { opacity:0; transform:translateY(18px); transition:opacity .6s ease,transform .6s ease; }
    .reveal.visible { opacity:1; transform:translateY(0); }
    .reveal-delay-1{transition-delay:.08s} .reveal-delay-2{transition-delay:.16s}
    .reveal-delay-3{transition-delay:.24s} .reveal-delay-4{transition-delay:.32s}
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
        <a href="admin_dashboard.php"><i class="fas fa-chart-line"></i> Dashboard</a>
        <a href="admin_menu.php"><i class="fas fa-utensils"></i> Menu</a>
        <a href="order_management.php"><i class="fas fa-shopping-bag"></i> Orders</a>
        <a href="category_management.php"><i class="fas fa-layer-group"></i> Categories</a>
        <div class="nav-section-label">Users</div>
        <a href="customer_info.php"><i class="fas fa-users"></i> Customers</a>
        <a href="feedback_review.php"><i class="fas fa-star"></i> Reviews</a>
        <div class="nav-section-label">Reports</div>
        <a href="admin_analytics.php" class="active"><i class="fas fa-chart-bar"></i> Analytics</a>
    </nav>
    <div class="sidebar-footer">
        <a href="admin-profile.php"><i class="fas fa-user-circle"></i> Profile</a>
    </div>
</aside>

<!-- ══ MAIN ══ -->
<div class="main-wrap">

    <!-- Topbar -->
    <div class="topbar">
        <div style="display:flex;align-items:center;gap:14px">
            <button class="mob-toggle" id="mobToggle" aria-label="Menu">
                <span></span><span></span><span></span>
            </button>
            <div class="topbar-left">
                <h2>Analytics &amp; Reporting</h2>
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
            <a href="admin-profile.php" style="text-decoration:none">
                <div class="admin-avatar">A</div>
            </a>
        </div>
    </div>

    <!-- Content -->
    <div class="content">

        <!-- ── KPI STAT CARDS ── -->
        <div class="stat-grid">
            <div class="stat-card reveal">
                <div class="stat-card-icon"><i class="fas fa-coins"></i></div>
                <div class="stat-card-label">Total Revenue</div>
                <div class="stat-card-num">Rs <?php echo $total_revenue >= 1000 ? number_format($total_revenue/1000,1).'K' : number_format($total_revenue); ?></div>
                <div class="stat-card-sub"><span class="<?php echo $growth_pct >= 0 ? 'up' : 'down'; ?>">
                    <i class="fas fa-arrow-<?php echo $growth_pct >= 0 ? 'up' : 'down'; ?>"></i>
                    <?php echo abs($growth_pct); ?>% vs last month
                </span></div>
            </div>
            <div class="stat-card reveal reveal-delay-1">
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
            <div class="stat-card reveal reveal-delay-2">
                <div class="stat-card-icon"><i class="fas fa-receipt"></i></div>
                <div class="stat-card-label">Avg Order Value</div>
                <div class="stat-card-num">Rs <?php echo number_format($avg_order); ?></div>
                <div class="stat-card-sub">Per transaction</div>
            </div>
            <div class="stat-card reveal reveal-delay-3">
                <div class="stat-card-icon"><i class="fas fa-star"></i></div>
                <div class="stat-card-label">Avg Rating</div>
                <div class="stat-card-num"><?php echo $avg_rating > 0 ? $avg_rating : 'N/A'; ?></div>
                <div class="stat-card-sub">
                    <?php if($total_reviews > 0): ?>
                    <span class="up"><?php echo $total_reviews; ?> reviews total</span>
                    <?php else: ?>No reviews yet<?php endif; ?>
                </div>
            </div>
        </div>

        <!-- ── PERFORMANCE BANNER ── -->
        <div class="perf-card reveal">
            <div class="perf-title"><i class="fas fa-chart-line" style="margin-right:8px"></i> Business Overview</div>
            <div class="perf-grid">
                <div class="perf-item">
                    <div class="perf-num"><?php echo number_format($total_customers); ?></div>
                    <div class="perf-label">Registered Customers</div>
                </div>
                <div class="perf-divider"></div>
                <div class="perf-item">
                    <div class="perf-num"><?php echo number_format($total_products); ?></div>
                    <div class="perf-label">Menu Items</div>
                </div>
                <div class="perf-divider"></div>
                <div class="perf-item">
                    <div class="perf-num">Rs <?php echo $rev_this >= 1000 ? number_format($rev_this/1000,1).'K' : number_format($rev_this); ?></div>
                    <div class="perf-label">This Month Revenue</div>
                </div>
            </div>
        </div>

        <!-- ── CHARTS ROW 1 ── -->
        <div class="charts-row">

            <!-- Weekly Orders + Revenue -->
            <div class="chart-card reveal">
                <div class="section-hdr">
                    <h3>Last 7 Days Performance</h3>
                    <span>Orders &amp; Revenue</span>
                </div>
                <canvas id="weeklyChart" height="100"></canvas>
            </div>

            <!-- Order Status Breakdown -->
            <div class="chart-card reveal reveal-delay-1">
                <div class="section-hdr">
                    <h3>Order Status</h3>
                    <span>Breakdown</span>
                </div>
                <?php if(!empty($status_data)): ?>
                <canvas id="statusChart" height="180"></canvas>
                <?php else: ?>
                <div class="empty-row"><i class="fas fa-inbox" style="font-size:2rem;color:var(--amber-light);display:block;margin-bottom:10px"></i>No order data yet.</div>
                <?php endif; ?>
                <div class="status-list" style="margin-top:16px">
                    <?php foreach($status_data as $sd):
                        $cls = strtolower($sd['status']);
                        $allowed = ['pending','completed','delivered','cancelled','processing'];
                        $cls = in_array($cls,$allowed) ? $cls : 'default';
                    ?>
                    <div class="status-row <?php echo $cls; ?>">
                        <span><?php echo ucfirst($sd['status']); ?></span>
                        <span class="count"><?php echo $sd['cnt']; ?></span>
                    </div>
                    <?php endforeach; ?>
                    <?php if(empty($status_data)): ?>
                    <div class="status-row default"><span>No orders</span><span class="count">0</span></div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- ── CHARTS ROW 2 ── -->
        <div class="charts-row-2">

            <!-- Monthly Revenue Trend -->
            <div class="chart-card reveal">
                <div class="section-hdr">
                    <h3>Monthly Revenue Trend</h3>
                    <span>Last 6 months</span>
                </div>
                <canvas id="monthlyChart" height="110"></canvas>
            </div>

            <!-- Top Products -->
            <div class="chart-card reveal reveal-delay-1">
                <div class="section-hdr">
                    <h3>Top Selling Items</h3>
                    <span>By quantity ordered</span>
                </div>
                <?php if(!empty($top_products)): ?>
                <div class="product-rank">
                    <?php
                    $max_qty = max(array_values($top_products));
                    $rank = 1;
                    foreach($top_products as $name => $qty):
                        $pct = $max_qty > 0 ? round(($qty / $max_qty) * 100) : 0;
                    ?>
                    <div class="rank-item">
                        <div class="rank-num"><?php echo $rank; ?></div>
                        <div class="rank-bar-wrap" style="flex:1;min-width:0">
                            <div class="rank-name" title="<?php echo htmlspecialchars($name); ?>"><?php echo htmlspecialchars($name); ?></div>
                            <div class="rank-bar-bg">
                                <div class="rank-bar-fill" style="width:<?php echo $pct; ?>%"></div>
                            </div>
                        </div>
                        <div class="rank-count">x<?php echo $qty; ?></div>
                    </div>
                    <?php $rank++; endforeach; ?>
                </div>
                <?php else: ?>
                <div class="empty-row">
                    <i class="fas fa-utensils" style="font-size:2rem;color:var(--amber-light);display:block;margin-bottom:10px"></i>
                    No sales data yet. Orders with named items will appear here.
                </div>
                <?php endif; ?>
            </div>
        </div>

          
     

    </div><!-- /content -->
</div><!-- /main-wrap -->

<script>
/* ── Mobile sidebar ── */
document.getElementById('mobToggle').addEventListener('click', () => {
    document.getElementById('sidebar').classList.toggle('open');
});

/* ── Scroll reveal ── */
const obs = new IntersectionObserver(entries => {
    entries.forEach(e => { if(e.isIntersecting){ e.target.classList.add('visible'); obs.unobserve(e.target); }});
}, { threshold: 0.08 });
document.querySelectorAll('.reveal').forEach(el => obs.observe(el));

/* ── Chart.js defaults ── */
Chart.defaults.font.family = "'Poppins', sans-serif";
Chart.defaults.color       = '#888';

/* ── Weekly Bar Chart ── */
const weeklyCtx = document.getElementById('weeklyChart').getContext('2d');
new Chart(weeklyCtx, {
    type: 'bar',
    data: {
        labels: <?php echo json_encode($daily_labels); ?>,
        datasets: [
            {
                label: 'Orders',
                data:  <?php echo json_encode($daily_orders); ?>,
                backgroundColor: 'rgba(255,140,0,.85)',
                borderRadius: 8,
                borderSkipped: false,
                yAxisID: 'y',
            },
            {
                label: 'Revenue (Rs)',
                data:  <?php echo json_encode($daily_revenue); ?>,
                type:  'line',
                borderColor: '#FF6B35',
                backgroundColor: 'rgba(255,107,53,.08)',
                pointBackgroundColor: '#FF6B35',
                pointRadius: 5,
                tension: 0.4,
                fill: true,
                yAxisID: 'y1',
            }
        ]
    },
    options: {
        responsive: true,
        interaction: { mode: 'index', intersect: false },
        plugins: {
            legend: { position: 'top', labels: { usePointStyle: true, boxWidth: 8, font: { size: 11 } } },
            tooltip: {
                callbacks: {
                    label: ctx => ctx.datasetIndex === 1
                        ? 'Rs ' + ctx.parsed.y.toLocaleString()
                        : ctx.parsed.y + ' orders'
                }
            }
        },
        scales: {
            y:  { beginAtZero: true, grid: { color: 'rgba(255,179,71,.1)' }, ticks: { stepSize: 1 } },
            y1: { beginAtZero: true, position: 'right', grid: { display: false },
                  ticks: { callback: v => 'Rs ' + (v >= 1000 ? (v/1000).toFixed(0)+'K' : v) } }
        }
    }
});

/* ── Monthly Line Chart ── */
const monthlyCtx = document.getElementById('monthlyChart').getContext('2d');
new Chart(monthlyCtx, {
    type: 'line',
    data: {
        labels: <?php echo json_encode($monthly_labels); ?>,
        datasets: [{
            label: 'Revenue (Rs)',
            data:  <?php echo json_encode($monthly_revenue); ?>,
            borderColor: '#FF8C00',
            backgroundColor: ctx => {
                const g = ctx.chart.ctx.createLinearGradient(0,0,0,300);
                g.addColorStop(0,'rgba(255,140,0,.25)');
                g.addColorStop(1,'rgba(255,140,0,.02)');
                return g;
            },
            pointBackgroundColor: '#FF8C00',
            pointBorderColor: '#fff',
            pointBorderWidth: 2,
            pointRadius: 6,
            tension: 0.45,
            fill: true,
            borderWidth: 2.5,
        }]
    },
    options: {
        responsive: true,
        plugins: {
            legend: { display: false },
            tooltip: { callbacks: { label: ctx => 'Rs ' + ctx.parsed.y.toLocaleString() } }
        },
        scales: {
            x: { grid: { color: 'rgba(255,179,71,.08)' } },
            y: { beginAtZero: true, grid: { color: 'rgba(255,179,71,.1)' },
                 ticks: { callback: v => 'Rs '+(v>=1000?(v/1000).toFixed(0)+'K':v) } }
        }
    }
});

/* ── Status Doughnut ── */
<?php if(!empty($status_data)): ?>
const statusCtx = document.getElementById('statusChart').getContext('2d');
new Chart(statusCtx, {
    type: 'doughnut',
    data: {
        labels: <?php echo json_encode(array_column($status_data,'status')); ?>,
        datasets: [{
            data: <?php echo json_encode(array_column($status_data,'cnt')); ?>,
            backgroundColor: ['#f59e0b','#22c55e','#3b82f6','#ef4444','#FF8C00'],
            borderWidth: 0,
            hoverOffset: 8,
        }]
    },
    options: {
        responsive: true,
        cutout: '68%',
        plugins: {
            legend: { position: 'bottom', labels: { usePointStyle: true, boxWidth: 8, font:{ size:11 } } }
        }
    }
});
<?php endif; ?>
</script>
</body>
</html>
