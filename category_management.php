<?php
include('connection.php');
// session_start();

// if (!isset($_SESSION['admin_id'])) {
//     header("Location: admin_login.php");
//     exit();
// }

/* ── DELETE ── */
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    mysqli_query($conn, "DELETE FROM categories WHERE id='$id'");
    header("Location: category_management.php");
    exit();
}

/* ── EDIT FETCH ── */
$editData = ['id' => '', 'category_name' => ''];
if (isset($_GET['edit'])) {
    $id     = (int)$_GET['edit'];
    $result = mysqli_query($conn, "SELECT * FROM categories WHERE id='$id'");
    $editData = mysqli_fetch_assoc($result) ?: $editData;
}

/* ── SAVE ── */
if (isset($_POST['save'])) {
    $name = mysqli_real_escape_string($conn, $_POST['category_name']);
    mysqli_query($conn, "INSERT INTO categories (category_name) VALUES ('$name')");
    header("Location: category_management.php");
    exit();
}

/* ── UPDATE ── */
if (isset($_POST['update'])) {
    $id   = (int)$_POST['id'];
    $name = mysqli_real_escape_string($conn, $_POST['category_name']);
    mysqli_query($conn, "UPDATE categories SET category_name='$name' WHERE id='$id'");
    header("Location: category_management.php");
    exit();
}

