<?php
include('connection.php');
session_start();

if (!isset($_SESSION['admin_id'])) {
    header("Location: admin_login.php");
    exit();
}

$admin_id = (int)$_SESSION['admin_id'];

/* ── Fetch admin data (prepared statement) ── */
$stmt = mysqli_prepare($conn, "SELECT * FROM admins WHERE id = ? LIMIT 1");
mysqli_stmt_bind_param($stmt, "i", $admin_id);
mysqli_stmt_execute($stmt);
$admin = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
mysqli_stmt_close($stmt);

$msg      = '';
$msg_type = '';

/* ── Handle Profile Update ── */
if (isset($_POST['update_profile'])) {
    $email            = trim($_POST['email']);
    $password         = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $msg      = 'Please enter a valid email address.';
        $msg_type = 'error';

    } elseif (!empty($password) && strlen($password) < 6) {
        $msg      = 'Password must be at least 6 characters.';
        $msg_type = 'error';

    } elseif (!empty($password) && $password !== $confirm_password) {
        $msg      = 'Passwords do not match. Please try again.';
        $msg_type = 'error';

    } else {
        if (empty($password)) {
            // Email only update
            $upd = mysqli_prepare($conn, "UPDATE admins SET email=? WHERE id=?");
            mysqli_stmt_bind_param($upd, "si", $email, $admin_id);
            mysqli_stmt_execute($upd);
            mysqli_stmt_close($upd);
            $msg      = 'Profile updated successfully!';
            $msg_type = 'success';
        } else {
            // Email + password update
            $hashed = password_hash($password, PASSWORD_DEFAULT);
            $upd = mysqli_prepare($conn, "UPDATE admins SET email=?, password=? WHERE id=?");
            mysqli_stmt_bind_param($upd, "ssi", $email, $hashed, $admin_id);
            mysqli_stmt_execute($upd);
            mysqli_stmt_close($upd);
            $msg      = 'Profile and password updated successfully!';
            $msg_type = 'success';
        }

        // Refresh admin data
        $stmt = mysqli_prepare($conn, "SELECT * FROM admins WHERE id = ? LIMIT 1");
        mysqli_stmt_bind_param($stmt, "i", $admin_id);
        mysqli_stmt_execute($stmt);
        $admin = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
        mysqli_stmt_close($stmt);
    }
}

