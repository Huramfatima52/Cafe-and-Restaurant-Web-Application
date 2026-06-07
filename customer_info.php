<?php
include('connection.php');
// session_start();

// if (!isset($_SESSION['admin_id'])) {
//     header("Location: admin_login.php");
//     exit();
// }

/* ── DELETE ── */
if(isset($_GET['delete'])){
    $id = (int)$_GET['delete'];
    $stmt = mysqli_prepare($conn, "DELETE FROM users WHERE id=?");
    mysqli_stmt_bind_param($stmt, "i", $id);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
    header("Location: customer_info.php");
    exit();
}

/* ── EDIT FETCH ── */
$editData = null;
if(isset($_GET['edit'])){
    $id = (int)$_GET['edit'];
    $stmt = mysqli_prepare($conn, "SELECT * FROM users WHERE id=?");
    mysqli_stmt_bind_param($stmt, "i", $id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $editData = mysqli_fetch_assoc($result);
    mysqli_stmt_close($stmt);
}

/* ── UPDATE ── */
if(isset($_POST['update'])){
    $id       = (int)$_POST['id'];
    $fullname = trim($_POST['fullname']);
    $email    = trim($_POST['email']);
    $phone    = trim($_POST['phone']);
    $address  = trim($_POST['address']);
    $gender   = $_POST['gender'];
    $dob      = $_POST['dob'];
    $stmt = mysqli_prepare($conn, "UPDATE users SET fullname=?,email=?,phone=?,address=?,gender=?,dob=? WHERE id=?");
    mysqli_stmt_bind_param($stmt, "ssssssi", $fullname, $email, $phone, $address, $gender, $dob, $id);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
    header("Location: customer_info.php");
    exit();
}

/* ── STATS ── */
$total_customers = 0; $r = mysqli_query($conn,"SELECT COUNT(*) AS c FROM users"); if($r) $total_customers = mysqli_fetch_assoc($r)['c'];
$new_this_month  = 0; $r = mysqli_query($conn,"SELECT COUNT(*) AS c FROM users WHERE MONTH(created_at)=MONTH(NOW()) AND YEAR(created_at)=YEAR(NOW())"); if($r) $new_this_month = mysqli_fetch_assoc($r)['c'];
$male_count      = 0; $r = mysqli_query($conn,"SELECT COUNT(*) AS c FROM users WHERE gender='M'"); if($r) $male_count = mysqli_fetch_assoc($r)['c'];
$female_count    = 0; $r = mysqli_query($conn,"SELECT COUNT(*) AS c FROM users WHERE gender='F'"); if($r) $female_count = mysqli_fetch_assoc($r)['c'];

/* ── ALL USERS ── */
$users = [];
$res = mysqli_query($conn, "SELECT * FROM users ORDER BY id DESC");
if($res) while($row = mysqli_fetch_assoc($res)) $users[] = $row;
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Customer Management — Brew&amp;Bite Admin</title>
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
.stat-card-sub .up{color:#22c55e;font-weight:600}

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
.form-group input[readonly]{background:#f5f5f5;color:var(--muted);cursor:not-allowed}

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

.customers-table{width:100%;border-collapse:collapse;font-family:'Poppins',sans-serif}
.customers-table thead th{background:linear-gradient(135deg,var(--amber),var(--ember));color:#fff;padding:12px 16px;text-align:left;font-weight:600;font-size:.74rem;text-transform:uppercase;letter-spacing:.6px}
.customers-table tbody tr{border-bottom:1px solid rgba(255,179,71,.1);transition:var(--transition)}
.customers-table tbody tr:last-child{border-bottom:none}
.customers-table tbody tr:hover td{background:rgba(255,243,224,.5)}
.customers-table td{padding:13px 16px;color:var(--charcoal);font-size:.82rem;vertical-align:middle;white-space:nowrap}

/* Avatar initials */
.customer-avatar-wrap{display:flex;align-items:center;gap:10px}
.c-avatar{width:36px;height:36px;border-radius:50%;background:linear-gradient(135deg,var(--amber),var(--ember));display:flex;align-items:center;justify-content:center;font-family:'Playfair Display',serif;font-size:.8rem;font-weight:700;color:#fff;flex-shrink:0}
.c-name{font-weight:600;font-size:.84rem;color:var(--charcoal)}
.c-email-row{font-size:.72rem;color:var(--muted);margin-top:1px}

.gender-badge{display:inline-flex;align-items:center;gap:4px;padding:3px 10px;border-radius:50px;font-size:.7rem;font-weight:600}
.gender-M{background:#DBEAFE;color:#1E40AF}
.gender-F{background:#FCE7F3;color:#9D174D}
.gender-O{background:#F3F4F6;color:#374151}

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
        <a href="order_management.php"><i class="fas fa-shopping-bag"></i> Orders</a>
        <a href="category_management.php"><i class="fas fa-layer-group"></i> Categories</a>
        <div class="nav-section-label">Users</div>
        <a href="customer_info.php" class="active"><i class="fas fa-users"></i> Customers</a>
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
    <div class="topbar">
        <div style="display:flex;align-items:center;gap:14px">
            <button class="mob-toggle" id="mobToggle" aria-label="Menu"><span></span><span></span><span></span></button>
            <div class="topbar-left">
                <h2>Customer Management</h2>
                <span><?= date('l, d F Y') ?></span>
            </div>
        </div>
        <div class="topbar-right">
            <a href="admin-profile.php" class="admin-avatar">A</a>
        </div>
    </div>

    <div class="content">

        <!-- Stat Cards -->
        <div class="stat-grid">
            <div class="stat-card reveal">
                <div class="stat-card-icon"><i class="fas fa-users"></i></div>
                <div class="stat-card-label">Total Customers</div>
                <div class="stat-card-num"><?= $total_customers ?></div>
                <div class="stat-card-sub">Registered users</div>
            </div>
            <div class="stat-card reveal reveal-delay-1">
                <div class="stat-card-icon"><i class="fas fa-user-plus"></i></div>
                <div class="stat-card-label">New This Month</div>
                <div class="stat-card-num"><?= $new_this_month ?></div>
                <div class="stat-card-sub"><span class="up"><i class="fas fa-arrow-up"></i> This month</span></div>
            </div>
            <div class="stat-card reveal reveal-delay-2">
                <div class="stat-card-icon"><i class="fas fa-mars"></i></div>
                <div class="stat-card-label">Male</div>
                <div class="stat-card-num"><?= $male_count ?></div>
                <div class="stat-card-sub">Male customers</div>
            </div>
            <div class="stat-card reveal reveal-delay-3">
                <div class="stat-card-icon"><i class="fas fa-venus"></i></div>
                <div class="stat-card-label">Female</div>
                <div class="stat-card-num"><?= $female_count ?></div>
                <div class="stat-card-sub">Female customers</div>
            </div>
        </div>

        <!-- Form Card — only show in edit mode -->
        <?php if($editData): ?>
        <div class="form-card reveal">
            <div class="form-card-header">
                <div class="hdr-icon"><i class="fas fa-pen"></i></div>
                <h3>Edit Customer Info</h3>
            </div>
            <div class="edit-banner">
                <i class="fas fa-info-circle"></i>
                Editing: <strong><?= htmlspecialchars($editData['fullname']) ?></strong> — ID #<?= $editData['id'] ?>
            </div>
            <form method="POST">
                <input type="hidden" name="id" value="<?= $editData['id'] ?>">
                <div class="form-grid">
                    <div class="form-group">
                        <label><i class="fas fa-user"></i> Full Name</label>
                        <input type="text" name="fullname" value="<?= htmlspecialchars($editData['fullname']) ?>" required>
                    </div>
                    <div class="form-group">
                        <label><i class="fas fa-envelope"></i> Email</label>
                        <input type="email" name="email" value="<?= htmlspecialchars($editData['email']) ?>" required>
                    </div>
                    <div class="form-group">
                        <label><i class="fas fa-phone"></i> Phone</label>
                        <input type="text" name="phone" value="<?= htmlspecialchars($editData['phone'] ?? '') ?>">
                    </div>
                    <div class="form-group">
                        <label><i class="fas fa-venus-mars"></i> Gender</label>
                        <select name="gender">
                            <option value="M" <?= ($editData['gender']??'')==='M' ? 'selected':'' ?>>Male</option>
                            <option value="F" <?= ($editData['gender']??'')==='F' ? 'selected':'' ?>>Female</option>
                            <option value="O" <?= ($editData['gender']??'')==='O' ? 'selected':'' ?>>Other</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label><i class="fas fa-calendar-alt"></i> Date of Birth</label>
                        <input type="date" name="dob" value="<?= htmlspecialchars($editData['dob'] ?? '') ?>">
                    </div>
                    <div class="form-group">
                        <label><i class="fas fa-lock"></i> Password</label>
                        <input type="text" value="(hashed — not editable)" readonly>
                    </div>
                    <div class="form-group full-width">
                        <label><i class="fas fa-map-marker-alt"></i> Address</label>
                        <input type="text" name="address" value="<?= htmlspecialchars($editData['address'] ?? '') ?>">
                    </div>
                    <div class="form-actions">
                        <button type="submit" name="update" class="btn-save"><i class="fas fa-check"></i> Update Customer</button>
                        <a href="customer_info.php" class="btn-cancel"><i class="fas fa-times"></i> Cancel</a>
                    </div>
                </div>
            </form>
        </div>
        <?php endif; ?>

        <!-- Customers Table -->
        <div class="table-card reveal">
            <div class="table-card-header">
                <h3><i class="fas fa-users"></i> Registered Customers</h3>
                <div class="search-wrap">
                    <i class="fas fa-search"></i>
                    <input type="text" id="tableSearch" placeholder="Search customers…" oninput="filterTable()">
                </div>
            </div>
            <div style="overflow-x:auto">
                <table class="customers-table" id="custTable">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Customer</th>
                            <th>Phone</th>
                            <th>Gender</th>
                            <th>DOB</th>
                            <th>Address</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php if(empty($users)): ?>
                        <tr class="empty-row"><td colspan="7"><span class="empty-icon">👥</span>No registered customers yet.</td></tr>
                    <?php else: foreach($users as $u):
                        $initials = strtoupper(substr($u['fullname'],0,1)) . (strpos($u['fullname'],' ')!==false ? strtoupper(substr(strrchr($u['fullname'],' '),1,1)) : '');
                        $gc = match($u['gender']??'O'){ 'M'=>'gender-M','F'=>'gender-F',default=>'gender-O' };
                        $gl = match($u['gender']??'O'){ 'M'=>'Male','F'=>'Female',default=>'Other' };
                    ?>
                        <tr>
                            <td style="color:var(--muted);font-size:.75rem"><?= $u['id'] ?></td>
                            <td>
                                <div class="customer-avatar-wrap">
                                    <div class="c-avatar"><?= $initials ?></div>
                                    <div>
                                        <div class="c-name"><?= htmlspecialchars($u['fullname']) ?></div>
                                        <div class="c-email-row"><?= htmlspecialchars($u['email']) ?></div>
                                    </div>
                                </div>
                            </td>
                            <td><?= htmlspecialchars($u['phone'] ?? '—') ?></td>
                            <td><span class="gender-badge <?= $gc ?>"><?= $gl ?></span></td>
                            <td style="color:var(--muted);font-size:.76rem"><?= htmlspecialchars($u['dob'] ?? '—') ?></td>
                            <td style="max-width:160px;overflow:hidden;text-overflow:ellipsis;color:var(--muted);font-size:.76rem"><?= htmlspecialchars($u['address'] ?? '—') ?></td>
                            <td>
                                <div class="action-btns">
                                    <a class="btn-edit" href="customer_info.php?edit=<?= $u['id'] ?>"><i class="fas fa-pen"></i> Edit</a>
                                    <a class="btn-delete" href="customer_info.php?delete=<?= $u['id'] ?>" onclick="return confirm('Delete <?= htmlspecialchars($u['fullname']) ?>?')"><i class="fas fa-trash"></i> Delete</a>
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
    document.querySelectorAll('#custTable tbody tr:not(.empty-row)').forEach(row => {
        row.style.display = row.textContent.toLowerCase().includes(q) ? '' : 'none';
    });
}

window.addEventListener('load', () => {
    document.querySelectorAll('.stat-card-num').forEach(el => {
        const n = parseInt(el.textContent);
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