/* ── FETCH ALL ── */
$categories = mysqli_query($conn, "SELECT * FROM categories ORDER BY id DESC");
$total      = $categories ? mysqli_num_rows($categories) : 0;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Category Management — Brew&amp;Bite</title>
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
    .admin-avatar { width: 38px; height: 38px; border-radius: 50%; background: linear-gradient(135deg, var(--amber), var(--ember)); display: flex; align-items: center; justify-content: center; color: #fff; font-weight: 700; font-size: .9rem; box-shadow: 0 4px 12px rgba(255,107,53,.3); text-decoration: none; }
    .mob-toggle { display: none; background: none; border: none; cursor: pointer; flex-direction: column; gap: 5px; padding: 6px; }
    .mob-toggle span { width: 22px; height: 2px; background: var(--charcoal); border-radius: 2px; display: block; }
    @media (max-width: 900px) { .mob-toggle { display: flex; } .sidebar { transform: translateX(-100%); } .sidebar.open { transform: translateX(0); } .main-wrap { margin-left: 0; } }

    /* ══ CONTENT ══ */
    .content { padding: 32px 36px; flex: 1; display: grid; grid-template-columns: 380px 1fr; gap: 28px; align-items: start; }
    @media (max-width: 1024px) { .content { grid-template-columns: 1fr; } }
    @media (max-width: 768px) { .content { padding: 20px; } }

    /* ══ FORM CARD ══ */
    .form-card { background: var(--white); border: 1px solid rgba(255,179,71,.2); border-radius: var(--radius); padding: 28px 28px; box-shadow: var(--card-shadow); position: sticky; top: calc(var(--nav-h) + 20px); }
    .form-card-title { font-family: 'Playfair Display', serif; font-size: 1.15rem; font-weight: 900; color: var(--charcoal); margin-bottom: 20px; padding-bottom: 14px; border-bottom: 1px solid rgba(255,179,71,.15); display: flex; align-items: center; gap: 10px; }
    .form-card-title i { color: var(--amber); }
    .edit-banner { background: var(--amber-pale); border: 1.5px dashed var(--amber); border-radius: 10px; padding: 10px 14px; margin-bottom: 18px; font-size: .8rem; color: var(--ember); font-weight: 600; display: flex; align-items: center; gap: 8px; }
    .form-group { display: flex; flex-direction: column; gap: 6px; margin-bottom: 16px; }
    .form-group label { font-size: .72rem; font-weight: 700; color: var(--muted); text-transform: uppercase; letter-spacing: .8px; }
    .form-group input { font-family: 'Poppins', sans-serif; font-size: .86rem; padding: 11px 14px; border: 1.5px solid rgba(255,179,71,.25); border-radius: 10px; outline: none; background: var(--cream); color: var(--charcoal); transition: var(--transition); width: 100%; }
    .form-group input:focus { border-color: var(--amber); background: #fff; box-shadow: 0 0 0 3px rgba(255,140,0,.1); }
    .form-actions { display: flex; gap: 10px; margin-top: 4px; }
    .btn-save { display: inline-flex; align-items: center; gap: 8px; padding: 11px 24px; background: linear-gradient(135deg, var(--amber), var(--ember)); color: #fff; border: none; border-radius: 50px; font-family: 'Poppins', sans-serif; font-size: .86rem; font-weight: 600; cursor: pointer; transition: var(--transition); box-shadow: 0 4px 14px rgba(255,107,53,.3); }
    .btn-save:hover { transform: translateY(-2px); box-shadow: 0 8px 22px rgba(255,107,53,.4); }
    .btn-cancel { display: inline-flex; align-items: center; gap: 8px; padding: 11px 20px; background: transparent; color: var(--muted); border: 1.5px solid rgba(0,0,0,.12); border-radius: 50px; font-family: 'Poppins', sans-serif; font-size: .86rem; font-weight: 600; cursor: pointer; text-decoration: none; transition: var(--transition); }
    .btn-cancel:hover { background: #f0f0f0; color: var(--charcoal); }

    /* ══ TABLE SIDE ══ */
    .table-side {}
    .table-topbar { display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 12px; margin-bottom: 16px; }
    .table-topbar h3 { font-family: 'Playfair Display', serif; font-size: 1.2rem; font-weight: 900; color: var(--charcoal); }
    .count-pill { background: var(--amber-pale); color: var(--amber); font-size: .72rem; font-weight: 700; padding: 3px 10px; border-radius: 50px; border: 1px solid rgba(255,179,71,.3); }
    .table-wrap { background: var(--white); border: 1px solid rgba(255,179,71,.15); border-radius: var(--radius); box-shadow: var(--card-shadow); overflow: hidden; }
    table { width: 100%; border-collapse: collapse; }
    thead tr { background: linear-gradient(135deg, var(--amber), var(--ember)); }
    thead th { padding: 13px 18px; text-align: left; font-size: .72rem; font-weight: 700; color: #fff; letter-spacing: .8px; text-transform: uppercase; }
    tbody tr { border-bottom: 1px solid rgba(255,179,71,.1); transition: var(--transition); }
    tbody tr:last-child { border-bottom: none; }
    tbody tr:hover { background: var(--amber-pale); }
    tbody td { padding: 13px 18px; font-size: .84rem; color: var(--charcoal); vertical-align: middle; }
    .cat-id { font-weight: 700; color: var(--amber); }
    .cat-name { font-weight: 600; }
    .action-btn { display: inline-flex; align-items: center; gap: 5px; padding: 6px 13px; border-radius: 50px; font-size: .74rem; font-weight: 600; text-decoration: none; transition: var(--transition); border: none; cursor: pointer; font-family: 'Poppins', sans-serif; }
    .btn-edit   { background: var(--amber-pale); color: var(--amber); border: 1px solid rgba(255,179,71,.3); }
    .btn-edit:hover { background: var(--amber); color: #fff; }
    .btn-delete { background: #FEE2E2; color: #991B1B; border: 1px solid rgba(153,27,27,.15); }
    .btn-delete:hover { background: #ef4444; color: #fff; }
    .no-data { text-align: center; padding: 50px 20px; color: var(--muted); font-size: .88rem; }
    .no-data i { font-size: 2.5rem; color: var(--amber-light); display: block; margin-bottom: 12px; }

    /* ══ REVEAL ══ */
    .reveal { opacity: 0; transform: translateY(16px); transition: opacity .55s ease, transform .55s ease; }
    .reveal.visible { opacity: 1; transform: translateY(0); }
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
        <a href="admin_menu.php"><i class="fas fa-utensils"></i> Menu Items</a>
        <a href="order_management.php"><i class="fas fa-shopping-bag"></i> Orders</a>
        <a href="category_management.php" class="active"><i class="fas fa-layer-group"></i> Categories</a>
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
                <h2>Category Management</h2>
                <span><?php echo date('l, d F Y'); ?></span>
            </div>
        </div>
        <div class="topbar-right">
            <a href="admin-profile.php" class="admin-avatar">A</a>
        </div>
    </div>

    <!-- Two-column content -->
    <div class="content">

        <!-- LEFT: Form -->
        <div class="form-card reveal">
            <div class="form-card-title">
                <i class="fas <?php echo isset($_GET['edit']) ? 'fa-pen-to-square' : 'fa-plus-circle'; ?>"></i>
                <?php echo isset($_GET['edit']) ? 'Edit Category' : 'Add New Category'; ?>
            </div>

            <?php if (isset($_GET['edit'])): ?>
            <div class="edit-banner">
                <i class="fas fa-circle-info"></i>
                Editing: <strong>&nbsp;<?php echo htmlspecialchars($editData['category_name']); ?></strong>
            </div>
            <?php endif; ?>

            <form method="POST">
                <?php if (isset($_GET['edit'])): ?>
                    <input type="hidden" name="id" value="<?php echo $editData['id']; ?>">
                <?php endif; ?>

                <div class="form-group">
                    <label><i class="fas fa-layer-group"></i> &nbsp;Category Name</label>
                    <input type="text" name="category_name"
                           value="<?php echo htmlspecialchars($editData['category_name']); ?>"
                           placeholder="e.g. Italian Corner"
                           required>
                </div>

                <div class="form-actions">
                    <?php if (isset($_GET['edit'])): ?>
                        <button type="submit" name="update" class="btn-save">
                            <i class="fas fa-check"></i> Update
                        </button>
                        <a href="category_management.php" class="btn-cancel">
                            <i class="fas fa-times"></i> Cancel
                        </a>
                    <?php else: ?>
                        <button type="submit" name="save" class="btn-save">
                            <i class="fas fa-plus"></i> Add Category
                        </button>
                    <?php endif; ?>
                </div>
            </form>
        </div>

        <!-- RIGHT: Table -->
        <div class="table-side reveal">
            <div class="table-topbar">
                <div style="display:flex;align-items:center;gap:10px;">
                    <h3>All Categories</h3>
                    <span class="count-pill"><?php echo $total; ?> total</span>
                </div>
            </div>

            <div class="table-wrap">
                <table>
                    <thead>
                        <tr>
                            <th>#ID</th>
                            <th>Category Name</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php
                    if ($categories && mysqli_num_rows($categories) > 0):
                        // reset pointer (already used num_rows)
                        mysqli_data_seek($categories, 0);
                        while ($row = mysqli_fetch_assoc($categories)):
                    ?>
                    <tr>
                        <td class="cat-id">#<?php echo $row['id']; ?></td>
                        <td class="cat-name"><?php echo htmlspecialchars($row['category_name']); ?></td>
                        <td>
                            <div style="display:flex;gap:6px;">
                                <a href="category_management.php?edit=<?php echo $row['id']; ?>"
                                   class="action-btn btn-edit">
                                    <i class="fas fa-pen"></i> Edit
                                </a>
                                <a href="category_management.php?delete=<?php echo $row['id']; ?>"
                                   class="action-btn btn-delete"
                                   onclick="return confirm('Delete \'<?php echo htmlspecialchars($row['category_name'], ENT_QUOTES); ?>\'?')">
                                    <i class="fas fa-trash"></i> Delete
                                </a>
                            </div>
                        </td>
                    </tr>
                    <?php endwhile; else: ?>
                    <tr><td colspan="3" class="no-data">
                        <i class="fas fa-layer-group"></i>
                        No categories added yet.
                    </td></tr>
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
</script>
</body>
</html>