<?php
include('connection.php');
session_start();

if (!isset($_SESSION['admin_id'])) {
    header("Location: admin_login.php");
    exit();
}

/* ── DELETE ── */
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    $del = mysqli_prepare($conn, "DELETE FROM products WHERE id = ?");
    mysqli_stmt_bind_param($del, "i", $id);
    mysqli_stmt_execute($del);
    mysqli_stmt_close($del);
    header("Location: admin_menu.php");
    exit();
}

/* ── EDIT FETCH ── */
$editData = null;
if (isset($_GET['edit'])) {
    $id   = (int)$_GET['edit'];
    $stmt = mysqli_prepare($conn, "SELECT * FROM products WHERE id = ? LIMIT 1");
    mysqli_stmt_bind_param($stmt, "i", $id);
    mysqli_stmt_execute($stmt);
    $editData = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
    mysqli_stmt_close($stmt);
}

$save_error = '';

/* ── ADD NEW PRODUCT ── */
if (isset($_POST['save'])) {
    $name     = trim($_POST['name']);
    $price    = (float)$_POST['price'];
    $category = trim($_POST['category']);
    $stock    = (int)$_POST['stock'];
    $status   = $_POST['status'];
    $featured = isset($_POST['featured']) ? 'yes' : 'no';
    $image    = '';

    // Handle image upload
    if (!empty($_FILES['image']['name'])) {
        $allowed = ['jpg','jpeg','png','gif','webp'];
        $ext     = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
        if (in_array($ext, $allowed)) {
            $image = time() . '_' . basename($_FILES['image']['name']);
            move_uploaded_file($_FILES['image']['tmp_name'], 'images/' . $image);
        } else {
            $save_error = 'Invalid image format. Use JPG, PNG, GIF or WEBP.';
        }
    }

    if (!$save_error) {
        $stmt = mysqli_prepare($conn,
        "INSERT INTO products (item_name, price, category, status, stock, image, featured)
        VALUES (?, ?, ?, ?, ?, ?, ?)");
        mysqli_stmt_bind_param($stmt, "sdssiss",
        $name, $price, $category, $status, $stock, $image, $featured
    );

        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);

        header("Location: admin_menu.php");
        exit();
    }
}

/* ── UPDATE PRODUCT ── */
if (isset($_POST['update'])) {
    $id       = (int)$_POST['id'];
    $name     = trim($_POST['name']);
    $price    = (float)$_POST['price'];
    $category = trim($_POST['category']);
    $stock    = (int)$_POST['stock'];
    $status   = $_POST['status'];
    $featured = isset($_POST['featured']) ? 'yes' : 'no';

    if (!empty($_FILES['image']['name'])) {
        // New image uploaded
        $image = time() . '_' . basename($_FILES['image']['name']);
        move_uploaded_file($_FILES['image']['tmp_name'], 'images/' . $image);

        $stmt = mysqli_prepare($conn,
            "UPDATE products
             SET item_name=?, price=?, category=?, status=?, stock=?, image=?, featured=?
             WHERE id=?"
        );
        mysqli_stmt_bind_param($stmt, "sdssissi",
            $name, $price, $category, $status, $stock, $image, $featured, $id
        );
    } else {
        // Keep existing image
        $stmt = mysqli_prepare($conn,
            "UPDATE products
             SET item_name=?, price=?, category=?, status=?, stock=?, featured=?
             WHERE id=?"
        );
        mysqli_stmt_bind_param($stmt, "sdssssi",
            $name, $price, $category, $status, $stock, $featured, $id
        );
    }

    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
    header("Location: admin_menu.php");
    exit();
}

/* ── SEARCH / FILTER ── */
$search = isset($_GET['search']) ? trim($_GET['search']) : '';

if ($search) {
    $like  = '%' . $search . '%';
    $pstmt = mysqli_prepare($conn,
        "SELECT * FROM products
         WHERE item_name LIKE ? OR category LIKE ?
         ORDER BY id ASC"
    );
    mysqli_stmt_bind_param($pstmt, "ss", $like, $like);
    mysqli_stmt_execute($pstmt);
    $products = mysqli_stmt_get_result($pstmt);
} else {
    $products = mysqli_query($conn, "SELECT * FROM products ORDER BY id ASC");
}