$admin_initial = strtoupper(substr($admin['email'] ?? 'A', 0, 1));
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Profile — Brew&amp;Bite</title>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,700;0,900;1,700&family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
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

    /* ══ SIDEBAR ══ */
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
    .admin-avatar { width: 38px; height: 38px; border-radius: 50%; background: linear-gradient(135deg, var(--amber), var(--ember)); display: flex; align-items: center; justify-content: center; color: #fff; font-weight: 700; font-size: .9rem; box-shadow: 0 4px 12px rgba(255,107,53,.3); }
    .mob-toggle { display: none; background: none; border: none; cursor: pointer; flex-direction: column; gap: 5px; padding: 6px; }
    .mob-toggle span { width: 22px; height: 2px; background: var(--charcoal); border-radius: 2px; display: block; }
    @media(max-width:900px){ .mob-toggle { display: flex; } }

    /* ══ CONTENT ══ */
    .content { padding: 36px; flex: 1; max-width: 1000px; }
    @media(max-width:900px){ .sidebar{transform:translateX(-100%)} .sidebar.open{transform:translateX(0)} .main-wrap{margin-left:0} .content{padding:20px} }

    /* ══ PROFILE HERO ══ */
    .profile-hero {
        background: linear-gradient(135deg, #FF8C00, #FF6B35);
        border-radius: var(--radius);
        padding: 32px 36px;
        display: flex;
        align-items: center;
        gap: 28px;
        margin-bottom: 28px;
        position: relative;
        overflow: hidden;
        box-shadow: 0 10px 36px rgba(255,107,53,.25);
    }
    .profile-hero::before { content:''; position:absolute; right:-40px; top:-40px; width:200px; height:200px; border-radius:50%; background:rgba(255,255,255,.07); pointer-events:none; }
    .profile-hero::after  { content:''; position:absolute; left:-20px; bottom:-50px; width:150px; height:150px; border-radius:50%; background:rgba(255,255,255,.05); pointer-events:none; }
    .hero-avatar {
        width: 80px; height: 80px; border-radius: 50%;
        background: rgba(255,255,255,.2);
        border: 3px solid rgba(255,255,255,.4);
        display: flex; align-items: center; justify-content: center;
        font-family: 'Playfair Display', serif;
        font-size: 2rem; font-weight: 900; color: #fff;
        flex-shrink: 0; position: relative; z-index: 1;
        backdrop-filter: blur(6px);
    }
    .hero-info { position: relative; z-index: 1; }
    .hero-info h2 { font-family: 'Playfair Display', serif; font-size: 1.5rem; font-weight: 900; color: #fff; margin-bottom: 4px; }
    .hero-info p  { font-size: .82rem; color: rgba(255,255,255,.8); margin-bottom: 8px; }
    .hero-badge   { display: inline-flex; align-items: center; gap: 6px; background: rgba(255,255,255,.18); color: #fff; font-size: .72rem; font-weight: 700; letter-spacing: 1.5px; text-transform: uppercase; padding: 4px 12px; border-radius: 50px; border: 1px solid rgba(255,255,255,.3); }

    /* ══ GRID ══ */
    .profile-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 24px; }
    @media(max-width:700px){ .profile-grid { grid-template-columns: 1fr; } }

    /* ══ CARDS ══ */
    .pcard {
        background: var(--white);
        border: 1px solid rgba(255,179,71,.15);
        border-radius: var(--radius);
        box-shadow: var(--card-shadow);
        overflow: hidden;
    }
    .pcard-header {
        padding: 18px 24px;
        border-bottom: 1px solid rgba(255,179,71,.12);
        display: flex; align-items: center; gap: 12px;
    }
    .pcard-icon {
        width: 38px; height: 38px; border-radius: 10px;
        background: var(--amber-pale);
        display: flex; align-items: center; justify-content: center;
        color: var(--amber); font-size: .9rem;
        transition: var(--transition);
    }
    .pcard:hover .pcard-icon { background: linear-gradient(135deg,var(--amber),var(--ember)); color: #fff; }
    .pcard-title { font-family: 'Playfair Display', serif; font-size: 1rem; font-weight: 700; color: var(--charcoal); }
    .pcard-body { padding: 24px; }

    /* ══ FORM ══ */
    .form-group  { margin-bottom: 18px; }
    .form-group label { font-size: .76rem; font-weight: 600; color: var(--charcoal); display: block; margin-bottom: 6px; letter-spacing: .3px; }
    .input-wrap  { position: relative; }
    .input-wrap i.fi { position: absolute; left: 13px; top: 50%; transform: translateY(-50%); color: var(--muted); font-size: .82rem; pointer-events: none; }
    .input-wrap input {
        width: 100%; padding: 11px 14px 11px 38px;
        border: 1.5px solid rgba(255,179,71,.25); border-radius: 12px;
        font-family: 'Poppins', sans-serif; font-size: .86rem;
        color: var(--charcoal); background: var(--cream);
        outline: none; transition: var(--transition);
    }
    .input-wrap input:focus { border-color: var(--amber); background: #fff; box-shadow: 0 0 0 3px rgba(255,140,0,.1); }
    .input-wrap .toggle-pw { position: absolute; right: 12px; top: 50%; transform: translateY(-50%); background: none; border: none; color: var(--muted); cursor: pointer; font-size: .82rem; padding: 0; transition: var(--transition); }
    .input-wrap .toggle-pw:hover { color: var(--amber); }

    /* pw strength */
    .pw-strength { height: 4px; border-radius: 4px; background: #eee; overflow: hidden; margin-top: 6px; }
    .pw-bar { height: 100%; width: 0; border-radius: 4px; transition: width .4s ease, background .4s ease; }
    .pw-hint { font-size: .7rem; color: var(--muted); margin-top: 3px; }

    /* ══ BUTTONS ══ */
    .btn-save {
        width: 100%; padding: 13px;
        background: linear-gradient(135deg, var(--amber), var(--ember));
        color: #fff; border: none; border-radius: 12px;
        font-family: 'Poppins', sans-serif; font-size: .9rem; font-weight: 700;
        cursor: pointer; transition: var(--transition);
        display: flex; align-items: center; justify-content: center; gap: 8px;
        margin-top: 4px;
    }
    .btn-save:hover { background: linear-gradient(135deg, var(--ember-dark), #c44d25); transform: translateY(-2px); box-shadow: 0 8px 24px rgba(255,107,53,.35); }

    /* ══ ALERT ══ */
    .alert-box { border-radius: 12px; padding: 12px 16px; font-size: .82rem; margin-bottom: 20px; display: flex; align-items: center; gap: 10px; animation: fadeUp .3s ease both; }
    .alert-success { background: #f0faf0; border: 1px solid #b2dfb2; color: #16a34a; }
    .alert-error   { background: #fff0f0; border: 1px solid #f5c6c6; color: #dc2626; }

    /* ══ INFO ROWS ══ */
    .info-row { display: flex; align-items: center; gap: 12px; padding: 12px 0; border-bottom: 1px dashed rgba(255,179,71,.2); font-size: .84rem; }
    .info-row:last-child { border-bottom: none; }
    .info-row i { color: var(--amber); width: 16px; text-align: center; font-size: .85rem; flex-shrink: 0; }
    .info-row .lbl { color: var(--muted); font-weight: 500; min-width: 70px; }
    .info-row .val { color: var(--charcoal); font-weight: 600; }

    /* ══ SECURITY TIPS ══ */
    .tip-list { list-style: none; display: flex; flex-direction: column; gap: 10px; }
    .tip-list li { display: flex; align-items: flex-start; gap: 10px; font-size: .8rem; color: var(--muted); line-height: 1.55; }
    .tip-list li i { color: var(--amber); margin-top: 1px; flex-shrink: 0; }

    /* ══ REVEAL ══ */
    .reveal { opacity: 0; transform: translateY(18px); transition: opacity .6s ease, transform .6s ease; }
    .reveal.visible { opacity: 1; transform: translateY(0); }
    .reveal-delay-1 { transition-delay: .08s; }
    .reveal-delay-2 { transition-delay: .16s; }

    @keyframes fadeUp { from{opacity:0;transform:translateY(12px)} to{opacity:1;transform:translateY(0)} }
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
        <a href="admin_analytics.php"><i class="fas fa-chart-bar"></i> Analytics</a>
    </nav>
    <div class="sidebar-footer">
        <a href="admin-profile.php" class="active"><i class="fas fa-user-circle"></i> Profile</a>
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
                <h2>Profile Management</h2>
                <span><?php echo date('l, d F Y'); ?></span>
            </div>
        </div>
        <div class="topbar-right">
            <div class="admin-avatar"><?php echo $admin_initial; ?></div>
        </div>
    </div>

    <!-- Content -->
    <div class="content">

        <!-- Alert -->
        <?php if($msg): ?>
        <div class="alert-box alert-<?php echo $msg_type; ?>">
            <i class="fas fa-<?php echo $msg_type === 'success' ? 'check-circle' : 'exclamation-circle'; ?>"></i>
            <?php echo htmlspecialchars($msg); ?>
        </div>
        <?php endif; ?>

        <!-- Profile Hero Banner -->
        <div class="profile-hero reveal">
            <div class="hero-avatar"><?php echo $admin_initial; ?></div>
            <div class="hero-info">
                <h2>Administrator</h2>
                <p><i class="fas fa-envelope" style="margin-right:6px;opacity:.8"></i><?php echo htmlspecialchars($admin['email'] ?? ''); ?></p>
                <span class="hero-badge"><i class="fas fa-shield-alt"></i> Admin Access</span>
            </div>
        </div>

        <!-- Two column grid -->
        <div class="profile-grid">

            <!-- ── LEFT: Update Profile Form ── -->
            <div class="pcard reveal">
                <div class="pcard-header">
                    <div class="pcard-icon"><i class="fas fa-user-edit"></i></div>
                    <span class="pcard-title">Update Profile</span>
                </div>
                <div class="pcard-body">
                    <form method="POST" novalidate>

                        <div class="form-group">
                            <label>Email Address *</label>
                            <div class="input-wrap">
                                <i class="fas fa-envelope fi"></i>
                                <input type="email" name="email"
                                       value="<?php echo htmlspecialchars($admin['email'] ?? ''); ?>"
                                       placeholder="admin@example.com" required>
                            </div>
                        </div>

                        <div class="form-group">
                            <label>New Password <span style="color:var(--muted);font-weight:400"></span></label>
                            <div class="input-wrap">
                                <i class="fas fa-lock fi"></i>
                                <input type="password" name="password" id="pw"
                                       placeholder="Min 6 characters"
                                       oninput="checkStrength(this.value)">
                                <button type="button" class="toggle-pw" onclick="togglePw('pw','pwIc')" aria-label="Toggle">
                                    <i class="fas fa-eye" id="pwIc"></i>
                                </button>
                            </div>
                            <div class="pw-strength"><div class="pw-bar" id="pwBar"></div></div>
                            <div class="pw-hint" id="pwHint">Enter a new password to change it</div>
                        </div>

                        <div class="form-group">
                            <label>Confirm New Password</label>
                            <div class="input-wrap">
                                <i class="fas fa-lock fi"></i>
                                <input type="password" name="confirm_password" id="cpw"
                                       placeholder="Repeat new password">
                                <button type="button" class="toggle-pw" onclick="togglePw('cpw','cpwIc')" aria-label="Toggle">
                                    <i class="fas fa-eye" id="cpwIc"></i>
                                </button>
                            </div>
                        </div>

                        <button type="submit" name="update_profile" class="btn-save">
                            <i class="fas fa-save"></i> Save Changes
                        </button>

                    </form>
                </div>
            </div>

            <!-- ── RIGHT: Account Info + Security Tips ── -->
            <div style="display:flex;flex-direction:column;gap:20px">

                <!-- Account Info -->
                <div class="pcard reveal reveal-delay-1">
                    <div class="pcard-header">
                        <div class="pcard-icon"><i class="fas fa-id-card"></i></div>
                        <span class="pcard-title">Account Info</span>
                    </div>
                    <div class="pcard-body">
                        <div class="info-row">
                            <i class="fas fa-hashtag"></i>
                            <span class="lbl">Admin ID</span>
                            <span class="val">#<?php echo $admin_id; ?></span>
                        </div>
                        <div class="info-row">
                            <i class="fas fa-envelope"></i>
                            <span class="lbl">Email</span>
                            <span class="val" style="word-break:break-all"><?php echo htmlspecialchars($admin['email'] ?? '—'); ?></span>
                        </div>
                        <div class="info-row">
                            <i class="fas fa-shield-alt"></i>
                            <span class="lbl">Role</span>
                            <span class="val" style="color:var(--amber)">Administrator</span>
                        </div>
                        <div class="info-row">
                            <i class="fas fa-key"></i>
                            <span class="lbl">Password</span>
                            <span class="val">••••••••</span>
                        </div>
                        <div class="info-row">
                            <i class="fas fa-calendar-alt"></i>
                            <span class="lbl">Date</span>
                            <span class="val"><?php echo date('d M Y'); ?></span>
                        </div>
                    </div>
                </div>

                <!-- Security Tips -->
                <div class="pcard reveal reveal-delay-2">
                    <div class="pcard-header">
                        <div class="pcard-icon"><i class="fas fa-shield-alt"></i></div>
                        <span class="pcard-title">Security Tips</span>
                    </div>
                    <div class="pcard-body">
                        <ul class="tip-list">
                            <li><i class="fas fa-check-circle"></i> Use at least 8 characters including numbers and symbols.</li>
                            <li><i class="fas fa-check-circle"></i> Never share your admin credentials with anyone.</li>
                            <li><i class="fas fa-check-circle"></i> Change your password regularly every 30–60 days.</li>
                            <li><i class="fas fa-check-circle"></i> Always log out after finishing your admin session.</li>
                            <li><i class="fas fa-check-circle"></i> Avoid using the same password for multiple accounts.</li>
                        </ul>
                    </div>
                </div>

                <!-- Logout Button -->
                <a href="logout.php"
                   style="display:flex;align-items:center;justify-content:center;gap:10px;padding:14px 22px;background:#fff;border:1.5px solid rgba(255,107,53,.25);border-radius:var(--radius);text-decoration:none;color:var(--ember);font-size:.86rem;font-weight:700;transition:var(--transition);box-shadow:var(--card-shadow);"
                   onmouseover="this.style.background='var(--ember)';this.style.color='#fff';this.style.borderColor='var(--ember)'"
                   onmouseout="this.style.background='#fff';this.style.color='var(--ember)';this.style.borderColor='rgba(255,107,53,.25)'">
                    <i class="fas fa-sign-out-alt"></i> Logout from Admin Panel
                </a>

            </div>
        </div><!-- /profile-grid -->

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

/* ── Password toggle ── */
function togglePw(id, icId){
    const f = document.getElementById(id);
    const i = document.getElementById(icId);
    if(f.type === 'password'){ f.type = 'text'; i.className = 'fas fa-eye-slash'; }
    else { f.type = 'password'; i.className = 'fas fa-eye'; }
}

/* ── Password strength meter ── */
function checkStrength(v){
    const bar  = document.getElementById('pwBar');
    const hint = document.getElementById('pwHint');
    if(!v){ bar.style.width='0'; hint.textContent='Enter a new password to change it'; hint.style.color='var(--muted)'; return; }
    let score = 0;
    if(v.length >= 6)  score++;
    if(v.length >= 10) score++;
    if(/[A-Z]/.test(v)) score++;
    if(/[0-9]/.test(v)) score++;
    if(/[^A-Za-z0-9]/.test(v)) score++;
    const colors  = ['','#ef4444','#f97316','#eab308','#22c55e','#16a34a'];
    const labels  = ['','Weak','Fair','Good','Strong','Very Strong'];
    const widths  = ['0%','25%','45%','65%','85%','100%'];
    bar.style.width      = widths[score]  || '0%';
    bar.style.background = colors[score]  || 'transparent';
    hint.textContent     = labels[score]  || '';
    hint.style.color     = colors[score]  || 'var(--muted)';
}

/* ── Client-side form validation ── */
document.querySelector('form').addEventListener('submit', function(e){
    const pw  = document.getElementById('pw').value;
    const cpw = document.getElementById('cpw').value;
    if(pw && pw.length < 6){
        e.preventDefault();
        alert('Password must be at least 6 characters.');
        return;
    }
    if(pw && pw !== cpw){
        e.preventDefault();
        alert('Passwords do not match.');
    }
});
</script>
</body>
</html>