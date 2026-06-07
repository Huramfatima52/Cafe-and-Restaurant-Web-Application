<?php
session_start();
if(!isset($_SESSION['user_id'])){
    header("Location: Login.php");
    exit();
}
include('connection.php');

$cart_count = array_sum($_SESSION['cart'] ?? []);
$user_id    = (int)$_SESSION['user_id'];

/* ── Fetch user profile ── */
$user_stmt = mysqli_prepare($conn, "SELECT * FROM users WHERE id = ? LIMIT 1");
mysqli_stmt_bind_param($user_stmt, "i", $user_id);
mysqli_stmt_execute($user_stmt);
$user_data = mysqli_fetch_assoc(mysqli_stmt_get_result($user_stmt));
$user_name = $user_data['name'] ?? $_SESSION['user_name'];

/* ══════════════════════════════════════
   REVIEW SUBMISSION
   DB columns: id, user_name, rating, comment, status, reply, created_at
══════════════════════════════════════ */
$review_msg  = '';
$review_type = '';

if(isset($_POST['submit_review'])){
    $rating  = (int)($_POST['rating']  ?? 0);
    $comment = trim($_POST['comment']  ?? '');

    if($rating < 1 || $rating > 5){
        $review_msg  = 'Please select a rating between 1 and 5 stars.';
        $review_type = 'error';
    } elseif(strlen($comment) < 10){
        $review_msg  = 'Please write at least 10 characters in your review.';
        $review_type = 'error';
    } else {
        /* Duplicate check: same user_name + same comment text */
        $chk = mysqli_prepare($conn,
            "SELECT id FROM reviews WHERE user_name = ? AND comment = ? LIMIT 1"
        );
        mysqli_stmt_bind_param($chk, "ss", $user_name, $comment);
        mysqli_stmt_execute($chk);
        mysqli_stmt_store_result($chk);

        if(mysqli_stmt_num_rows($chk) > 0){
            $review_msg  = 'You have already submitted this exact review.';
            $review_type = 'error';
        } else {
            $ins = mysqli_prepare($conn,
                "INSERT INTO reviews (user_name, rating, comment, status, created_at)
                 VALUES (?, ?, ?, 'Pending', NOW())"
            );
            mysqli_stmt_bind_param($ins, "sis", $user_name, $rating, $comment);

            if(mysqli_stmt_execute($ins)){
                $review_msg  = 'Your review has been submitted! It will appear after admin approval.';
                $review_type = 'success';
            } else {
                $review_msg  = 'Something went wrong. Please try again.';
                $review_type = 'error';
            }
            mysqli_stmt_close($ins);
        }
        mysqli_stmt_close($chk);
    }
}

/* ── Delete review (match by id + user_name since no user_id col) ── */
if(isset($_GET['delete_review'])){
    $rid = (int)$_GET['delete_review'];
    $del = mysqli_prepare($conn, "DELETE FROM reviews WHERE id = ? AND user_name = ?");
    mysqli_stmt_bind_param($del, "is", $rid, $user_name);
    mysqli_stmt_execute($del);
    mysqli_stmt_close($del);
    header("Location: Dashboard.php?tab=reviews");
    exit();
}

/* ── Fetch orders ── */
$orders_result = mysqli_query($conn,
    "SELECT * FROM orders WHERE user_id = $user_id ORDER BY order_date DESC"
);
$orders = []; $total_spent = 0;
while($row = mysqli_fetch_assoc($orders_result)){
    $orders[] = $row;
    $total_spent += $row['total'];
}
$order_count = count($orders);

/* ── Fetch THIS user's reviews by user_name ── */
$rev_stmt = mysqli_prepare($conn,
    "SELECT * FROM reviews WHERE user_name = ? ORDER BY created_at DESC"
);
mysqli_stmt_bind_param($rev_stmt, "s", $user_name);
mysqli_stmt_execute($rev_stmt);
$rev_result = mysqli_stmt_get_result($rev_stmt);
$my_reviews = [];
while($r = mysqli_fetch_assoc($rev_result)) $my_reviews[] = $r;
mysqli_stmt_close($rev_stmt);

/* ── Active tab ── */
$active_tab = $_GET['tab'] ?? 'orders';

