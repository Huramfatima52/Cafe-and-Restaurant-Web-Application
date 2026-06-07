<?php
session_start();
include('connection.php');

if(!isset($_GET['id'])){
    header("Location: Menu.php");
    exit();
}

$id = (int)$_GET['id'];
$stmt = mysqli_prepare($conn, "SELECT * FROM products WHERE id = ? AND status = 'available' LIMIT 1");
mysqli_stmt_bind_param($stmt, "i", $id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

if(mysqli_num_rows($result) === 0){
    header("Location: Menu.php");
    exit();
}

$product = mysqli_fetch_assoc($result);
mysqli_stmt_close($stmt);

$cart_count = array_sum($_SESSION['cart'] ?? []);

// Related products (same category, excluding current)
$rel_stmt = mysqli_prepare($conn,
    "SELECT * FROM products WHERE category = ? AND id != ? AND status = 'available' ORDER BY RAND() LIMIT 3"
);
mysqli_stmt_bind_param($rel_stmt, "si", $product['category'], $id);
mysqli_stmt_execute($rel_stmt);
$related = mysqli_stmt_get_result($rel_stmt);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($product['item_name']); ?> — Brew&amp;Bite</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,700;0,900;1,700&family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
    :root{--amber:#FF8C00;--amber-light:#FFB347;--amber-pale:#FFF3E0;--ember:#FF6B35;--ember-dark:#E65C2E;--cream:#FFF8F0;--charcoal:#2D2D2D;--muted:#888;--white:#fff;--radius:18px;--transition:.35s cubic-bezier(.4,0,.2,1);--nav-h:68px;}
    *,*::before,*::after{box-sizing:border-box;margin:0;padding:0}
    html{scroll-behavior:smooth}
    body{font-family:'Poppins',sans-serif;background:var(--cream);color:var(--charcoal);overflow-x:hidden}
    .navbar-bb{position:fixed;top:0;left:0;right:0;height:var(--nav-h);z-index:500;display:flex;align-items:center;justify-content:space-between;padding:0 40px;background:rgba(255,255,255,.95);backdrop-filter:blur(18px);border-bottom:1px solid rgba(255,179,71,.22);transition:box-shadow .3s}
    .navbar-bb.scrolled{box-shadow:0 4px 24px rgba(255,140,0,.12)}
    .nav-brand{font-family:'Playfair Display',serif;font-weight:900;font-size:1.6rem;color:var(--amber);text-decoration:none;letter-spacing:-.5px}
    .nav-brand span{color:var(--ember)}
    .nav-links{display:flex;align-items:center;gap:6px;list-style:none}
    .nav-links a{color:var(--charcoal);text-decoration:none;font-size:.85rem;font-weight:500;padding:7px 14px;border-radius:50px;transition:var(--transition)}
    .nav-links a:hover,.nav-links a.active{background:var(--amber-pale);color:var(--amber)}
    .nav-links .cart-link{display:flex;align-items:center;gap:6px;background:linear-gradient(135deg,var(--amber),var(--ember));color:#fff !important;padding:8px 18px;border-radius:50px;font-weight:600}
    .nav-links .cart-link:hover{background:linear-gradient(135deg,var(--ember-dark),#c44d25);transform:translateY(-1px)}
    .cart-badge{background:#fff;color:var(--ember);border-radius:50px;font-size:.72rem;font-weight:700;padding:1px 7px;min-width:22px;text-align:center}
    .hamburger{display:none;flex-direction:column;gap:5px;cursor:pointer;padding:6px}
    .hamburger span{width:24px;height:2.5px;background:var(--charcoal);border-radius:2px;transition:var(--transition)}
    @media(max-width:900px){.hamburger{display:flex}.nav-links{position:fixed;top:var(--nav-h);left:0;right:0;background:rgba(255,255,255,.97);backdrop-filter:blur(18px);flex-direction:column;padding:20px 30px 30px;gap:4px;border-bottom:1px solid rgba(255,179,71,.2);transform:translateY(-120%);transition:transform .4s cubic-bezier(.4,0,.2,1);box-shadow:0 10px 30px rgba(0,0,0,.08)}.nav-links.open{transform:translateY(0)}.nav-links a{padding:12px 16px;width:100%;border-radius:10px}.navbar-bb{padding:0 20px}}

    /* BREADCRUMB */
    .breadcrumb-bar{padding-top:calc(var(--nav-h) + 20px);padding-bottom:0}
    .breadcrumb-inner{max-width:1200px;margin:0 auto;padding:0 40px}
    .breadcrumb-list{display:flex;align-items:center;gap:8px;list-style:none;font-size:.8rem;color:var(--muted)}
    .breadcrumb-list a{color:var(--muted);text-decoration:none;transition:var(--transition)}
    .breadcrumb-list a:hover{color:var(--amber)}
    .breadcrumb-list .sep{opacity:.4}
    .breadcrumb-list .current{color:var(--charcoal);font-weight:500}

    /* MAIN */
    .pd-body{max-width:1200px;margin:0 auto;padding:30px 40px 80px}
    @media(max-width:768px){.pd-body{padding:20px 20px 60px}}
    .pd-grid{display:grid;grid-template-columns:1fr 1fr;gap:60px;align-items:start}
    @media(max-width:900px){.pd-grid{grid-template-columns:1fr;gap:30px}}

    /* IMAGE */
    .pd-img-wrap{position:relative;border-radius:24px;overflow:hidden;background:var(--amber-pale);aspect-ratio:1;box-shadow:0 20px 50px rgba(255,140,0,.18)}
    .pd-img-wrap img{width:100%;height:100%;object-fit:cover;transition:transform .5s ease}
    .pd-img-wrap:hover img{transform:scale(1.05)}
    .pd-badge{position:absolute;top:16px;left:16px;background:linear-gradient(135deg,var(--amber),var(--ember));color:#fff;font-size:.68rem;font-weight:700;padding:5px 12px;border-radius:50px;text-transform:uppercase;letter-spacing:1px}

    /* INFO */
    .pd-cat{font-size:.72rem;font-weight:700;letter-spacing:2px;text-transform:uppercase;color:var(--ember);margin-bottom:10px}
    .pd-name{font-family:'Playfair Display',serif;font-size:clamp(1.8rem,4vw,2.6rem);font-weight:900;color:var(--charcoal);margin-bottom:12px;line-height:1.15}
    .pd-stars{display:flex;align-items:center;gap:4px;margin-bottom:18px}
    .pd-stars i{color:var(--amber);font-size:.85rem}
    .pd-stars span{font-size:.82rem;color:var(--muted);margin-left:6px}
    .pd-desc{font-size:.9rem;color:var(--muted);line-height:1.75;margin-bottom:24px;padding-bottom:24px;border-bottom:1px solid rgba(255,179,71,.2)}
    .pd-price-row{display:flex;align-items:center;gap:16px;margin-bottom:28px}
    .pd-price{font-family:'Playfair Display',serif;font-size:2rem;font-weight:900;color:var(--amber)}
    .pd-per{font-size:.78rem;color:var(--muted);font-weight:400}
    .pd-status{display:inline-flex;align-items:center;gap:5px;font-size:.78rem;font-weight:600;padding:5px 12px;border-radius:50px}
    .pd-status.available{background:#f0faf0;color:#27ae60}
    .pd-status.unavailable{background:#fff0f0;color:#c0392b}

    /* QTY + CART */
    .qty-row{display:flex;align-items:center;gap:14px;margin-bottom:20px;flex-wrap:wrap}
    .qty-label{font-size:.82rem;font-weight:600;color:var(--charcoal)}
    .qty-ctrl{display:flex;align-items:center;gap:0;border:1.5px solid rgba(255,179,71,.3);border-radius:12px;overflow:hidden}
    .qty-ctrl button{width:38px;height:38px;border:none;background:var(--amber-pale);color:var(--amber);font-size:1.1rem;font-weight:700;cursor:pointer;transition:var(--transition);display:flex;align-items:center;justify-content:center}
    .qty-ctrl button:hover{background:var(--amber);color:#fff}
    .qty-ctrl input{width:50px;height:38px;border:none;text-align:center;font-family:'Poppins',sans-serif;font-size:.9rem;font-weight:600;background:var(--white);color:var(--charcoal);outline:none}

    .pd-actions{display:flex;gap:12px;flex-wrap:wrap}
    .btn-cart{display:inline-flex;align-items:center;gap:8px;padding:13px 26px;background:linear-gradient(135deg,var(--amber),var(--ember));color:#fff;font-family:'Poppins',sans-serif;font-size:.9rem;font-weight:700;border-radius:50px;text-decoration:none;transition:var(--transition);border:none;cursor:pointer}
    .btn-cart:hover{background:linear-gradient(135deg,var(--ember-dark),#c44d25);transform:translateY(-2px);box-shadow:0 8px 24px rgba(255,107,53,.35);color:#fff}
    .btn-back{display:inline-flex;align-items:center;gap:8px;padding:12px 22px;background:transparent;color:var(--charcoal);font-family:'Poppins',sans-serif;font-size:.88rem;font-weight:600;border-radius:50px;text-decoration:none;border:1.5px solid rgba(255,179,71,.3);transition:var(--transition)}
    .btn-back:hover{border-color:var(--amber);color:var(--amber);background:var(--amber-pale)}

    /* TAGS */
    .pd-tags{display:flex;flex-wrap:wrap;gap:8px;margin-top:24px}
    .pd-tag{background:var(--amber-pale);color:var(--amber);font-size:.72rem;font-weight:600;padding:4px 12px;border-radius:50px}

    /* RELATED */
    .related-section{margin-top:70px}
    .rel-title{font-family:'Playfair Display',serif;font-size:1.5rem;font-weight:900;color:var(--charcoal);margin-bottom:24px}
    .related-grid{display:grid;grid-template-columns:repeat(3,1fr);gap:20px}
    @media(max-width:768px){.related-grid{grid-template-columns:1fr}}
    .rel-card{background:var(--white);border:1px solid rgba(255,179,71,.16);border-radius:16px;overflow:hidden;transition:var(--transition);box-shadow:0 4px 18px rgba(255,140,0,.08);display:flex;flex-direction:column}
    .rel-card:hover{transform:translateY(-5px);box-shadow:0 16px 40px rgba(255,140,0,.18)}
    .rel-img{height:160px;overflow:hidden;background:var(--amber-pale)}
    .rel-img img{width:100%;height:100%;object-fit:cover;transition:transform .5s ease}
    .rel-card:hover .rel-img img{transform:scale(1.07)}
    .rel-body{padding:14px 16px}
    .rel-name{font-weight:600;font-size:.9rem;color:var(--charcoal);margin-bottom:4px}
    .rel-price{font-family:'Playfair Display',serif;font-size:1rem;color:var(--amber);font-weight:700;margin-bottom:10px}
    .rel-btns{display:flex;gap:8px}
    .rel-btns a{flex:1;text-align:center;padding:7px;border-radius:8px;font-size:.76rem;font-weight:600;text-decoration:none;transition:var(--transition)}
    .rel-view{background:var(--amber-pale);color:var(--amber)}
    .rel-view:hover{background:var(--amber);color:#fff}
    .rel-add{background:linear-gradient(135deg,var(--amber),var(--ember));color:#fff}
    .rel-add:hover{background:linear-gradient(135deg,var(--ember-dark),#c44d25);color:#fff}

    /* TOAST */
    .toast-msg{position:fixed;bottom:30px;left:50%;transform:translateX(-50%) translateY(80px);background:var(--charcoal);color:#fff;padding:12px 24px;border-radius:50px;font-size:.86rem;font-weight:500;z-index:9999;transition:transform .4s ease,opacity .4s ease;opacity:0;display:flex;align-items:center;gap:8px}
    .toast-msg.show{transform:translateX(-50%) translateY(0);opacity:1}
    .toast-msg i{color:var(--amber)}
    </style>
</head>
<body>

<button id="scrollTop" style="position:fixed;bottom:28px;right:28px;width:46px;height:46px;border-radius:50%;background:linear-gradient(135deg,var(--amber),var(--ember));color:#fff;border:none;cursor:pointer;box-shadow:0 6px 20px rgba(255,107,53,.4);opacity:0;pointer-events:none;transition:var(--transition);z-index:999;display:flex;align-items:center;justify-content:center;" aria-label="Scroll to top">
    <i class="fas fa-arrow-up"></i>
</button>

<div class="toast-msg" id="toast"><i class="fas fa-check-circle"></i> <span id="toastText">Added to cart!</span></div>

<!-- NAVBAR -->
<nav class="navbar-bb" id="mainNav">
    <a class="nav-brand" href="index.php">Brew<span>&</span>Bite</a>
    <div class="hamburger" id="hamburger" aria-label="Toggle menu" role="button" tabindex="0"><span></span><span></span><span></span></div>
    <ul class="nav-links" id="navLinks">
        <li><a href="index.php">Home</a></li>
        <li><a href="Menu.php" class="active">Menu</a></li>
        <li><a href="AboutUs.php">About Us</a></li>
        <li><a href="ContactUs.php">Contact Us</a></li>
        <?php if(isset($_SESSION['user_id'])): ?>
            <li><a href="Dashboard.php"><i class="fas fa-user-circle"></i> <?php echo htmlspecialchars($_SESSION['user_name']); ?></a></li>
        <?php else: ?>
            <li><a href="Login.php"><i class="fas fa-user"></i> Login</a></li>
        <?php endif; ?>
        <li><a href="Cart.php" class="cart-link"><i class="fas fa-shopping-bag"></i> Cart <span class="cart-badge"><?php echo $cart_count; ?></span></a></li>
    </ul>
</nav>

<!-- BREADCRUMB -->
<div class="breadcrumb-bar">
    <div class="breadcrumb-inner">
        <ul class="breadcrumb-list">
            <li><a href="index.php">Home</a></li>
            <li class="sep">/</li>
            <li><a href="Menu.php">Menu</a></li>
            <li class="sep">/</li>
            <li><a href="Menu.php?category=<?php echo urlencode($product['category']); ?>"><?php echo htmlspecialchars($product['category']); ?></a></li>
            <li class="sep">/</li>
            <li class="current"><?php echo htmlspecialchars($product['item_name']); ?></li>
        </ul>
    </div>
</div>

<!-- MAIN -->
<div class="pd-body">
    <div class="pd-grid">

        <!-- IMAGE -->
        <div>
            <div class="pd-img-wrap">
                <img src="images/<?php echo htmlspecialchars($product['image']); ?>"
                     alt="<?php echo htmlspecialchars($product['item_name']); ?>"
                     onerror="this.src='images/default.png'">
                <span class="pd-badge"><?php echo htmlspecialchars($product['category']); ?></span>
            </div>
        </div>

        <!-- INFO -->
        <div>
            <div class="pd-cat"><?php echo htmlspecialchars($product['category']); ?></div>
            <h1 class="pd-name"><?php echo htmlspecialchars($product['item_name']); ?></h1>
            <div class="pd-stars">
                <i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i>
                <i class="fas fa-star"></i><i class="fas fa-star-half-alt"></i>
                <span>4.5 (32 reviews)</span>
            </div>
            <div class="pd-desc">
                <?php echo !empty($product['description'])
                    ? htmlspecialchars($product['description'])
                    : 'A carefully crafted Brew&amp;Bite creation, prepared fresh daily with quality ingredients. Every bite is made to delight.'; ?>
            </div>
            <div class="pd-price-row">
                <div>
                    <div class="pd-price">Rs <?php echo number_format($product['price']); ?></div>
                    <div class="pd-per">per serving</div>
                </div>
                <span class="pd-status <?php echo $product['status'] === 'available' ? 'available' : 'unavailable'; ?>">
                    <i class="fas fa-<?php echo $product['status'] === 'available' ? 'check-circle' : 'times-circle'; ?>"></i>
                    <?php echo ucfirst($product['status']); ?>
                </span>
            </div>

            <?php if($product['status'] === 'available'): ?>
            <div class="qty-row">
                <span class="qty-label">Quantity:</span>
                <div class="qty-ctrl">
                    <button type="button" onclick="changeQty(-1)" aria-label="Decrease">−</button>
                    <input type="number" id="qtyInput" value="1" min="1" max="20" readonly>
                    <button type="button" onclick="changeQty(1)" aria-label="Increase">+</button>
                </div>
            </div>
            <div class="pd-actions">
                <a href="add_to_cart.php?id=<?php echo $product['id']; ?>" class="btn-cart" id="addCartBtn">
                    <i class="fas fa-shopping-bag"></i> Add to Cart
                </a>
                <a href="Menu.php" class="btn-back"><i class="fas fa-arrow-left"></i> Back to Menu</a>
            </div>
            <?php else: ?>
            <div class="pd-actions">
                <a href="Menu.php" class="btn-back"><i class="fas fa-arrow-left"></i> Back to Menu</a>
            </div>
            <?php endif; ?>

            <div class="pd-tags">
                <span class="pd-tag"><?php echo htmlspecialchars($product['category']); ?></span>
                <span class="pd-tag">Fresh Daily</span>
                <span class="pd-tag">Brew&amp;Bite</span>
            </div>
        </div>
    </div>

    <!-- RELATED -->
    <?php
    $rel_rows = [];
    while($r = mysqli_fetch_assoc($related)) $rel_rows[] = $r;
    mysqli_stmt_close($rel_stmt);
    if(!empty($rel_rows)):
    ?>
    <div class="related-section">
        <div class="rel-title">You Might Also Like</div>
        <div class="related-grid">
            <?php foreach($rel_rows as $r): ?>
            <div class="rel-card">
                <div class="rel-img">
                    <img src="images/<?php echo htmlspecialchars($r['image']); ?>"
                         alt="<?php echo htmlspecialchars($r['item_name']); ?>"
                         onerror="this.src='images/default.png'" loading="lazy">
                </div>
                <div class="rel-body">
                    <div class="rel-name"><?php echo htmlspecialchars($r['item_name']); ?></div>
                    <div class="rel-price">Rs <?php echo number_format($r['price']); ?></div>
                    <div class="rel-btns">
                        <a href="product_details.php?id=<?php echo (int)$r['id']; ?>" class="rel-view">View</a>
                        <a href="add_to_cart.php?id=<?php echo (int)$r['id']; ?>" class="rel-add"><i class="fas fa-plus"></i> Add</a>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
    <?php endif; ?>
</div>

<!-- FOOTER -->
<footer style="background:#1a1a1a;padding:30px 40px">
    <div style="max-width:1200px;margin:0 auto;display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:12px">
        <span style="font-family:'Playfair Display',serif;font-weight:900;font-size:1.4rem;color:#FF8C00">Brew<span style="color:#FF6B35">&</span>Bite</span>
        <p style="font-size:.78rem;color:rgba(255,255,255,.35)">&copy; 2026 Brew&amp;Bite. All rights reserved.</p>
    </div>
</footer>

<script>
const scrollBtn=document.getElementById('scrollTop');
window.addEventListener('scroll',()=>{scrollBtn.style.opacity=window.scrollY>300?'1':'0';scrollBtn.style.pointerEvents=window.scrollY>300?'auto':'none';document.getElementById('mainNav').classList.toggle('scrolled',window.scrollY>10)});
scrollBtn.addEventListener('click',()=>window.scrollTo({top:0,behavior:'smooth'}));
const ham=document.getElementById('hamburger'),nl=document.getElementById('navLinks');
ham.addEventListener('click',()=>nl.classList.toggle('open'));

function changeQty(delta){
    const inp=document.getElementById('qtyInput');
    let v=parseInt(inp.value)+delta;
    v=Math.max(1,Math.min(20,v));
    inp.value=v;
    const btn=document.getElementById('addCartBtn');
    if(btn) btn.href='add_to_cart.php?id=<?php echo $product['id']; ?>&qty='+v;
}

// Toast on cart click
const cartBtn=document.getElementById('addCartBtn');
if(cartBtn){
    cartBtn.addEventListener('click',function(e){
        const toast=document.getElementById('toast');
        toast.classList.add('show');
        setTimeout(()=>toast.classList.remove('show'),2500);
    });
}
</script>
</body>
</html>