$total_count = $products ? mysqli_num_rows($products) : 0;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Menu Management — Brew&amp;Bite</title>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,700;0,900;1,700&family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
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
    body { font-family: 'Poppins', sans-serif; background: var(--cream); color: var(--charcoal); display: flex; min-height: 100vh; }

    /* ══ SIDEBAR ══ */
    .sidebar { width: var(--sidebar-w); background: #1a1a1a; min-height: 100vh; position: fixed; top: 0; left: 0; display: flex; flex-direction: column; z-index: 300; transition: transform var(--transition); }
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

    /* ══ MAIN ══ */
    .main-wrap { margin-left: var(--sidebar-w); flex: 1; display: flex; flex-direction: column; min-height: 100vh; }

    /* ══ TOPBAR ══ */
    .topbar { height: var(--nav-h); background: rgba(255,255,255,.95); backdrop-filter: blur(18px); border-bottom: 1px solid rgba(255,179,71,.2); display: flex; align-items: center; justify-content: space-between; padding: 0 36px; position: sticky; top: 0; z-index: 200; box-shadow: 0 2px 12px rgba(255,140,0,.07); }
    .topbar-left h2 { font-family: 'Playfair Display', serif; font-size: 1.4rem; font-weight: 900; color: var(--charcoal); }
    .topbar-left span { font-size: .72rem; color: var(--muted); margin-top: 2px; display: block; }
    .topbar-right { display: flex; align-items: center; gap: 12px; }
    .admin-avatar { width: 38px; height: 38px; border-radius: 50%; background: linear-gradient(135deg, var(--amber), var(--ember)); display: flex; align-items: center; justify-content: center; color: #fff; font-weight: 700; font-size: .9rem; box-shadow: 0 4px 12px rgba(255,107,53,.3); }
    .mob-toggle { display: none; background: none; border: none; cursor: pointer; flex-direction: column; gap: 5px; padding: 6px; }
    .mob-toggle span { width: 22px; height: 2px; background: var(--charcoal); border-radius: 2px; display: block; }
    @media (max-width: 900px) { .mob-toggle { display: flex; } .sidebar { transform: translateX(-100%); } .sidebar.open { transform: translateX(0); } .main-wrap { margin-left: 0; } }

    /* ══ CONTENT ══ */
    .content { padding: 32px 36px; flex: 1; }

    /* ══ ERROR BANNER ══ */
    .alert-error { border-radius: 12px; padding: 12px 16px; font-size: .82rem; margin-bottom: 18px; display: flex; align-items: center; gap: 10px; background: #fff0f0; border: 1px solid #f5c6c6; color: #dc2626; }

    /* ══ FORM CARD ══ */
    .form-card { background: var(--white); border: 1px solid rgba(255,179,71,.2); border-radius: var(--radius); padding: 28px 32px; margin-bottom: 32px; box-shadow: var(--card-shadow); }
    .form-card-title { font-family: 'Playfair Display', serif; font-size: 1.2rem; font-weight: 900; color: var(--charcoal); margin-bottom: 20px; padding-bottom: 14px; border-bottom: 1px solid rgba(255,179,71,.15); display: flex; align-items: center; gap: 10px; }
    .form-card-title i { color: var(--amber); }
    .edit-banner { background: var(--amber-pale); border: 1.5px dashed var(--amber); border-radius: 10px; padding: 10px 16px; margin-bottom: 20px; font-size: .82rem; color: var(--ember); font-weight: 600; display: flex; align-items: center; gap: 8px; }
    .form-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 16px 24px; }
    .form-group { display: flex; flex-direction: column; gap: 6px; }
    .form-group.full { grid-column: 1 / -1; }
    .form-group label { font-size: .72rem; font-weight: 700; color: var(--muted); text-transform: uppercase; letter-spacing: .8px; }
    .form-group input,
    .form-group select { font-family: 'Poppins', sans-serif; font-size: .86rem; padding: 10px 14px; border: 1.5px solid rgba(255,179,71,.25); border-radius: 10px; outline: none; background: var(--cream); color: var(--charcoal); transition: var(--transition); width: 100%; }
    .form-group input:focus,
    .form-group select:focus { border-color: var(--amber); background: #fff; box-shadow: 0 0 0 3px rgba(255,140,0,.1); }
    .form-group input[type="file"] { padding: 8px 12px; cursor: pointer; }
    .form-group input[type="file"]::file-selector-button { font-family: 'Poppins', sans-serif; font-size: .74rem; font-weight: 700; padding: 5px 12px; background: linear-gradient(135deg, var(--amber), var(--ember)); color: #fff; border: none; border-radius: 6px; cursor: pointer; margin-right: 10px; }
    .featured-toggle { display: flex; align-items: center; gap: 10px; padding: 10px 14px; border: 1.5px solid rgba(255,179,71,.25); border-radius: 10px; background: var(--cream); cursor: pointer; transition: var(--transition); }
    .featured-toggle:hover { border-color: var(--amber); background: var(--amber-pale); }
    .featured-toggle input[type="checkbox"] { accent-color: var(--amber); width: 16px; height: 16px; cursor: pointer; }
    .featured-toggle span { font-size: .84rem; font-weight: 600; color: var(--charcoal); }
    .form-actions { grid-column: 1 / -1; display: flex; gap: 10px; margin-top: 4px; }
    .btn-save { display: inline-flex; align-items: center; gap: 8px; padding: 11px 26px; background: linear-gradient(135deg, var(--amber), var(--ember)); color: #fff; border: none; border-radius: 50px; font-family: 'Poppins', sans-serif; font-size: .86rem; font-weight: 600; cursor: pointer; transition: var(--transition); box-shadow: 0 4px 14px rgba(255,107,53,.3); }
    .btn-save:hover { transform: translateY(-2px); box-shadow: 0 8px 22px rgba(255,107,53,.4); }
    .btn-cancel { display: inline-flex; align-items: center; gap: 8px; padding: 11px 22px; background: transparent; color: var(--muted); border: 1.5px solid rgba(0,0,0,.12); border-radius: 50px; font-family: 'Poppins', sans-serif; font-size: .86rem; font-weight: 600; cursor: pointer; text-decoration: none; transition: var(--transition); }
    .btn-cancel:hover { background: #f0f0f0; color: var(--charcoal); }

    /* ══ TABLE TOP ══ */
    .table-topbar { display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 14px; margin-bottom: 18px; }
    .table-topbar h3 { font-family: 'Playfair Display', serif; font-size: 1.2rem; font-weight: 900; color: var(--charcoal); }
    .search-form { display: flex; gap: 8px; }
    .search-form input { padding: 9px 16px; border: 1.5px solid rgba(255,179,71,.25); border-radius: 50px; font-family: 'Poppins', sans-serif; font-size: .82rem; background: var(--white); outline: none; transition: var(--transition); min-width: 220px; }
    .search-form input:focus { border-color: var(--amber); box-shadow: 0 0 0 3px rgba(255,140,0,.1); }
    .search-form button { padding: 9px 18px; border: none; border-radius: 50px; background: linear-gradient(135deg, var(--amber), var(--ember)); color: #fff; font-size: .82rem; font-weight: 600; cursor: pointer; font-family: 'Poppins', sans-serif; transition: var(--transition); }
    .search-form button:hover { transform: translateY(-1px); }
    .count-pill { background: var(--amber-pale); color: var(--amber); font-size: .72rem; font-weight: 700; padding: 3px 10px; border-radius: 50px; border: 1px solid rgba(255,179,71,.3); }

    /* ══ TABLE ══ */
    .table-wrap { background: var(--white); border: 1px solid rgba(255,179,71,.15); border-radius: var(--radius); box-shadow: var(--card-shadow); overflow: hidden; }
    table { width: 100%; border-collapse: collapse; }
    thead tr { background: linear-gradient(135deg, var(--amber), var(--ember)); }
    thead th { padding: 13px 16px; text-align: left; font-size: .72rem; font-weight: 700; color: #fff; letter-spacing: .8px; text-transform: uppercase; }
    tbody tr { border-bottom: 1px solid rgba(255,179,71,.1); transition: var(--transition); }
    tbody tr:last-child { border-bottom: none; }
    tbody tr:hover { background: var(--amber-pale); }
    tbody td { padding: 12px 16px; font-size: .82rem; color: var(--charcoal); vertical-align: middle; }
    .prod-img { width: 54px; height: 54px; border-radius: 10px; object-fit: contain; background: var(--amber-pale); border: 1px solid rgba(255,179,71,.2); padding: 4px; }
    .prod-name { font-weight: 600; color: var(--charcoal); }
    .prod-cat { font-size: .74rem; color: var(--muted); margin-top: 2px; }
    .price-tag { font-family: 'Playfair Display', serif; font-size: 1rem; font-weight: 700; color: var(--amber); }
    .status-badge { display: inline-block; padding: 3px 10px; border-radius: 50px; font-size: .68rem; font-weight: 700; text-transform: capitalize; }
    .status-available     { background: #D1FAE5; color: #065F46; }
    .status-not-available { background: #FEE2E2; color: #991B1B; }
    .featured-yes { background: var(--amber-pale); color: var(--amber); border: 1px solid rgba(255,179,71,.35); }
    .featured-no  { background: #f5f5f5; color: #aaa; }
    .action-btn { display: inline-flex; align-items: center; gap: 5px; padding: 6px 12px; border-radius: 50px; font-size: .74rem; font-weight: 600; text-decoration: none; transition: var(--transition); border: none; cursor: pointer; font-family: 'Poppins', sans-serif; }
    .btn-edit   { background: var(--amber-pale); color: var(--amber); border: 1px solid rgba(255,179,71,.3); }
    .btn-edit:hover   { background: var(--amber); color: #fff; }
    .btn-delete { background: #FEE2E2; color: #991B1B; border: 1px solid rgba(153,27,27,.15); }
    .btn-delete:hover { background: #ef4444; color: #fff; }
    .no-data { text-align: center; padding: 50px 20px; color: var(--muted); font-size: .88rem; }
    .no-data i { font-size: 2.5rem; color: var(--amber-light); display: block; margin-bottom: 12px; }

    /* ══ REVEAL ══ */
    .reveal { opacity: 0; transform: translateY(16px); transition: opacity .55s ease, transform .55s ease; }
    .reveal.visible { opacity: 1; transform: translateY(0); }
    @media (max-width: 768px) {
        .content { padding: 20px; }
        .form-grid { grid-template-columns: 1fr; }
        thead th:nth-child(2), tbody td:nth-child(2) { display: none; }
    }
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
        <a href="admin_menu.php" class="active"><i class="fas fa-utensils"></i> Menu Items</a>
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
            <button class="mob-toggle" id="mobToggle"><span></span><span></span><span></span></button>
            <div class="topbar-left">
                <h2>Menu Management</h2>
                <span><?php echo date('l, d F Y'); ?></span>
            </div>
        </div>
        <div class="topbar-right">
            <a href="admin-profile.php" style="text-decoration:none;">
                <div class="admin-avatar">A</div>
            </a>
        </div>
    </div>

    <div class="content">

        <?php if ($save_error): ?>
        <div class="alert-error">
            <i class="fas fa-exclamation-circle"></i>
            <?php echo htmlspecialchars($save_error); ?>
        </div>
        <?php endif; ?>

        <!-- ══ FORM CARD ══ -->
        <div class="form-card reveal">
            <div class="form-card-title">
                <i class="fas <?php echo $editData ? 'fa-pen-to-square' : 'fa-plus-circle'; ?>"></i>
                <?php echo $editData ? 'Edit Product' : 'Add New Product'; ?>
            </div>

            <?php if ($editData): ?>
            <div class="edit-banner">
                <i class="fas fa-circle-info"></i>
                Editing: <strong>&nbsp;<?php echo htmlspecialchars($editData['item_name']); ?></strong>
            </div>
            <?php endif; ?>

            <form method="POST" enctype="multipart/form-data">
                <?php if ($editData): ?>
                    <input type="hidden" name="id" value="<?php echo (int)$editData['id']; ?>">
                <?php endif; ?>

                <div class="form-grid">

                    <!-- item_name -->
                    <div class="form-group">
                        <label><i class="fas fa-tag"></i>&nbsp; Product Name</label>
                        <input type="text" name="name" placeholder="e.g. Mocha Latte" required
                               value="<?php echo htmlspecialchars($editData['item_name'] ?? ''); ?>">
                    </div>

                    <!-- price -->
                    <div class="form-group">
                        <label><i class="fas fa-money-bill-wave"></i>&nbsp; Price (Rs)</label>
                        <input type="number" name="price" placeholder="e.g. 450" required min="0" step="0.01"
                               value="<?php echo htmlspecialchars($editData['price'] ?? ''); ?>">
                    </div>

                    <!-- category -->
                    <div class="form-group">
                        <label><i class="fas fa-layer-group"></i>&nbsp; Category</label>
                        <input type="text" name="category" placeholder="e.g. Coffee" required
                               value="<?php echo htmlspecialchars($editData['category'] ?? ''); ?>">
                    </div>

                    <!-- stock -->
                    <div class="form-group">
                        <label><i class="fas fa-cubes"></i>&nbsp; Stock Quantity</label>
                        <input type="number" name="stock" placeholder="e.g. 50" required min="0"
                               value="<?php echo htmlspecialchars($editData['stock'] ?? ''); ?>">
                    </div>

                    <!-- status -->
                    <div class="form-group">
                        <label><i class="fas fa-toggle-on"></i>&nbsp; Status</label>
                        <select name="status" required>
                            <option value="">— Select Status —</option>
                            <option value="available"
                                <?php echo (($editData['status'] ?? '') === 'available') ? 'selected' : ''; ?>>
                                Available
                            </option>
                            <option value="not available"
                                <?php echo (($editData['status'] ?? '') === 'not available') ? 'selected' : ''; ?>>
                                Not Available
                            </option>
                        </select>
                    </div>

                    <!-- image -->
                    <div class="form-group">
                        <label><i class="fas fa-image"></i>&nbsp; Product Image <?php echo $editData ? '(leave blank to keep)' : ''; ?></label>
                        <input type="file" name="image" accept="image/jpeg,image/png,image/gif,image/webp">
                        <?php if ($editData && !empty($editData['image'])): ?>
                        <div style="margin-top:6px;display:flex;align-items:center;gap:8px;">
                            <img src="images/<?php echo htmlspecialchars($editData['image']); ?>"
                                 alt="current" style="width:44px;height:44px;object-fit:contain;border-radius:8px;background:var(--amber-pale);padding:3px;border:1px solid rgba(255,179,71,.3);"
                                 onerror="this.style.display='none'">
                            <span style="font-size:.72rem;color:var(--muted);">Current image</span>
                        </div>
                        <?php endif; ?>
                    </div>

                    <!-- featured -->
                    <div class="form-group">
                        <label><i class="fas fa-star"></i>&nbsp; Featured on Homepage</label>
                        <label class="featured-toggle">
                            <input type="checkbox" name="featured"
                                <?php echo (($editData['featured'] ?? 'no') === 'yes') ? 'checked' : ''; ?>>
                            <span>Show as featured item on homepage</span>
                        </label>
                    </div>

                    <!-- actions -->
                    <div class="form-actions">
                        <?php if ($editData): ?>
                            <button type="submit" name="update" class="btn-save">
                                <i class="fas fa-check"></i> Update Product
                            </button>
                            <a href="admin_menu.php" class="btn-cancel">
                                <i class="fas fa-times"></i> Cancel
                            </a>
                        <?php else: ?>
                            <button type="submit" name="save" class="btn-save">
                                <i class="fas fa-plus"></i> Add Product
                            </button>
                        <?php endif; ?>
                    </div>

                </div><!-- /form-grid -->
            </form>
        </div>

        <!-- ══ PRODUCT TABLE ══ -->
        <div class="reveal">
            <div class="table-topbar">
                <div style="display:flex;align-items:center;gap:12px;">
                    <h3>All Products</h3>
                    <span class="count-pill"><?php echo $total_count; ?> item<?php echo $total_count != 1 ? 's' : ''; ?></span>
                </div>
                <form class="search-form" method="GET" action="admin_menu.php">
                    <input type="text" name="search" placeholder="Search products or category..."
                           value="<?php echo htmlspecialchars($search); ?>">
                    <button type="submit"><i class="fas fa-search"></i> Search</button>
                    <?php if ($search): ?>
                    <a href="admin_menu.php"
                       style="padding:9px 14px;border-radius:50px;background:#f0f0f0;color:var(--muted);font-size:.82rem;font-weight:600;text-decoration:none;display:flex;align-items:center;gap:5px;">
                        <i class="fas fa-times"></i> Clear
                    </a>
                    <?php endif; ?>
                </form>
            </div>

            <div class="table-wrap">
                <table>
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Image</th>
                            <th>Product &amp; Category</th>
                            <th>Price</th>
                            <th>Stock</th>
                            <th>Status</th>
                            <th>Featured</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php
                    if ($products && mysqli_num_rows($products) > 0):
                        // reset pointer if needed
                        mysqli_data_seek($products, 0);
                        while ($row = mysqli_fetch_assoc($products)):
                            $sc = ($row['status'] === 'available') ? 'status-available' : 'status-not-available';
                            $fc = (($row['featured'] ?? 'no') === 'yes') ? 'featured-yes' : 'featured-no';
                    ?>
                    <tr>
                        <td style="color:var(--amber);font-weight:700;"><?php echo (int)$row['id']; ?></td>
                        <td>
                            <img class="prod-img"
                                 src="images/<?php echo htmlspecialchars($row['image']); ?>"
                                 alt="<?php echo htmlspecialchars($row['item_name']); ?>"
                                 onerror="this.src='images/default.png'">
                        </td>
                        <td>
                            <div class="prod-name"><?php echo htmlspecialchars($row['item_name']); ?></div>
                            <div class="prod-cat"><?php echo htmlspecialchars($row['category']); ?></div>
                        </td>
                        <td><span class="price-tag">Rs <?php echo number_format($row['price']); ?></span></td>
                        <td><?php echo (int)$row['stock']; ?></td>
                        <td><span class="status-badge <?php echo $sc; ?>"><?php echo ucwords($row['status']); ?></span></td>
                        <td><span class="status-badge <?php echo $fc; ?>"><?php echo ucfirst($row['featured'] ?? 'no'); ?></span></td>
                        <td>
                            <div style="display:flex;gap:6px;flex-wrap:wrap;">
                                <a href="admin_menu.php?edit=<?php echo (int)$row['id']; ?>"
                                   class="action-btn btn-edit">
                                    <i class="fas fa-pen"></i> Edit
                                </a>
                                <a href="admin_menu.php?delete=<?php echo (int)$row['id']; ?>"
                                   class="action-btn btn-delete"
                                   onclick="return confirm('Delete \'<?php echo htmlspecialchars($row['item_name'], ENT_QUOTES); ?>\'? This cannot be undone.')">
                                    <i class="fas fa-trash"></i> Delete
                                </a>
                            </div>
                        </td>
                    </tr>
                    <?php endwhile; else: ?>
                    <tr>
                        <td colspan="8" class="no-data">
                            <i class="fas fa-box-open"></i>
                            <?php echo $search
                                ? 'No products match &ldquo;' . htmlspecialchars($search) . '&rdquo;'
                                : 'No products added yet. Use the form above to add your first item!'; ?>
                        </td>
                    </tr>
                    <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

    </div><!-- /content -->
</div><!-- /main-wrap -->

<script>
document.getElementById('mobToggle').addEventListener('click', () => {
    document.getElementById('sidebar').classList.toggle('open');
});
const obs = new IntersectionObserver(entries => {
    entries.forEach(e => { if (e.isIntersecting) { e.target.classList.add('visible'); obs.unobserve(e.target); } });
}, { threshold: 0.08 });
document.querySelectorAll('.reveal').forEach(el => obs.observe(el));

// Scroll to form when Edit is clicked
<?php if ($editData): ?>
window.scrollTo({ top: 0, behavior: 'smooth' });
<?php endif; ?>
</script>
</body>
</html>