/* ── Helpers ── */
function status_badge($status){
    $map = [
        'Pending'    => ['#f59e0b','#fffbeb','⏳'],
        'Processing' => ['#8b5cf6','#f5f3ff','🔄'],
        'Completed'  => ['#16a34a','#f0fdf4','✅'],
        'Delivered'  => ['#2563eb','#eff6ff','🚚'],
        'Cancelled'  => ['#dc2626','#fff5f5','❌'],
    ];
    $s = ucfirst($status);
    [$color,$bg,$icon] = $map[$s] ?? ['#888','#f5f5f5','📦'];
    return "<span style='background:$bg;color:$color;border:1px solid {$color}33;
            font-size:.68rem;font-weight:700;padding:3px 10px;border-radius:50px;
            letter-spacing:.5px;white-space:nowrap;'>$icon $s</span>";
}

function render_stars($rating, $interactive=false){
    if($interactive){
        echo '<div class="star-picker">';
        for($i=1;$i<=5;$i++){
            echo '<input type="radio" id="s'.$i.'" name="rating" value="'.$i.'" required>
                  <label for="s'.$i.'"><i class="fas fa-star"></i></label>';
        }
        echo '</div>';
    } else {
        $out = '<span>';
        for($i=1;$i<=5;$i++)
            $out .= $i<=$rating
                ? '<i class="fas fa-star" style="color:var(--amber);font-size:.8rem"></i>'
                : '<i class="far fa-star" style="color:#ddd;font-size:.8rem"></i>';
        return $out.'</span>';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard — Brew&amp;Bite</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,700;0,900;1,700&family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
    :root{--amber:#FF8C00;--amber-light:#FFB347;--amber-pale:#FFF3E0;--ember:#FF6B35;--ember-dark:#E65C2E;--cream:#FFF8F0;--charcoal:#2D2D2D;--muted:#888;--white:#fff;--card-shadow:0 8px 32px rgba(255,140,0,.12);--radius:18px;--transition:.35s cubic-bezier(.4,0,.2,1);--nav-h:68px;}
    *,*::before,*::after{box-sizing:border-box;margin:0;padding:0}
    html{scroll-behavior:smooth}
    body{font-family:'Poppins',sans-serif;background:var(--cream);overflow-x:hidden;min-height:100vh;}
    body::before{content:'';position:fixed;inset:0;background:radial-gradient(ellipse 700px 500px at 10% 20%,rgba(255,179,71,.18) 0%,transparent 70%),radial-gradient(ellipse 500px 400px at 90% 80%,rgba(255,107,53,.12) 0%,transparent 60%);pointer-events:none;z-index:0;}

    .topbar{position:fixed;top:0;left:0;right:0;z-index:100;display:flex;align-items:center;justify-content:space-between;padding:0 32px;height:var(--nav-h);background:rgba(255,255,255,.92);backdrop-filter:blur(16px);border-bottom:1px solid rgba(255,179,71,.25);}
    .brand{font-family:'Playfair Display',serif;font-weight:900;font-size:1.5rem;color:var(--amber);letter-spacing:-.5px;text-decoration:none;}
    .brand span{color:var(--ember);}
    .topbar-right{display:flex;align-items:center;gap:16px;}
    .topbar-nav{display:flex;align-items:center;gap:4px;}
    .topbar-nav a{color:var(--charcoal);text-decoration:none;font-size:.82rem;font-weight:500;padding:6px 12px;border-radius:50px;transition:var(--transition);}
    .topbar-nav a:hover{background:var(--amber-pale);color:var(--amber);}
    .topbar-avatar{width:38px;height:38px;border-radius:50%;background:linear-gradient(135deg,var(--amber),var(--ember));display:flex;align-items:center;justify-content:center;color:#fff;font-weight:700;font-size:.95rem;box-shadow:0 2px 10px rgba(255,107,53,.35);}
    .logout-btn{display:flex;align-items:center;gap:7px;padding:8px 18px;border-radius:50px;background:transparent;border:1.5px solid rgba(255,59,59,.4);color:#ff3b3b;font-family:'Poppins',sans-serif;font-size:.82rem;font-weight:600;text-decoration:none;transition:var(--transition);}
    .logout-btn:hover{background:#ff3b3b;color:#fff;border-color:#ff3b3b;}
    @media(max-width:768px){.topbar{padding:0 18px;}.topbar-nav{display:none;}}

    .page-wrap{position:relative;z-index:1;padding:calc(var(--nav-h) + 32px) 24px 70px;max-width:1140px;margin:0 auto;}

    .hero{text-align:center;margin-bottom:32px;animation:fadeUp .6s ease both;}
    .hero-pill{display:inline-flex;align-items:center;gap:8px;background:linear-gradient(135deg,var(--amber),var(--ember));color:#fff;font-size:.7rem;font-weight:700;letter-spacing:2px;text-transform:uppercase;padding:5px 16px;border-radius:50px;margin-bottom:12px;}
    .hero h1{font-family:'Playfair Display',serif;font-size:clamp(1.7rem,4vw,2.5rem);font-weight:900;color:var(--charcoal);margin-bottom:6px;}
    .hero h1 .hi{background:linear-gradient(90deg,var(--amber),var(--ember));-webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text;}
    .hero p{color:var(--muted);font-size:.88rem;}

    .stats-strip{display:grid;grid-template-columns:repeat(4,1fr);gap:14px;margin-bottom:24px;animation:fadeUp .6s .08s ease both;}
    .stat-card{background:var(--white);border:1px solid rgba(255,179,71,.2);border-radius:16px;padding:18px 16px;text-align:center;box-shadow:var(--card-shadow);transition:var(--transition);position:relative;overflow:hidden;}
    .stat-card::after{content:'';position:absolute;bottom:0;left:0;right:0;height:3px;background:linear-gradient(90deg,var(--amber),var(--ember));transform:scaleX(0);transform-origin:left;transition:var(--transition);}
    .stat-card:hover{transform:translateY(-4px);box-shadow:0 14px 36px rgba(255,140,0,.2);}
    .stat-card:hover::after{transform:scaleX(1);}
    .stat-icon{width:42px;height:42px;border-radius:11px;background:var(--amber-pale);display:flex;align-items:center;justify-content:center;font-size:1.1rem;color:var(--amber);margin:0 auto 8px;}
    .stat-value{font-size:1.5rem;font-weight:700;color:var(--charcoal);line-height:1;}
    .stat-label{font-size:.72rem;color:var(--muted);margin-top:4px;font-weight:500;}
    @media(max-width:700px){.stats-strip{grid-template-columns:repeat(2,1fr);}}

    .content-grid{display:grid;grid-template-columns:280px 1fr;gap:20px;align-items:start;}
    @media(max-width:900px){.content-grid{grid-template-columns:1fr;}}

    .sidebar-card{background:var(--white);border:1px solid rgba(255,179,71,.2);border-radius:var(--radius);box-shadow:var(--card-shadow);padding:24px;position:sticky;top:calc(var(--nav-h) + 20px);animation:fadeUp .6s .12s ease both;}
    .profile-avatar-lg{width:70px;height:70px;border-radius:50%;background:linear-gradient(135deg,var(--amber),var(--ember));display:flex;align-items:center;justify-content:center;font-size:1.9rem;color:#fff;font-weight:700;box-shadow:0 6px 20px rgba(255,107,53,.35);margin:0 auto 10px;}
    .badge-member{background:var(--amber-pale);color:var(--amber);font-size:.68rem;font-weight:700;letter-spacing:1px;text-transform:uppercase;padding:3px 12px;border-radius:50px;border:1px solid rgba(255,179,71,.4);display:block;text-align:center;margin-bottom:16px;}
    .profile-row{display:flex;align-items:center;gap:10px;padding:9px 0;border-bottom:1px dashed rgba(255,179,71,.2);font-size:.83rem;color:var(--charcoal);}
    .profile-row:last-of-type{border-bottom:none;}
    .profile-row i{color:var(--amber);width:16px;text-align:center;font-size:.85rem;}
    .divider{height:1px;background:linear-gradient(90deg,transparent,rgba(255,179,71,.3),transparent);margin:12px 0;}

    .tab-nav{display:flex;gap:6px;margin-bottom:20px;background:var(--white);border:1px solid rgba(255,179,71,.2);border-radius:14px;padding:6px;box-shadow:var(--card-shadow);animation:fadeUp .6s .16s ease both;flex-wrap:wrap;}
    .tab-btn{flex:1;min-width:100px;display:flex;align-items:center;justify-content:center;gap:7px;padding:10px 16px;border-radius:10px;font-family:'Poppins',sans-serif;font-size:.8rem;font-weight:600;color:var(--muted);text-decoration:none;transition:var(--transition);border:none;background:transparent;cursor:pointer;white-space:nowrap;}
    .tab-btn:hover{background:var(--amber-pale);color:var(--amber);}
    .tab-btn.active{background:linear-gradient(135deg,var(--amber),var(--ember));color:#fff;box-shadow:0 4px 14px rgba(255,107,53,.3);}
    .tab-btn .tab-count{border-radius:50px;font-size:.65rem;padding:1px 6px;font-weight:700;}
    .tab-btn.active .tab-count{background:rgba(255,255,255,.25);}
    .tab-btn:not(.active) .tab-count{background:var(--amber-pale);color:var(--amber);}

    .tab-panel{display:none;animation:fadeUp .4s ease both;}
    .tab-panel.active{display:block;}

    .panel-card{background:var(--white);border:1px solid rgba(255,179,71,.18);border-radius:var(--radius);box-shadow:var(--card-shadow);overflow:hidden;}
    .panel-card-header{padding:20px 24px;border-bottom:1px solid rgba(255,179,71,.13);display:flex;align-items:center;justify-content:space-between;}
    .panel-card-title{font-family:'Playfair Display',serif;font-size:1.1rem;font-weight:700;color:var(--charcoal);}

    .order-item{padding:16px 24px;border-bottom:1px solid rgba(255,179,71,.1);transition:var(--transition);display:grid;grid-template-columns:auto 1fr auto auto;gap:14px;align-items:center;}
    .order-item:last-child{border-bottom:none;}
    .order-item:hover{background:rgba(255,243,224,.4);}
    .order-num{width:36px;height:36px;border-radius:10px;background:var(--amber-pale);display:flex;align-items:center;justify-content:center;font-size:.7rem;font-weight:700;color:var(--amber);flex-shrink:0;}
    .order-items-text{font-size:.82rem;font-weight:600;color:var(--charcoal);margin-bottom:2px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;max-width:280px;}
    .order-date{font-size:.72rem;color:var(--muted);}
    .order-price{font-family:'Playfair Display',serif;font-size:.98rem;font-weight:700;color:var(--amber);white-space:nowrap;text-align:right;}
    .order-payment{font-size:.68rem;color:var(--muted);text-align:right;margin-top:2px;}
    @media(max-width:700px){.order-item{grid-template-columns:auto 1fr;}.order-payment,.order-price{display:none;}.order-items-text{max-width:180px;}}

    /* Review form */
    .review-form-card{background:var(--white);border:1px solid rgba(255,179,71,.2);border-radius:var(--radius);box-shadow:var(--card-shadow);padding:28px;margin-bottom:20px;}
    .review-form-title{font-family:'Playfair Display',serif;font-size:1.15rem;font-weight:700;color:var(--charcoal);margin-bottom:4px;}
    .review-form-sub{font-size:.8rem;color:var(--muted);margin-bottom:22px;}
    .star-picker{display:flex;flex-direction:row-reverse;gap:4px;margin-bottom:4px;}
    .star-picker input{display:none;}
    .star-picker label{font-size:2rem;color:#ddd;cursor:pointer;transition:color .15s;}
    .star-picker label:hover,.star-picker label:hover ~ label,.star-picker input:checked ~ label{color:var(--amber);}
    .star-hint{font-size:.74rem;color:var(--muted);margin-bottom:16px;}
    .form-group-rv{margin-bottom:16px;}
    .form-group-rv label{font-size:.78rem;font-weight:600;color:var(--charcoal);display:block;margin-bottom:6px;}
    .rv-input{width:100%;padding:11px 14px;border:1.5px solid rgba(255,179,71,.25);border-radius:12px;font-family:'Poppins',sans-serif;font-size:.86rem;color:var(--charcoal);background:var(--cream);outline:none;transition:var(--transition);}
    .rv-input:focus{border-color:var(--amber);background:#fff;box-shadow:0 0 0 3px rgba(255,140,0,.1);}
    textarea.rv-input{resize:vertical;min-height:110px;}
    .char-count{font-size:.7rem;color:var(--muted);text-align:right;margin-top:3px;}
    .btn-submit-rv{display:inline-flex;align-items:center;gap:8px;padding:12px 26px;background:linear-gradient(135deg,var(--amber),var(--ember));color:#fff;border:none;border-radius:50px;font-family:'Poppins',sans-serif;font-size:.88rem;font-weight:700;cursor:pointer;transition:var(--transition);}
    .btn-submit-rv:hover{background:linear-gradient(135deg,var(--ember-dark),#c44d25);transform:translateY(-2px);box-shadow:0 8px 24px rgba(255,107,53,.35);}

    /* Reviews list */
    .review-item{padding:20px 24px;border-bottom:1px solid rgba(255,179,71,.1);transition:var(--transition);}
    .review-item:last-child{border-bottom:none;}
    .review-item:hover{background:rgba(255,243,224,.3);}
    .rv-header{display:flex;align-items:flex-start;justify-content:space-between;gap:12px;margin-bottom:8px;}
    .rv-meta{display:flex;align-items:center;gap:10px;flex-wrap:wrap;}
    .rv-status-pill{font-size:.65rem;font-weight:700;padding:2px 9px;border-radius:50px;letter-spacing:.5px;}
    .rv-status-pill.pending{background:#fffbeb;color:#f59e0b;border:1px solid #fde68a;}
    .rv-status-pill.approved{background:#f0fdf4;color:#16a34a;border:1px solid #bbf7d0;}
    .rv-comment-text{font-size:.85rem;color:#555;line-height:1.65;font-style:italic;margin-bottom:8px;}
    .rv-comment-text::before{content:open-quote;}
    .rv-comment-text::after{content:close-quote;}
    .rv-footer{display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:8px;}
    .rv-date{font-size:.7rem;color:var(--muted);}
    .rv-delete{display:inline-flex;align-items:center;gap:5px;padding:5px 12px;border-radius:8px;background:#fff0f0;color:#dc2626;font-size:.72rem;font-weight:600;text-decoration:none;border:1px solid #f5c6c6;transition:var(--transition);}
    .rv-delete:hover{background:#dc2626;color:#fff;}
    .rv-reply{background:var(--amber-pale);border-left:3px solid var(--amber);border-radius:0 8px 8px 0;padding:10px 14px;margin-top:8px;font-size:.8rem;color:var(--charcoal);}
    .rv-reply strong{color:var(--ember);font-size:.72rem;text-transform:uppercase;letter-spacing:.5px;display:block;margin-bottom:3px;}

    .empty-state{text-align:center;padding:50px 20px;}
    .empty-state .em-icon{font-size:3rem;margin-bottom:12px;opacity:.5;display:block;}
    .empty-state p{color:var(--muted);font-size:.86rem;margin-bottom:4px;}
    .empty-state small{font-size:.76rem;color:#bbb;}

    .alert-box{border-radius:12px;padding:12px 16px;font-size:.83rem;margin-bottom:18px;display:flex;align-items:center;gap:10px;}
    .alert-success{background:#f0faf0;border:1px solid #b2dfb2;color:#16a34a;}
    .alert-error{background:#fff0f0;border:1px solid #f5c6c6;color:#dc2626;}

    .quick-links{display:grid;grid-template-columns:repeat(4,1fr);gap:12px;margin-top:20px;animation:fadeUp .6s .2s ease both;}
    .quick-link{background:var(--white);border:1px solid rgba(255,179,71,.18);border-radius:14px;padding:16px 12px;text-align:center;text-decoration:none;transition:var(--transition);box-shadow:0 3px 12px rgba(255,140,0,.07);}
    .quick-link:hover{background:var(--amber-pale);border-color:var(--amber-light);transform:translateY(-4px);box-shadow:0 10px 28px rgba(255,140,0,.18);}
    .quick-link i{font-size:1.3rem;color:var(--amber);margin-bottom:7px;display:block;transition:var(--transition);}
    .quick-link:hover i{color:var(--ember);transform:scale(1.15);}
    .quick-link span{font-size:.73rem;font-weight:600;color:var(--charcoal);}
    @media(max-width:600px){.quick-links{grid-template-columns:repeat(2,1fr);}}

    .cta-btn{display:inline-flex;align-items:center;gap:8px;padding:10px 22px;background:linear-gradient(135deg,var(--amber),var(--ember));color:#fff;text-decoration:none;border-radius:50px;font-weight:600;font-size:.84rem;box-shadow:0 4px 18px rgba(255,107,53,.28);transition:var(--transition);border:none;cursor:pointer;}
    .cta-btn:hover{transform:translateY(-2px);box-shadow:0 8px 28px rgba(255,107,53,.38);color:#fff;}

    @keyframes fadeUp{from{opacity:0;transform:translateY(20px);}to{opacity:1;transform:translateY(0);}}
    </style>
</head>
<body>

<nav class="topbar">
    <a class="brand" href="index.php">Brew<span>&</span>Bite</a>
    <div class="topbar-right">
        <div class="topbar-nav">
            <a href="index.php">Home</a>
            <a href="Menu.php">Menu</a>
            <a href="Cart.php"><i class="fas fa-shopping-bag"></i> Cart
                <?php if($cart_count>0): ?>
                <span style="background:var(--ember);color:#fff;border-radius:50px;font-size:.65rem;padding:1px 6px;font-weight:700;"><?php echo $cart_count; ?></span>
                <?php endif; ?>
            </a>
        </div>
        <div class="topbar-avatar"><?php echo strtoupper(substr($user_name,0,1)); ?></div>
        <a class="logout-btn" href="logout.php"><i class="fas fa-sign-out-alt"></i> <span>Logout</span></a>
    </div>
</nav>

<div class="page-wrap">
    <div class="hero">
        <div class="hero-pill"><i class="fas fa-user-circle"></i> Customer Dashboard</div>
        <h1>Welcome back, <span class="hi"><?php echo htmlspecialchars(explode(' ',$user_name)[0]); ?></span>!</h1>
        <p>Manage your orders, write reviews and explore our menu.</p>
    </div>

    <div class="stats-strip">
        <div class="stat-card"><div class="stat-icon"><i class="fas fa-receipt"></i></div><div class="stat-value"><?php echo $order_count; ?></div><div class="stat-label">Total Orders</div></div>
        <div class="stat-card"><div class="stat-icon"><i class="fas fa-wallet"></i></div><div class="stat-value">Rs <?php echo $total_spent>=1000?number_format($total_spent/1000,1).'K':number_format($total_spent); ?></div><div class="stat-label">Total Spent</div></div>
        <div class="stat-card"><div class="stat-icon"><i class="fas fa-star"></i></div><div class="stat-value"><?php echo count($my_reviews); ?></div><div class="stat-label">Reviews Given</div></div>
        <div class="stat-card"><div class="stat-icon"><i class="fas fa-medal"></i></div><div class="stat-value"><?php echo $order_count>=10?'Gold':($order_count>=5?'Silver':'New'); ?></div><div class="stat-label">Member Status</div></div>
    </div>

    <div class="content-grid">
        <!-- SIDEBAR -->
        <div class="sidebar-card">
            <div style="text-align:center;margin-bottom:6px">
                <div class="profile-avatar-lg"><?php echo strtoupper(substr($user_name,0,1)); ?></div>
                <span class="badge-member"><?php echo $order_count>=15?'🥇 Gold Member':($order_count>=10?'🥈 Silver Member':($order_count>=5?'🥉 Bronze Member':'✦ Member')); ?></span>
            </div>
            <div class="divider"></div>
            <div class="profile-row"><i class="fas fa-id-badge"></i><span><?php echo htmlspecialchars($user_name); ?></span></div>
            <div class="profile-row"><i class="fas fa-envelope"></i><span style="word-break:break-all;font-size:.78rem"><?php echo htmlspecialchars($user_data['email']??'Not set'); ?></span></div>
            <div class="profile-row"><i class="fas fa-phone"></i><span><?php echo htmlspecialchars($user_data['phone']??'Not set'); ?></span></div>
            <div class="profile-row"><i class="fas fa-shopping-bag"></i><span><?php echo $order_count; ?> order<?php echo $order_count!=1?'s':''; ?></span></div>
            <div class="profile-row"><i class="fas fa-star"></i><span><?php echo count($my_reviews); ?> review<?php echo count($my_reviews)!=1?'s':''; ?></span></div>
            <div class="divider"></div>
            <div style="text-align:center"><a class="cta-btn" href="Menu.php" style="width:100%;justify-content:center;"><i class="fas fa-utensils"></i> Order Now</a></div>
        </div>

        <!-- RIGHT PANEL -->
        <div>
            <div class="tab-nav">
                <a class="tab-btn <?php echo $active_tab==='orders'?'active':''; ?>" href="Dashboard.php?tab=orders">
                    <i class="fas fa-receipt"></i> My Orders <span class="tab-count"><?php echo $order_count; ?></span>
                </a>
                <a class="tab-btn <?php echo $active_tab==='review'?'active':''; ?>" href="Dashboard.php?tab=review">
                    <i class="fas fa-pen"></i> Write a Review
                </a>
                <a class="tab-btn <?php echo $active_tab==='reviews'?'active':''; ?>" href="Dashboard.php?tab=reviews">
                    <i class="fas fa-star"></i> My Reviews <span class="tab-count"><?php echo count($my_reviews); ?></span>
                </a>
            </div>

            <!-- TAB 1: ORDERS -->
            <div class="tab-panel <?php echo $active_tab==='orders'?'active':''; ?>">
                <div class="panel-card">
                    <div class="panel-card-header">
                        <span class="panel-card-title">🧾 Your Order History</span>
                        <?php if($order_count>0): ?>
                        <span style="background:var(--amber-pale);color:var(--amber);font-size:.72rem;font-weight:700;padding:3px 10px;border-radius:50px;border:1px solid rgba(255,179,71,.3);"><?php echo $order_count; ?> order<?php echo $order_count!=1?'s':''; ?></span>
                        <?php endif; ?>
                    </div>
                    <?php if(empty($orders)): ?>
                    <div class="empty-state">
                        <span class="em-icon">🛒</span>
                        <p><b>No orders yet</b></p>
                        <small>Your order history will appear here once you place your first order.</small><br>
                        <a class="cta-btn" href="Menu.php" style="margin-top:16px;display:inline-flex;"><i class="fas fa-plus"></i> Browse Menu</a>
                    </div>
                    <?php else: foreach($orders as $order):
                        $ts=$order['order_date']?strtotime($order['order_date']):0;
                        $date=$ts?date('d M Y, h:i A',$ts):$order['order_date'];
                    ?>
                    <div class="order-item">
                        <div class="order-num">#<?php echo $order['o_id']; ?></div>
                        <div>
                            <div class="order-items-text" title="<?php echo htmlspecialchars($order['items']); ?>"><?php echo htmlspecialchars($order['items']); ?></div>
                            <div class="order-date"><i class="fas fa-clock" style="font-size:.62rem;margin-right:3px;"></i><?php echo $date; ?></div>
                        </div>
                        <div><?php echo status_badge($order['status']); ?></div>
                        <div>
                            <div class="order-price">Rs <?php echo number_format($order['total']); ?></div>
                            <div class="order-payment"><i class="fas fa-<?php echo strpos(strtolower($order['payment_method']??''),'online')!==false?'credit-card':'money-bill-wave'; ?>" style="font-size:.6rem;"></i> <?php echo htmlspecialchars($order['payment_method']??'COD'); ?></div>
                        </div>
                    </div>
                    <?php endforeach; endif; ?>
                </div>
            </div>

            <!-- TAB 2: WRITE REVIEW -->
            <div class="tab-panel <?php echo $active_tab==='review'?'active':''; ?>">
                <?php if($review_msg): ?>
                <div class="alert-box alert-<?php echo $review_type; ?>">
                    <i class="fas fa-<?php echo $review_type==='success'?'check-circle':'exclamation-circle'; ?>"></i>
                    <?php echo htmlspecialchars($review_msg); ?>
                </div>
                <?php endif; ?>
                <div class="review-form-card">
                    <div class="review-form-title">Share Your Experience</div>
                    <p class="review-form-sub">Your honest feedback helps us improve and helps other customers decide.</p>
                    <form method="POST" id="reviewForm" novalidate>
                        <div class="form-group-rv">
                            <label><i class="fas fa-star" style="color:var(--amber);margin-right:5px;"></i> Your Rating</label>
                            <?php render_stars(0, true); ?>
                            <div class="star-hint" id="starHint">Click a star to rate</div>
                        </div>
                        <div class="form-group-rv">
                            <label><i class="fas fa-comment-dots" style="color:var(--amber);margin-right:5px;"></i> Your Review  <span style="color:var(--muted);font-weight:400;">(min 10 characters)</span></label>
                            <textarea name="comment" class="rv-input" placeholder="Tell us about your experience — what did you love? What could be better?" required maxlength="500" oninput="document.getElementById('rvCount').textContent=this.value.length"></textarea>
                            <div class="char-count"><span id="rvCount">0</span> / 500</div>
                        </div>
                        <button type="submit" name="submit_review" class="btn-submit-rv">
                            <i class="fas fa-paper-plane"></i> Submit Review
                        </button>
                        <p style="font-size:.74rem;color:var(--muted);margin-top:12px;">
                            <i class="fas fa-info-circle" style="color:var(--amber);"></i>
                            Submitted as <b><?php echo htmlspecialchars($user_name); ?></b> · visible after admin approval.
                        </p>
                    </form>
                </div>
            </div>

            <!-- TAB 3: MY REVIEWS -->
            <div class="tab-panel <?php echo $active_tab==='reviews'?'active':''; ?>">
                <div class="panel-card">
                    <div class="panel-card-header">
                        <span class="panel-card-title">Your Reviews</span>
                        <span style="font-size:.78rem;color:var(--muted);"><?php echo count($my_reviews); ?> review<?php echo count($my_reviews)!=1?'s':''; ?></span>
                    </div>
                    <?php if(empty($my_reviews)): ?>
                    <div class="empty-state">
                        <span class="em-icon">✍️</span>
                        <p><b>No reviews yet</b></p>
                        <small>Share your experience to help other customers!</small><br>
                        <a class="cta-btn" href="Dashboard.php?tab=review" style="margin-top:14px;display:inline-flex;"><i class="fas fa-pen"></i> Write First Review</a>
                    </div>
                    <?php else: foreach($my_reviews as $rv):
                        $ts=strtotime($rv['created_at']);
                        $date=$ts?date('d M Y',$ts):$rv['created_at'];
                        $sl=strtolower($rv['status']??'pending');
                    ?>
                    <div class="review-item">
                        <div class="rv-header">
                            <div class="rv-meta">
                                <?php echo render_stars($rv['rating']); ?>
                                <span style="font-size:.72rem;color:var(--muted);"><?php echo $rv['rating']; ?>/5</span>
                                <span class="rv-status-pill <?php echo $sl; ?>"><?php echo $sl==='approved'?'✓ Approved':'⏳ Pending'; ?></span>
                            </div>
                            <a href="Dashboard.php?delete_review=<?php echo $rv['id']; ?>&tab=reviews" class="rv-delete" onclick="return confirm('Delete this review?')">
                                <i class="fas fa-trash"></i> Delete
                            </a>
                        </div>
                        <div class="rv-comment-text"><?php echo htmlspecialchars($rv['comment']); ?></div>
                        <?php if(!empty($rv['reply'])): ?>
                        <div class="rv-reply">
                            <strong><i class="fas fa-reply"></i> Brew&amp;Bite replied:</strong>
                            <?php echo htmlspecialchars($rv['reply']); ?>
                        </div>
                        <?php endif; ?>
                        <div class="rv-footer">
                            <div class="rv-date"><i class="fas fa-calendar-alt" style="font-size:.62rem;margin-right:3px;"></i><?php echo $date; ?></div>
                        </div>
                    </div>
                    <?php endforeach; endif; ?>
                </div>
            </div>

            <!-- QUICK LINKS -->
            <div class="quick-links">
                <a class="quick-link" href="index.php"><i class="fas fa-home"></i><span>Home</span></a>
                <a class="quick-link" href="Menu.php"><i class="fas fa-book-open"></i><span>Menu</span></a>
                <a class="quick-link" href="Cart.php"><i class="fas fa-shopping-cart"></i><span>My Cart</span></a>
                <a class="quick-link" href="ContactUs.php"><i class="fas fa-phone-alt"></i><span>Contact Us</span></a>
            </div>
        </div>
    </div>
</div>

<script>
const hints=['','Terrible 😞','Below Average 😕','Average 😐','Good 😊','Excellent! 🤩'];
document.querySelectorAll('.star-picker input').forEach(inp=>{
    inp.addEventListener('change',()=>{
        const h=document.getElementById('starHint');
        h.textContent=hints[inp.value]||'';
        h.style.color=inp.value>=4?'var(--amber)':'var(--muted)';
    });
});
document.getElementById('reviewForm')?.addEventListener('submit',function(e){
    if(!document.querySelector('input[name="rating"]:checked')){e.preventDefault();alert('Please give a star rating.');return;}
    if(document.querySelector('textarea[name="comment"]').value.trim().length<10){e.preventDefault();alert('Review must be at least 10 characters.');return;}
});
</script>
</body>
</html>