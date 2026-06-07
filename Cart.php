<?php
session_start();
include('connection.php');

$cart  = $_SESSION['cart'] ?? [];
$total = 0;
$items = [];

foreach($cart as $id => $qty){
    $q   = mysqli_query($conn, "SELECT * FROM products WHERE id='" . mysqli_real_escape_string($conn,$id) . "'");
    $row = mysqli_fetch_assoc($q);
    if(!$row) continue;
    $row['qty']      = $qty;
    $row['subtotal'] = $row['price'] * $qty;
    $total          += $row['subtotal'];
    $items[]         = $row;
}

$item_count = array_sum($cart);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Your Cart — Brew&amp;Bite</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,700;0,900;1,700&family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
    :root{
        --amber:#FF8C00;--amber-light:#FFB347;--amber-pale:#FFF3E0;
        --ember:#FF6B35;--ember-dark:#E65C2E;--cream:#FFF8F0;
        --charcoal:#2D2D2D;--muted:#888;--white:#fff;
        --radius:18px;--transition:.35s cubic-bezier(.4,0,.2,1);--nav-h:68px;
    }
    *,*::before,*::after{box-sizing:border-box;margin:0;padding:0}
    html{scroll-behavior:smooth}
    body{font-family:'Poppins',sans-serif;background:var(--cream);color:var(--charcoal);min-height:100vh;}

    /* ── NAVBAR ── */
    .navbar-bb{position:fixed;top:0;left:0;right:0;height:var(--nav-h);z-index:500;display:flex;align-items:center;justify-content:space-between;padding:0 40px;background:rgba(255,255,255,.95);backdrop-filter:blur(18px);border-bottom:1px solid rgba(255,179,71,.22);transition:box-shadow .3s}
    .navbar-bb.scrolled{box-shadow:0 4px 24px rgba(255,140,0,.12)}
    .nav-brand{font-family:'Playfair Display',serif;font-weight:900;font-size:1.6rem;color:var(--amber);text-decoration:none;letter-spacing:-.5px}
    .nav-brand span{color:var(--ember)}
    .nav-links{display:flex;align-items:center;gap:6px;list-style:none}
    .nav-links a{color:var(--charcoal);text-decoration:none;font-size:.85rem;font-weight:500;padding:7px 14px;border-radius:50px;transition:var(--transition)}
    .nav-links a:hover,.nav-links a.active{background:var(--amber-pale);color:var(--amber)}
    .nav-links .cart-link{display:flex;align-items:center;gap:6px;background:linear-gradient(135deg,var(--amber),var(--ember));color:#fff !important;padding:8px 18px;border-radius:50px;font-weight:600}
    .nav-links .cart-link:hover{background:linear-gradient(135deg,var(--ember-dark),#c44d25);transform:translateY(-1px)}
    .hamburger{display:none;flex-direction:column;gap:5px;cursor:pointer;padding:6px}
    .hamburger span{width:24px;height:2.5px;background:var(--charcoal);border-radius:2px;transition:var(--transition)}
    @media(max-width:900px){
        .hamburger{display:flex}
        .nav-links{position:fixed;top:var(--nav-h);left:0;right:0;background:rgba(255,255,255,.97);backdrop-filter:blur(18px);flex-direction:column;padding:20px 30px 30px;gap:4px;border-bottom:1px solid rgba(255,179,71,.2);transform:translateY(-120%);transition:transform .4s cubic-bezier(.4,0,.2,1);box-shadow:0 10px 30px rgba(0,0,0,.08)}
        .nav-links.open{transform:translateY(0)}
        .nav-links a{padding:12px 16px;width:100%;border-radius:10px}
        .navbar-bb{padding:0 20px}
    }

    /* ── PAGE LAYOUT ── */
    .cart-page{
        padding-top:calc(var(--nav-h) + 40px);
        padding-bottom:60px;
        min-height:100vh;
        background:
            radial-gradient(ellipse 600px 400px at 100% 0%,rgba(255,179,71,.12) 0%,transparent 60%),
            radial-gradient(ellipse 500px 400px at 0% 80%,rgba(255,107,53,.08) 0%,transparent 60%),
            var(--cream);
    }
    .cart-inner{max-width:1100px;margin:0 auto;padding:0 24px;display:grid;grid-template-columns:1fr 360px;gap:28px;align-items:start}
    @media(max-width:900px){.cart-inner{grid-template-columns:1fr}}

    /* ── PAGE HEADER ── */
    .cart-header{margin-bottom:28px}
    .cart-header .eyebrow{display:inline-block;font-size:.7rem;font-weight:700;letter-spacing:2.5px;text-transform:uppercase;color:var(--ember);margin-bottom:8px}
    .cart-header h1{font-family:'Playfair Display',serif;font-size:clamp(1.8rem,4vw,2.4rem);font-weight:900;color:var(--charcoal);line-height:1.1}
    .cart-header h1 span{font-style:italic;background:linear-gradient(90deg,var(--amber),var(--ember));-webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text}
    .item-count-badge{display:inline-flex;align-items:center;gap:6px;background:var(--amber-pale);color:var(--amber);font-size:.78rem;font-weight:700;padding:5px 14px;border-radius:50px;margin-top:10px}

    /* ── CART ITEMS PANEL ── */
    .cart-panel{background:var(--white);border:1px solid rgba(255,179,71,.18);border-radius:var(--radius);overflow:hidden;box-shadow:0 8px 32px rgba(255,140,0,.08)}
    .cart-panel-header{padding:18px 24px;border-bottom:1px solid rgba(255,179,71,.12);display:flex;align-items:center;justify-content:space-between}
    .cart-panel-header h2{font-family:'Playfair Display',serif;font-size:1.1rem;font-weight:700;color:var(--charcoal)}
    .clear-cart-btn{font-size:.75rem;color:var(--muted);text-decoration:none;display:flex;align-items:center;gap:5px;padding:6px 12px;border-radius:50px;transition:var(--transition)}
    .clear-cart-btn:hover{background:#fff0f0;color:#c0392b}

    /* ── CART ITEM ROW ── */
    .cart-item{display:flex;align-items:center;gap:18px;padding:18px 24px;border-bottom:1px solid rgba(255,179,71,.1);transition:var(--transition);animation:fadeIn .4s ease both}
    .cart-item:last-child{border-bottom:none}
    .cart-item:hover{background:rgba(255,243,224,.4)}
    @keyframes fadeIn{from{opacity:0;transform:translateX(-10px)}to{opacity:1;transform:translateX(0)}}

    .item-icon{width:54px;height:54px;border-radius:14px;background:linear-gradient(135deg,var(--amber-pale),#ffe0b2);display:flex;align-items:center;justify-content:center;font-size:1.4rem;flex-shrink:0}
    .item-info{flex:1;min-width:0}
    .item-name{font-weight:600;font-size:.92rem;color:var(--charcoal);white-space:nowrap;overflow:hidden;text-overflow:ellipsis;margin-bottom:3px}
    .item-unit-price{font-size:.75rem;color:var(--muted)}

    /* Qty stepper */
    .qty-stepper{display:inline-flex;align-items:center;border:1.5px solid rgba(255,179,71,.3);border-radius:10px;overflow:hidden;margin-top:6px}
    .qty-stepper a{display:flex;align-items:center;justify-content:center;width:30px;height:30px;background:var(--amber-pale);color:var(--amber);text-decoration:none;font-size:.9rem;font-weight:700;transition:var(--transition)}
    .qty-stepper a:hover{background:var(--amber);color:#fff}
    .qty-stepper a.remove-at-one{background:#fff5f5;color:#e74c3c}
    .qty-stepper a.remove-at-one:hover{background:#e74c3c;color:#fff}
    .qty-display{min-width:34px;text-align:center;font-size:.85rem;font-weight:700;color:var(--charcoal);background:#fff;border-left:1.5px solid rgba(255,179,71,.2);border-right:1.5px solid rgba(255,179,71,.2);height:30px;line-height:30px;padding:0 2px}

    .item-subtotal{text-align:right;flex-shrink:0}
    .item-subtotal .sub-price{font-family:'Playfair Display',serif;font-size:1rem;font-weight:700;color:var(--charcoal)}
    .remove-btn{display:flex;align-items:center;justify-content:center;width:28px;height:28px;border-radius:8px;background:transparent;color:var(--muted);text-decoration:none;font-size:.8rem;transition:var(--transition);margin-top:4px;margin-left:auto}
    .remove-btn:hover{background:#fff0f0;color:#e74c3c}

    /* ── ADD MORE ── */
    .add-more-row{padding:16px 24px;background:var(--amber-pale);display:flex;align-items:center;justify-content:center}
    .add-more-btn{display:inline-flex;align-items:center;gap:8px;color:var(--amber);font-size:.82rem;font-weight:600;text-decoration:none;padding:8px 20px;border:1.5px dashed rgba(255,140,0,.4);border-radius:50px;transition:var(--transition)}
    .add-more-btn:hover{background:var(--white);border-color:var(--amber);color:var(--amber);box-shadow:0 4px 14px rgba(255,140,0,.15)}

    /* ── ORDER SUMMARY SIDEBAR ── */
    .summary-card{background:var(--white);border:1px solid rgba(255,179,71,.18);border-radius:var(--radius);padding:28px;box-shadow:0 8px 32px rgba(255,140,0,.08);position:sticky;top:calc(var(--nav-h) + 20px)}
    .summary-title{font-family:'Playfair Display',serif;font-size:1.15rem;font-weight:700;color:var(--charcoal);margin-bottom:20px;padding-bottom:14px;border-bottom:1px solid rgba(255,179,71,.15)}
    .summary-row{display:flex;align-items:center;justify-content:space-between;font-size:.84rem;color:var(--muted);margin-bottom:10px}
    .summary-row span:last-child{font-weight:600;color:var(--charcoal)}
    .summary-divider{height:1px;background:rgba(255,179,71,.15);margin:14px 0}
    .summary-total-row{display:flex;align-items:center;justify-content:space-between;margin-bottom:22px}
    .summary-total-row .label{font-weight:700;font-size:.95rem;color:var(--charcoal)}
    .summary-total-row .amount{font-family:'Playfair Display',serif;font-size:1.5rem;font-weight:900;background:linear-gradient(90deg,var(--amber),var(--ember));-webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text}

    .checkout-btn{
        display:flex;align-items:center;justify-content:center;gap:9px;
        width:100%;padding:14px;
        background:linear-gradient(135deg,var(--amber),var(--ember));
        color:#fff;border:none;border-radius:12px;
        font-family:'Poppins',sans-serif;font-size:.95rem;font-weight:700;
        text-decoration:none;cursor:pointer;transition:var(--transition);
    }
    .checkout-btn:hover{background:linear-gradient(135deg,var(--ember-dark),#c44d25);transform:translateY(-2px);box-shadow:0 8px 24px rgba(255,107,53,.35);color:#fff}

    .safe-pay{display:flex;align-items:center;justify-content:center;gap:6px;margin-top:12px;font-size:.72rem;color:var(--muted)}
    .safe-pay i{color:var(--amber)}

    /* Promo code box */
    .promo-wrap{display:flex;gap:8px;margin-bottom:18px}
    .promo-wrap input{flex:1;padding:10px 14px;border:1.5px solid rgba(255,179,71,.25);border-radius:10px;font-family:'Poppins',sans-serif;font-size:.82rem;color:var(--charcoal);background:var(--cream);outline:none;transition:var(--transition)}
    .promo-wrap input:focus{border-color:var(--amber);background:#fff;box-shadow:0 0 0 3px rgba(255,140,0,.1)}
    .promo-wrap button{padding:10px 16px;background:var(--charcoal);color:#fff;border:none;border-radius:10px;font-family:'Poppins',sans-serif;font-size:.78rem;font-weight:600;cursor:pointer;transition:var(--transition);white-space:nowrap}
    .promo-wrap button:hover{background:var(--amber)}

    /* ── EMPTY STATE ── */
    .empty-state{text-align:center;padding:80px 40px;max-width:420px;margin:0 auto}
    .empty-icon{font-size:4rem;margin-bottom:20px;opacity:.5}
    .empty-state h2{font-family:'Playfair Display',serif;font-size:1.6rem;font-weight:700;color:var(--charcoal);margin-bottom:10px}
    .empty-state p{font-size:.88rem;color:var(--muted);line-height:1.6;margin-bottom:28px}
    .btn-primary-bb{display:inline-flex;align-items:center;gap:9px;padding:13px 28px;background:linear-gradient(135deg,var(--amber),var(--ember));color:#fff;font-family:'Poppins',sans-serif;font-size:.9rem;font-weight:600;border-radius:50px;text-decoration:none;transition:var(--transition)}
    .btn-primary-bb:hover{transform:translateY(-2px);box-shadow:0 8px 24px rgba(255,107,53,.35);color:#fff}

    /* ── FOOTER ── */
    footer{background:#1a1a1a;padding:30px 40px;color:rgba(255,255,255,.65);margin-top:40px}
    .footer-bottom-simple{display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:12px;max-width:1200px;margin:0 auto}
    .footer-brand{font-family:'Playfair Display',serif;font-weight:900;font-size:1.4rem;color:var(--amber)}
    .footer-brand span{color:var(--ember)}
    .footer-bottom-simple p{font-size:.78rem;color:rgba(255,255,255,.35)}
    .footer-socials{display:flex;gap:8px}
    .social-link{width:34px;height:34px;border-radius:50%;background:rgba(255,255,255,.07);border:1px solid rgba(255,255,255,.1);display:flex;align-items:center;justify-content:center;color:rgba(255,255,255,.65);font-size:.82rem;text-decoration:none;transition:var(--transition)}
    .social-link:hover{background:linear-gradient(135deg,var(--amber),var(--ember));color:#fff;border-color:transparent}
    </style>
</head>
<body>

<!-- NAVBAR -->
<nav class="navbar-bb" id="mainNav">
    <a class="nav-brand" href="index.php">Brew<span>&</span>Bite</a>
    <div class="hamburger" id="hamburger" aria-label="Toggle menu" role="button" tabindex="0">
        <span></span><span></span><span></span>
    </div>
    <ul class="nav-links" id="navLinks">
        <li><a href="index.php">Home</a></li>
        <li><a href="Menu.php">Menu</a></li>
        <li><a href="AboutUs.php">About Us</a></li>
        <li><a href="ContactUs.php">Contact Us</a></li>
        <?php if(isset($_SESSION['user_id'])): ?>
            <li><a href="Dashboard.php"><i class="fas fa-user"></i> <?= htmlspecialchars($_SESSION['user_name']) ?></a></li>
        <?php else: ?>
            <li><a href="Login.php"><i class="fas fa-user"></i> Login</a></li>
        <?php endif; ?>
        <li>
            <a href="Cart.php" class="cart-link active">
                <i class="fas fa-shopping-bag"></i> Cart
                <?php if($item_count > 0): ?>
                <span style="background:#fff;color:var(--ember);border-radius:50px;font-size:.7rem;font-weight:700;padding:1px 7px;"><?= $item_count ?></span>
                <?php endif; ?>
            </a>
        </li>
    </ul>
</nav>

<!-- PAGE -->
<div class="cart-page">
    <div style="max-width:1100px;margin:0 auto;padding:0 24px">

        <!-- Header -->
        <div class="cart-header">
            <span class="eyebrow">Review Your Order</span>
            <h1>Your <span>Cart</span></h1>
            <?php if($item_count > 0): ?>
            <div class="item-count-badge"><i class="fas fa-shopping-bag"></i> <?= $item_count ?> item<?= $item_count != 1 ? 's' : '' ?> in your cart</div>
            <?php endif; ?>
        </div>

        <?php if(empty($items)): ?>
        <!-- EMPTY STATE -->
        <div class="empty-state">
            <div class="empty-icon">🛒</div>
            <h2>Your cart is empty</h2>
            <p>Looks like you haven't added anything yet. Browse our menu and find something delicious!</p>
            <a href="Menu.php" class="btn-primary-bb"><i class=""></i> Browse Menu</a>
        </div>

        <?php else: ?>
        <!-- FILLED CART — 2 column layout -->
        <div class="cart-inner">

            <!-- LEFT: Items list -->
            <div>
                <div class="cart-panel">
                    <div class="cart-panel-header">
                        <h2>Order Items</h2>
                        <a href="clear_cart.php" class="clear-cart-btn" onclick="return confirm('Clear entire cart?')">
                            <i class="fas fa-trash-alt"></i> Clear All
                        </a>
                    </div>

                    <?php foreach($items as $i => $row): ?>
                    <div class="cart-item" style="animation-delay:<?= $i * 0.07 ?>s">
                       <img src="images/<?= htmlspecialchars($row['image']) ?>" class="item-icon" style="width:54px;height:54px;border-radius:14px;object-fit:cover;" onerror="this.src='images/default.png'">
                        <div class="item-info">
                            <div class="item-name"><?= htmlspecialchars($row['item_name']) ?></div>
                            <div class="item-unit-price">Rs <?= number_format($row['price']) ?> each</div>
                            <!-- Qty stepper -->
                            <div class="qty-stepper">
                                <?php if($row['qty'] > 1): ?>
                                    <a href="update_cart.php?id=<?= $row['id'] ?>&qty=<?= $row['qty'] - 1 ?>" title="Decrease">&#8722;</a>
                                <?php else: ?>
                                    <a href="remove_from_cart.php?id=<?= $row['id'] ?>" title="Remove item" class="remove-at-one">&#8722;</a>
                                <?php endif; ?>
                                <span class="qty-display"><?= $row['qty'] ?></span>
                                <a href="update_cart.php?id=<?= $row['id'] ?>&qty=<?= $row['qty'] + 1 ?>" title="Increase">&#43;</a>
                            </div>
                        </div>
                        <div class="item-subtotal">
                            <div class="sub-price">Rs <?= number_format($row['subtotal']) ?></div>
                            <a href="remove_from_cart.php?id=<?= $row['id'] ?>" class="remove-btn" title="Remove">
                                <i class="fas fa-times"></i>
                            </a>
                        </div>
                    </div>
                    <?php endforeach; ?>

                    <div class="add-more-row">
                        <a href="Menu.php" class="add-more-btn">
                            <i class="fas fa-plus"></i> Add More Items
                        </a>
                    </div>
                </div>
            </div>

            <!-- RIGHT: Order Summary -->
            <div>
                <div class="summary-card">
                    <div class="summary-title"><i class="fas fa-receipt" style="color:var(--amber);margin-right:8px"></i>Order Summary</div>

                    <div class="summary-row">
                        <span>Subtotal (<?= $item_count ?> items)</span>
                        <span>Rs <?= number_format($total) ?></span>
                    </div>
                    <div class="summary-row">
                        <span>Delivery Fee</span>
                        <span>Rs 150</span>
                    </div>

                    <div class="summary-divider"></div>

                    <div class="summary-total-row">
                        <span class="label">Total</span>
                        <span class="amount">Rs <?= number_format($total + 150) ?></span>
                    </div>

                    <a href="<?= isset($_SESSION['user_id']) ? 'Checkout.php' : 'Login.php?redirect=Cart.php' ?>" class="checkout-btn">
                        <i class="fas fa-lock"></i>
                        <?= isset($_SESSION['user_id']) ? 'Proceed to Checkout' : 'Login to Checkout' ?>
                    </a>

                    <div class="safe-pay">
                        <i class="fas fa-shield-alt"></i> Secure &amp; Safe Checkout
                    </div>
                </div>
            </div>

        </div>
        <?php endif; ?>

    </div>
</div>

<!-- FOOTER -->
<footer>
    <div class="footer-bottom-simple">
        <span class="footer-brand">Brew<span>&</span>Bite</span>
        <p>&copy; 2026 Brew&amp;Bite. All rights reserved.</p>
        <div class="footer-socials">
            <a href="#" class="social-link"><i class="fab fa-instagram"></i></a>
            <a href="#" class="social-link"><i class="fab fa-facebook"></i></a>
            <a href="#" class="social-link"><i class="fab fa-twitter"></i></a>
        </div>
    </div>
</footer>

<script>
window.addEventListener('scroll',()=>document.getElementById('mainNav').classList.toggle('scrolled',window.scrollY>10));
const ham=document.getElementById('hamburger'),nl=document.getElementById('navLinks');
ham.addEventListener('click',()=>nl.classList.toggle('open'));

function applyPromo(){
    const val = document.getElementById('promoInput').value.trim().toUpperCase();
    if(!val){ return; }
    alert('Promo code "' + val + '" is not valid or has expired.');
}
</script>
</body>
</html>