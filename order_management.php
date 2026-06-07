<?php
include('connection.php');
// session_start();

// if (!isset($_SESSION['admin_id'])) {
//     header("Location: admin_login.php");
//     exit();
// }

/* ── DELETE ── */
if(isset($_GET['delete'])){
    $o_id = (int)$_GET['delete'];
    $stmt = mysqli_prepare($conn, "DELETE FROM orders WHERE o_id=?");
    mysqli_stmt_bind_param($stmt, "i", $o_id);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
    header("Location: order_management.php");
    exit();
}

/* ── EDIT FETCH ── */
$editData = [];
if(isset($_GET['edit'])){
    $o_id = (int)$_GET['edit'];
    $stmt = mysqli_prepare($conn, "SELECT * FROM orders WHERE o_id=?");
    mysqli_stmt_bind_param($stmt, "i", $o_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $editData = mysqli_fetch_assoc($result) ?: [];
    mysqli_stmt_close($stmt);
}

/* ── UPDATE ── */
if(isset($_POST['update']) && !empty($_POST['o_id'])){
    $o_id   = (int)$_POST['o_id'];
    $status = $_POST['status'];
    $stmt = mysqli_prepare($conn, "UPDATE orders SET status=? WHERE o_id=?");
    mysqli_stmt_bind_param($stmt, "si", $status, $o_id);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
    header("Location: order_management.php");
    exit();
}

/* ── SAVE ── */
if(isset($_POST['save']) && empty($_POST['o_id'])){
    $c_name     = $_POST['c_name'];
    $items      = $_POST['items'];
    $total      = $_POST['total'];
    $status     = $_POST['status'];
    $order_date = $_POST['order_date'];
    $stmt = mysqli_prepare($conn, "INSERT INTO orders (c_name,items,total,status,order_date) VALUES (?,?,?,?,?)");
    mysqli_stmt_bind_param($stmt, "ssdss", $c_name, $items, $total, $status, $order_date);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
    header("Location: order_management.php");
    exit();
}

/* ── STATS ── */
$total_orders    = 0; $pending = 0; $completed = 0; $cancelled = 0;
$r = mysqli_query($conn, "SELECT COUNT(*) AS c FROM orders"); if($r){ $total_orders = mysqli_fetch_assoc($r)['c']; }
$r = mysqli_query($conn, "SELECT COUNT(*) AS c FROM orders WHERE status='Pending'");    if($r){ $pending   = mysqli_fetch_assoc($r)['c']; }
$r = mysqli_query($conn, "SELECT COUNT(*) AS c FROM orders WHERE status='Completed'"); if($r){ $completed = mysqli_fetch_assoc($r)['c']; }
$r = mysqli_query($conn, "SELECT COUNT(*) AS c FROM orders WHERE status='Cancelled'"); if($r){ $cancelled = mysqli_fetch_assoc($r)['c']; }

/* ── ALL ORDERS ── */
$orders = [];
$res = mysqli_query($conn, "SELECT * FROM orders ORDER BY o_id DESC");
if($res) while($row = mysqli_fetch_assoc($res)) $orders[] = $row;
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Order Management — Brew&amp;Bite Admin</title>
<link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,700;0,900;1,700&family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<style>
:root{
    --amber:#FF8C00;--amber-light:#FFB347;--amber-pale:#FFF3E0;
    --ember:#FF6B35;--ember-dark:#E65C2E;--cream:#FFF8F0;
    --charcoal:#2D2D2D;--muted:#888;--white:#fff;
    --sidebar-w:260px;--nav-h:68px;--radius:16px;
    --transition:.3s cubic-bezier(.4,0,.2,1);
    --card-shadow:0 4px 24px rgba(255,140,0,.1);
}
*,*::before,*::after{box-sizing:border-box;margin:0;padding:0}
html{scroll-behavior:smooth}
body{font-family:'Poppins',sans-serif;background:var(--cream);color:var(--charcoal);display:flex;min-height:100vh;overflow-x:hidden}

/* ── SIDEBAR ── */
.sidebar{width:var(--sidebar-w);background:#1a1a1a;min-height:100vh;position:fixed;top:0;left:0;display:flex;flex-direction:column;z-index:300;transition:transform var(--transition)}
.sidebar-brand{padding:28px 28px 20px;border-bottom:1px solid rgba(255,255,255,.07)}
.sidebar-brand a{font-family:'Playfair Display',serif;font-weight:900;font-size:1.5rem;color:var(--amber);text-decoration:none;letter-spacing:-.5px}
.sidebar-brand a span{color:var(--ember)}
.sidebar-brand small{display:block;font-size:.68rem;color:rgba(255,255,255,.35);font-weight:500;letter-spacing:2px;text-transform:uppercase;margin-top:3px}
.sidebar-nav{flex:1;padding:16px 0;overflow-y:auto}
.nav-section-label{font-size:.62rem;font-weight:700;letter-spacing:2px;text-transform:uppercase;color:rgba(255,255,255,.25);padding:16px 28px 8px}
.sidebar-nav a{display:flex;align-items:center;gap:12px;padding:11px 28px;color:rgba(255,255,255,.55);text-decoration:none;font-size:.84rem;font-weight:500;transition:var(--transition);position:relative}
.sidebar-nav a i{width:18px;text-align:center;font-size:.9rem;flex-shrink:0}
.sidebar-nav a:hover{color:#fff;background:rgba(255,255,255,.06)}
.sidebar-nav a.active{color:var(--amber);background:rgba(255,140,0,.1)}
.sidebar-nav a.active::before{content:'';position:absolute;left:0;top:0;bottom:0;width:3px;background:linear-gradient(180deg,var(--amber),var(--ember));border-radius:0 3px 3px 0}
.sidebar-footer{padding:20px 28px;border-top:1px solid rgba(255,255,255,.07)}
.sidebar-footer a{display:flex;align-items:center;gap:10px;color:rgba(255,255,255,.4);font-size:.82rem;text-decoration:none;transition:var(--transition)}
.sidebar-footer a:hover{color:var(--ember)}

/* ── MAIN ── */
.main-wrap{margin-left:var(--sidebar-w);flex:1;display:flex;flex-direction:column;min-height:100vh}

/* ── TOPBAR ── */
.topbar{height:var(--nav-h);background:rgba(255,255,255,.95);backdrop-filter:blur(18px);border-bottom:1px solid rgba(255,179,71,.2);display:flex;align-items:center;justify-content:space-between;padding:0 36px;position:sticky;top:0;z-index:200;box-shadow:0 2px 12px rgba(255,140,0,.07)}
.topbar-left h2{font-family:'Playfair Display',serif;font-size:1.4rem;font-weight:900;color:var(--charcoal);line-height:1}
.topbar-left span{font-size:.72rem;color:var(--muted);margin-top:3px;display:block}
.topbar-right{display:flex;align-items:center;gap:14px}
.topbar-badge{display:flex;align-items:center;gap:7px;background:var(--amber-pale);border:1px solid rgba(255,179,71,.3);border-radius:50px;padding:7px 16px;font-size:.8rem;font-weight:600;color:var(--amber);text-decoration:none;transition:var(--transition)}
.topbar-badge:hover{background:var(--amber);color:#fff}
.topbar-badge .dot{width:8px;height:8px;border-radius:50%;background:var(--ember);animation:pulse 2s infinite}
@keyframes pulse{0%,100%{opacity:1;transform:scale(1)}50%{opacity:.6;transform:scale(1.3)}}
.admin-avatar{width:38px;height:38px;border-radius:50%;background:linear-gradient(135deg,var(--amber),var(--ember));display:flex;align-items:center;justify-content:center;color:#fff;font-weight:700;font-size:.9rem;box-shadow:0 4px 12px rgba(255,107,53,.3);text-decoration:none}

/* ── CONTENT ── */
.content{padding:36px;flex:1}

/* ── STAT CARDS ── */
.stat-grid{display:grid;grid-template-columns:repeat(4,1fr);gap:20px;margin-bottom:32px}
.stat-card{background:var(--white);border:1px solid rgba(255,179,71,.15);border-radius:var(--radius);padding:22px;box-shadow:var(--card-shadow);transition:var(--transition);position:relative;overflow:hidden}
.stat-card::before{content:'';position:absolute;top:0;left:0;right:0;height:3px;background:linear-gradient(90deg,var(--amber),var(--ember));transform:scaleX(0);transform-origin:left;transition:var(--transition)}
.stat-card:hover{transform:translateY(-4px);box-shadow:0 14px 36px rgba(255,140,0,.16)}
.stat-card:hover::before{transform:scaleX(1)}
.stat-card-icon{width:44px;height:44px;border-radius:12px;background:var(--amber-pale);display:flex;align-items:center;justify-content:center;font-size:1rem;color:var(--amber);margin-bottom:14px;transition:var(--transition)}
.stat-card:hover .stat-card-icon{background:linear-gradient(135deg,var(--amber),var(--ember));color:#fff;transform:rotate(-5deg) scale(1.08)}
.stat-card-label{font-size:.72rem;font-weight:600;color:var(--muted);text-transform:uppercase;letter-spacing:1px;margin-bottom:4px}
.stat-card-num{font-family:'Playfair Display',serif;font-size:2rem;font-weight:900;color:var(--charcoal);line-height:1}
.stat-card-sub{font-size:.7rem;color:var(--muted);margin-top:5px}
.stat-card-sub .warn{color:var(--ember);font-weight:600}
.stat-card-sub .up{color:#22c55e;font-weight:600}
.stat-card-sub .bad{color:#e74c3c;font-weight:600}

/* ── SECTION HEADER ── */
.section-hdr{display:flex;align-items:center;justify-content:space-between;margin-bottom:18px}
.section-hdr h3{font-family:'Playfair Display',serif;font-size:1.15rem;font-weight:900;color:var(--charcoal)}

/* ── FORM CARD ── */
.form-card{background:var(--white);border:1px solid rgba(255,179,71,.18);border-radius:var(--radius);padding:28px 30px;margin-bottom:28px;box-shadow:var(--card-shadow)}
.form-card-header{display:flex;align-items:center;gap:10px;padding-bottom:16px;margin-bottom:22px;border-bottom:1px solid rgba(255,179,71,.15)}
.form-card-header .hdr-icon{width:36px;height:36px;border-radius:10px;background:linear-gradient(135deg,var(--amber),var(--ember));display:flex;align-items:center;justify-content:center;color:#fff;font-size:.85rem;flex-shrink:0}
.form-card-header h3{font-family:'Playfair Display',serif;font-size:1.05rem;font-weight:700;color:var(--charcoal)}
.edit-banner{background:var(--amber-pale);border:1.5px dashed rgba(255,140,0,.4);border-radius:10px;padding:10px 16px;margin-bottom:18px;font-size:.8rem;color:var(--ember);display:flex;align-items:center;gap:8px;font-weight:500}

.form-grid{display:grid;grid-template-columns:1fr 1fr;gap:16px 22px}
.form-group{display:flex;flex-direction:column;gap:6px}
.form-group.full-width{grid-column:1/-1}
.form-group label{font-size:.72rem;font-weight:700;color:var(--charcoal);text-transform:uppercase;letter-spacing:.5px;display:flex;align-items:center;gap:5px}
.form-group label i{color:var(--amber);font-size:.7rem}
.form-group input,.form-group select{font-family:'Poppins',sans-serif;font-size:.86rem;padding:10px 14px;border:1.5px solid rgba(255,179,71,.25);border-radius:10px;outline:none;background:var(--cream);color:var(--charcoal);transition:var(--transition);width:100%}
.form-group input:focus,.form-group select:focus{border-color:var(--amber);background:var(--white);box-shadow:0 0 0 3px rgba(255,140,0,.1)}

.form-actions{grid-column:1/-1;display:flex;gap:10px;margin-top:4px}
.btn-save{display:flex;align-items:center;gap:7px;padding:10px 24px;background:linear-gradient(135deg,var(--amber),var(--ember));color:#fff;border:none;border-radius:10px;font-family:'Poppins',sans-serif;font-size:.85rem;font-weight:700;cursor:pointer;transition:var(--transition)}
.btn-save:hover{background:linear-gradient(135deg,var(--ember-dark),#c44d25);transform:translateY(-1px);box-shadow:0 6px 18px rgba(255,107,53,.3)}
.btn-cancel{display:flex;align-items:center;gap:7px;padding:10px 18px;background:rgba(0,0,0,.05);color:var(--muted);border:none;border-radius:10px;font-family:'Poppins',sans-serif;font-size:.85rem;font-weight:600;cursor:pointer;text-decoration:none;transition:var(--transition)}
.btn-cancel:hover{background:rgba(0,0,0,.1);color:var(--charcoal)}

/* ── TABLE CARD ── */
.table-card{background:var(--white);border:1px solid rgba(255,179,71,.18);border-radius:var(--radius);overflow:hidden;box-shadow:var(--card-shadow)}
.table-card-header{padding:16px 24px;border-bottom:1px solid rgba(255,179,71,.12);display:flex;align-items:center;justify-content:space-between;background:rgba(255,243,224,.3)}
.table-card-header h3{font-family:'Playfair Display',serif;font-size:1rem;font-weight:700;color:var(--charcoal);display:flex;align-items:center;gap:8px}
.table-card-header h3 i{color:var(--amber)}
.search-wrap{position:relative}
.search-wrap i{position:absolute;left:11px;top:50%;transform:translateY(-50%);color:var(--muted);font-size:.78rem;pointer-events:none}
.search-wrap input{padding:7px 14px 7px 32px;border:1.5px solid rgba(255,179,71,.25);border-radius:50px;font-family:'Poppins',sans-serif;font-size:.78rem;background:var(--cream);outline:none;width:200px;transition:var(--transition)}
.search-wrap input:focus{border-color:var(--amber);background:var(--white);width:220px;box-shadow:0 0 0 3px rgba(255,140,0,.08)}

.orders-table{width:100%;border-collapse:collapse;font-family:'Poppins',sans-serif}
.orders-table thead th{background:linear-gradient(135deg,var(--amber),var(--ember));color:#fff;padding:12px 16px;text-align:left;font-weight:600;font-size:.74rem;text-transform:uppercase;letter-spacing:.6px}
.orders-table tbody tr{border-bottom:1px solid rgba(255,179,71,.1);transition:var(--transition)}
.orders-table tbody tr:last-child{border-bottom:none}
.orders-table tbody tr:hover td{background:rgba(255,243,224,.5)}
.orders-table td{padding:13px 16px;color:var(--charcoal);font-size:.82rem;vertical-align:middle;white-space:nowrap}

.order-id-cell{font-weight:700;color:var(--amber);font-family:'Playfair Display',serif;font-size:.95rem}
.customer-cell .name{font-weight:600;font-size:.84rem;color:var(--charcoal)}
.items-cell{max-width:180px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;color:var(--muted);font-size:.78rem}
.total-cell{font-family:'Playfair Display',serif;font-weight:700;font-size:.95rem;color:var(--charcoal)}
.date-cell{color:var(--muted);font-size:.76rem}

.status-badge{display:inline-flex;align-items:center;gap:5px;padding:4px 10px;border-radius:50px;font-size:.68rem;font-weight:700;text-transform:uppercase;letter-spacing:.5px}
.status-pending   {background:#FFF3CD;color:#856404}
.status-processing{background:#DBEAFE;color:#1E40AF}
.status-completed {background:#D1FAE5;color:#065F46}
.status-cancelled {background:#FEE2E2;color:#991B1B}

.action-btns{display:flex;gap:6px}
.btn-edit{display:flex;align-items:center;gap:5px;padding:6px 13px;background:var(--amber-pale);color:var(--amber);border-radius:8px;text-decoration:none;font-size:.74rem;font-weight:600;transition:var(--transition);border:1px solid rgba(255,140,0,.2)}
.btn-edit:hover{background:var(--amber);color:#fff}
.btn-delete{display:flex;align-items:center;gap:5px;padding:6px 13px;background:#fff5f5;color:#e74c3c;border-radius:8px;text-decoration:none;font-size:.74rem;font-weight:600;transition:var(--transition);border:1px solid rgba(231,76,60,.15)}
.btn-delete:hover{background:#e74c3c;color:#fff}

.empty-row td{text-align:center;padding:48px 20px;color:var(--muted);font-size:.85rem}
.empty-row .empty-icon{font-size:2.2rem;display:block;margin-bottom:8px;opacity:.4}

/* ── REVEAL ── */
.reveal{opacity:0;transform:translateY(18px);transition:opacity .6s ease,transform .6s ease}
.reveal.visible{opacity:1;transform:translateY(0)}
.reveal-delay-1{transition-delay:.08s}.reveal-delay-2{transition-delay:.16s}.reveal-delay-3{transition-delay:.24s}

/* mobile */
.mob-toggle{display:none;background:none;border:none;cursor:pointer;flex-direction:column;gap:5px;padding:6px}
.mob-toggle span{width:22px;height:2px;background:var(--charcoal);border-radius:2px;display:block}
@media(max-width:900px){
    .mob-toggle{display:flex}
    .sidebar{transform:translateX(-100%)}
    .sidebar.open{transform:translateX(0)}
    .main-wrap{margin-left:0}
    .stat-grid{grid-template-columns:repeat(2,1fr)}
    .form-grid{grid-template-columns:1fr}
    .content{padding:20px}
}
@media(max-width:500px){.stat-grid{grid-template-columns:1fr}}
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
        <a href="order_management.php" class="active"><i class="fas fa-shopping-bag"></i> Orders</a>
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
        <div style="display:flex;align-items:center;gap:14px">
            <button class="mob-toggle" id="mobToggle" aria-label="Menu"><span></span><span></span><span></span></button>
            <div class="topbar-left">
                <h2>Order Management</h2>
                <span><?= date('l, d F Y') ?></span>
            </div>
        </div>
        <div class="topbar-right">
            <?php if($pending > 0): ?>
            <a href="#ordersTable" class="topbar-badge">
                <span class="dot"></span> <?= $pending ?> Pending
            </a>
            <?php endif; ?>
            <a href="admin-profile.php" class="admin-avatar">A</a>
        </div>
    </div>

    <div class="content">

        <!-- Stat Cards -->
        <div class="stat-grid">
            <div class="stat-card reveal">
                <div class="stat-card-icon"><i class="fas fa-shopping-bag"></i></div>
                <div class="stat-card-label">Total Orders</div>
                <div class="stat-card-num"><?= $total_orders ?></div>
                <div class="stat-card-sub">All time</div>
            </div>
            <div class="stat-card reveal reveal-delay-1">
                <div class="stat-card-icon"><i class="fas fa-clock"></i></div>
                <div class="stat-card-label">Pending</div>
                <div class="stat-card-num"><?= $pending ?></div>
                <div class="stat-card-sub">
                    <?php if($pending > 0): ?><span class="warn"><i class="fas fa-exclamation-circle"></i> Needs attention</span>
                    <?php else: ?><span class="up">All clear</span><?php endif; ?>
                </div>
            </div>
            <div class="stat-card reveal reveal-delay-2">
                <div class="stat-card-icon"><i class="fas fa-check-circle"></i></div>
                <div class="stat-card-label">Completed</div>
                <div class="stat-card-num"><?= $completed ?></div>
                <div class="stat-card-sub"><span class="up"><i class="fas fa-arrow-up"></i> Fulfilled</span></div>
            </div>
            <div class="stat-card reveal reveal-delay-3">
                <div class="stat-card-icon"><i class="fas fa-times-circle"></i></div>
                <div class="stat-card-label">Cancelled</div>
                <div class="stat-card-num"><?= $cancelled ?></div>
                <div class="stat-card-sub"><?php if($cancelled > 0): ?><span class="bad"><?= $cancelled ?> lost</span><?php else: ?><span class="up">None</span><?php endif; ?></div>
            </div>
        </div>

        <!-- Form Card -->
        <div class="form-card reveal">
            <div class="form-card-header">
                <div class="hdr-icon"><i class="fas <?= !empty($editData) ? 'fa-pen' : 'fa-plus' ?>"></i></div>
                <h3><?= !empty($editData) ? 'Update Order Status' : 'Update Customer Order' ?></h3>
            </div>

            <?php if(!empty($editData)): ?>
            <div class="edit-banner">
                <i class="fas fa-info-circle"></i>
                Editing Order <strong>#<?= $editData['o_id'] ?></strong> — <?= htmlspecialchars($editData['c_name']) ?>
            </div>
            <?php endif; ?>

            <form method="POST">
                <input type="hidden" name="o_id" value="<?= $editData['o_id'] ?? '' ?>">
                <div class="form-grid">
                    <div class="form-group">
                        <label><i class="fas fa-user"></i> Customer Name</label>
                        <input type="text" name="c_name" placeholder="Customer name"
                               value="<?= htmlspecialchars($editData['c_name'] ?? '') ?>" required>
                    </div>
                    <div class="form-group">
                        <label><i class="fas fa-money-bill-wave"></i> Total (Rs)</label>
                        <input type="number" name="total" placeholder="e.g. 1500"
                               value="<?= htmlspecialchars($editData['total'] ?? '') ?>" required>
                    </div>
                    <div class="form-group full-width">
                        <label><i class="fas fa-utensils"></i> Items</label>
                        <input type="text" name="items" placeholder="e.g. Cappuccino x2, Burger x1"
                               value="<?= htmlspecialchars($editData['items'] ?? '') ?>" required>
                    </div>
                    <div class="form-group">
                        <label><i class="fas fa-toggle-on"></i> Status</label>
                        <select name="status" required>
                            <option value="">— Select Status —</option>
                            <?php foreach(['Pending','Processing','Completed','Cancelled'] as $s): ?>
                            <option value="<?= $s ?>" <?= (($editData['status'] ?? '') == $s) ? 'selected' : '' ?>><?= $s ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label><i class="fas fa-calendar-alt"></i> Order Date</label>
                        <input type="date" name="order_date"
                               value="<?= htmlspecialchars($editData['order_date'] ?? date('Y-m-d')) ?>" required>
                    </div>
                    <div class="form-actions">
                        <?php if(!empty($editData)): ?>
                            <button type="submit" name="update" class="btn-save"><i class="fas fa-check"></i> Update Order</button>
                            <a href="order_management.php" class="btn-cancel"><i class="fas fa-times"></i> Cancel</a>
                        <?php else: ?>
                            <button type="submit" name="save" class="btn-save"><i class="fas fa-plus"></i> Save</button>
                        <?php endif; ?>
                    </div>
                </div>
            </form>
        </div>

        <!-- Orders Table -->
        <div class="table-card reveal" id="ordersTable">
            <div class="table-card-header">
                <h3><i class="fas fa-list"></i> All Orders</h3>
                <div class="search-wrap">
                    <i class="fas fa-search"></i>
                    <input type="text" id="tableSearch" placeholder="Search orders…" oninput="filterTable()">
                </div>
            </div>
            <div style="overflow-x:auto">
                <table class="orders-table" id="ordersTableEl">
                    <thead>
                        <tr>
                            <th>#ID</th>
                            <th>Customer</th>
                            <th>Items</th>
                            <th>Total</th>
                            <th>Status</th>
                            <th>Date</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php if(empty($orders)): ?>
                        <tr class="empty-row"><td colspan="7"><span class="empty-icon">🛒</span>No orders found yet.</td></tr>
                    <?php else: foreach($orders as $row):
                        $s = strtolower($row['status']);
                        $sc = match($s){
                            'completed'  => 'status-completed',
                            'cancelled','canceled' => 'status-cancelled',
                            'processing' => 'status-processing',
                            default      => 'status-pending',
                        };
                    ?>
                        <tr>
                            <td class="order-id-cell">#<?= $row['o_id'] ?></td>
                            <td class="customer-cell"><div class="name"><?= htmlspecialchars($row['c_name']) ?></div></td>
                            <td class="items-cell" title="<?= htmlspecialchars($row['items']) ?>"><?= htmlspecialchars($row['items']) ?></td>
                            <td class="total-cell">Rs <?= number_format($row['total']) ?></td>
                            <td><span class="status-badge <?= $sc ?>"><?= htmlspecialchars($row['status']) ?></span></td>
                            <td class="date-cell"><?= htmlspecialchars($row['order_date']) ?></td>
                            <td>
                                <div class="action-btns">
                                    <a class="btn-edit" href="order_management.php?edit=<?= $row['o_id'] ?>"><i class="fas fa-pen"></i> Edit</a>
                                    <a class="btn-delete" href="order_management.php?delete=<?= $row['o_id'] ?>" onclick="return confirm('Delete order #<?= $row['o_id'] ?>?')"><i class="fas fa-trash"></i> Delete</a>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

    </div><!-- /content -->
</div><!-- /main-wrap -->

<script>
document.getElementById('mobToggle').addEventListener('click', () => document.getElementById('sidebar').classList.toggle('open'));

const obs = new IntersectionObserver(entries => {
    entries.forEach(e => { if(e.isIntersecting){ e.target.classList.add('visible'); obs.unobserve(e.target); }});
}, {threshold:0.1});
document.querySelectorAll('.reveal').forEach(el => obs.observe(el));

function filterTable(){
    const q = document.getElementById('tableSearch').value.toLowerCase();
    document.querySelectorAll('#ordersTableEl tbody tr:not(.empty-row)').forEach(row => {
        row.style.display = row.textContent.toLowerCase().includes(q) ? '' : 'none';
    });
}

// Animate stat counters
window.addEventListener('load', () => {
    document.querySelectorAll('.stat-card-num').forEach(el => {
        const n = parseInt(el.textContent.replace(/\D/g,''));
        if(!n) return;
        let cur = 0, step = n/50;
        const t = setInterval(() => {
            cur = Math.min(cur+step, n);
            el.textContent = Math.floor(cur);
            if(cur >= n){ el.textContent = n; clearInterval(t); }
        }, 20);
    });
});
</script>
</body>
</html>