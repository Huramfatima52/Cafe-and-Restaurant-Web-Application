<?php
session_start();
include('connection.php');

$cart_count = array_sum($_SESSION['cart'] ?? []);

// Fetch all categories
$categories = [];
$cat_result = mysqli_query($conn, "SELECT DISTINCT category FROM products WHERE status='available' ORDER BY category");
if (!$cat_result) die("Database error: " . mysqli_error($conn));
while ($cat = mysqli_fetch_assoc($cat_result)) {
    $categories[] = $cat['category'];
}

// Search support
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$active_cat = isset($_GET['category']) ? trim($_GET['category']) : '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Menu — Brew&amp;Bite</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
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
        --radius:      18px;
        --transition:  .35s cubic-bezier(.4,0,.2,1);
        --nav-h:       68px;
    }
    *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
    html { scroll-behavior: smooth; }
    body { font-family: 'Poppins', sans-serif; background: var(--cream); color: var(--charcoal); overflow-x: hidden; }

    /* ── NAVBAR ── */
    .navbar-bb {
        position: fixed; top: 0; left: 0; right: 0; height: var(--nav-h);
        z-index: 500; display: flex; align-items: center; justify-content: space-between;
        padding: 0 40px; background: rgba(255,255,255,.95);
        backdrop-filter: blur(18px); -webkit-backdrop-filter: blur(18px);
        border-bottom: 1px solid rgba(255,179,71,.22); transition: box-shadow .3s;
    }
    .navbar-bb.scrolled { box-shadow: 0 4px 24px rgba(255,140,0,.12); }
    .nav-brand { font-family: 'Playfair Display', serif; font-weight: 900; font-size: 1.6rem; color: var(--amber); text-decoration: none; letter-spacing: -.5px; }
    .nav-brand span { color: var(--ember); }
    .nav-links { display: flex; align-items: center; gap: 6px; list-style: none; }
    .nav-links a { color: var(--charcoal); text-decoration: none; font-size: .85rem; font-weight: 500; padding: 7px 14px; border-radius: 50px; transition: var(--transition); }
    .nav-links a:hover, .nav-links a.active { background: var(--amber-pale); color: var(--amber); }
    .nav-links .cart-link { display: flex; align-items: center; gap: 6px; background: linear-gradient(135deg, var(--amber), var(--ember)); color: #fff !important; padding: 8px 18px; border-radius: 50px; font-weight: 600; }
    .nav-links .cart-link:hover { background: linear-gradient(135deg, var(--ember-dark), #c44d25); transform: translateY(-1px); }
    .cart-badge { background: #fff; color: var(--ember); border-radius: 50px; font-size: .72rem; font-weight: 700; padding: 1px 7px; min-width: 22px; text-align: center; }
    .hamburger { display: none; flex-direction: column; gap: 5px; cursor: pointer; padding: 6px; }
    .hamburger span { width: 24px; height: 2.5px; background: var(--charcoal); border-radius: 2px; transition: var(--transition); }
    @media (max-width: 900px) {
        .hamburger { display: flex; }
        .nav-links { position: fixed; top: var(--nav-h); left: 0; right: 0; background: rgba(255,255,255,.97); backdrop-filter: blur(18px); flex-direction: column; padding: 20px 30px 30px; gap: 4px; border-bottom: 1px solid rgba(255,179,71,.2); transform: translateY(-120%); transition: transform .4s cubic-bezier(.4,0,.2,1); box-shadow: 0 10px 30px rgba(0,0,0,.08); }
        .nav-links.open { transform: translateY(0); }
        .nav-links a { padding: 12px 16px; width: 100%; border-radius: 10px; }
        .navbar-bb { padding: 0 20px; }
    }

    /* ── PAGE HEADER ── */
    .menu-header {
        padding-top: calc(var(--nav-h) + 50px);
        padding-bottom: 40px;
        background: linear-gradient(135deg, rgba(255,179,71,.12) 0%, rgba(255,107,53,.08) 100%), var(--cream);
        text-align: center;
    }
    .menu-header .eyebrow { display: inline-block; font-size: .7rem; font-weight: 700; letter-spacing: 2.5px; text-transform: uppercase; color: var(--ember); margin-bottom: 10px; }
    .menu-header h1 { font-family: 'Playfair Display', serif; font-size: clamp(2rem, 5vw, 3rem); font-weight: 900; color: var(--charcoal); margin-bottom: 12px; }
    .menu-header h1 em { font-style: italic; background: linear-gradient(90deg, var(--amber), var(--ember)); -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text; }
    .menu-header p { color: var(--muted); font-size: .92rem; max-width: 420px; margin: 0 auto 28px; line-height: 1.7; }

    /* ── SEARCH BAR ── */
    .search-wrap { max-width: 500px; margin: 0 auto; position: relative; }
    .search-wrap input {
        width: 100%; padding: 14px 50px 14px 20px;
        border: 1.5px solid rgba(255,179,71,.3); border-radius: 50px;
        font-family: 'Poppins', sans-serif; font-size: .9rem;
        background: #fff; color: var(--charcoal); outline: none;
        transition: var(--transition);
    }
    .search-wrap input:focus { border-color: var(--amber); box-shadow: 0 0 0 3px rgba(255,140,0,.12); }
    .search-wrap button {
        position: absolute; right: 6px; top: 50%; transform: translateY(-50%);
        width: 38px; height: 38px; border-radius: 50%;
        background: linear-gradient(135deg, var(--amber), var(--ember));
        border: none; color: #fff; cursor: pointer; font-size: .9rem;
        display: flex; align-items: center; justify-content: center;
        transition: var(--transition);
    }
    .search-wrap button:hover { transform: translateY(-50%) scale(1.08); }

    /* ── CATEGORY FILTER TABS ── */
    .filter-bar { background: var(--white); border-bottom: 1px solid rgba(255,179,71,.15); padding: 16px 40px; position: sticky; top: var(--nav-h); z-index: 400; }
    .filter-inner { max-width: 1200px; margin: 0 auto; display: flex; gap: 8px; overflow-x: auto; scrollbar-width: none; -ms-overflow-style: none; align-items: center; }
    .filter-inner::-webkit-scrollbar { display: none; }
    .filter-btn {
        white-space: nowrap; padding: 8px 18px; border-radius: 50px;
        border: 1.5px solid rgba(255,179,71,.25); background: transparent;
        font-family: 'Poppins', sans-serif; font-size: .8rem; font-weight: 500;
        color: var(--charcoal); cursor: pointer; transition: var(--transition);
        flex-shrink: 0; text-decoration: none;
    }
    .filter-btn:hover { border-color: var(--amber); color: var(--amber); background: var(--amber-pale); }
    .filter-btn.active { background: linear-gradient(135deg, var(--amber), var(--ember)); color: #fff; border-color: transparent; }

    /* ── MAIN CONTENT ── */
    .menu-body { max-width: 1200px; margin: 0 auto; padding: 50px 40px 80px; }
    @media (max-width: 768px) { .menu-body { padding: 30px 20px 60px; } .filter-bar { padding: 12px 20px; } }

    /* ── CATEGORY SECTION ── */
    .cat-section { margin-bottom: 60px; }
    .cat-title {
        font-family: 'Playfair Display', serif;
        font-size: 1.8rem; font-weight: 900;
        color: var(--charcoal); margin-bottom: 6px;
        display: flex; align-items: center; gap: 12px;
        /* NO ::after line — removed */
    }
    .cat-count {
        font-family: 'Poppins', sans-serif; font-size: .75rem; font-weight: 600;
        color: var(--ember); background: var(--amber-pale);
        padding: 3px 10px; border-radius: 50px;
    }

    /* ── PRODUCT GRID ── */
    .product-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(240px, 1fr));
        gap: 22px; margin-top: 24px;
    }
    .product-card {
        background: var(--white); border: 1px solid rgba(255,179,71,.16);
        border-radius: var(--radius); overflow: hidden; transition: var(--transition);
        box-shadow: 0 4px 18px rgba(255,140,0,.08);
        display: flex; flex-direction: column; position: relative;
    }
    .product-card:hover { transform: translateY(-6px); box-shadow: 0 18px 44px rgba(255,140,0,.18); }

    /* ── IMAGE: contain, no crop, no badge ── */
    .product-card-img {
        position: relative;
        width: 100%; height: 200px;
        overflow: hidden;
        background: radial-gradient(ellipse at center, #fff6e8 0%, var(--amber-pale) 100%);
        display: flex; align-items: center; justify-content: center;
        flex-shrink: 0;
    }
    .product-card-img img {
        width: 100%; height: 100%;
        object-fit: contain;
        object-position: center center;
        padding: 10px;
        display: block;
        transition: transform .5s ease;
    }
    .product-card:hover .product-card-img img { transform: scale(1.06); }
    .product-card-img .img-placeholder {
        width: 100%; height: 100%;
        display: flex; align-items: center; justify-content: center;
        font-size: 3.5rem; color: var(--amber-light);
    }

    /* ── CARD BODY ── */
    .product-body {
        padding: 14px 16px 16px;
        flex: 1; display: flex; flex-direction: column;
        min-height: 160px;
    }
    .product-stars { display: flex; align-items: center; gap: 2px; margin-bottom: 6px; flex-shrink: 0; }
    .product-stars i { color: var(--amber); font-size: .7rem; }
    .product-stars span { font-size: .7rem; color: var(--muted); margin-left: 4px; }
    .product-name { font-weight: 700; font-size: .92rem; color: var(--charcoal); margin-bottom: 4px; line-height: 1.3; flex-shrink: 0; }
    .product-desc {
        font-size: .76rem; color: var(--muted); line-height: 1.5;
        margin-bottom: 12px; flex: 1;
        display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden;
    }
    .product-footer {
        display: flex; align-items: center; justify-content: space-between;
        margin-top: auto; gap: 8px; flex-shrink: 0;
        padding-top: 10px; border-top: 1px solid rgba(255,179,71,.12);
    }
    .product-price { font-family: 'Playfair Display', serif; font-size: 1.1rem; font-weight: 700; color: var(--amber); white-space: nowrap; }
    .product-price small { font-family: 'Poppins', sans-serif; font-size: .66rem; color: var(--muted); font-weight: 400; }
    .btn-group-card { display: flex; gap: 6px; flex-shrink: 0; }
    .view-btn, .cart-btn {
        display: inline-flex; align-items: center; gap: 5px;
        padding: 8px 12px; border-radius: 50px; font-size: .74rem; font-weight: 600;
        text-decoration: none; transition: var(--transition); border: none; cursor: pointer;
        white-space: nowrap;
    }
    .view-btn { background: var(--amber-pale); color: var(--amber); border: 1.5px solid rgba(255,179,71,.3); }
    .view-btn:hover { background: var(--amber); color: #fff; border-color: var(--amber); }
    .cart-btn { background: linear-gradient(135deg, var(--amber), var(--ember)); color: #fff; }
    .cart-btn:hover { background: linear-gradient(135deg, var(--ember-dark), #c44d25); transform: scale(1.05); color: #fff; }

    /* ── EMPTY / NO RESULTS ── */
    .empty-state { text-align: center; padding: 60px 20px; grid-column: 1/-1; }
    .empty-state i { font-size: 3rem; color: var(--amber-light); margin-bottom: 14px; display: block; }
    .empty-state p { color: var(--muted); font-size: .9rem; }
    .no-results { text-align: center; padding: 80px 20px; }
    .no-results i { font-size: 3.5rem; color: var(--amber-light); margin-bottom: 16px; display: block; }
    .no-results h3 { font-family: 'Playfair Display', serif; font-size: 1.4rem; color: var(--charcoal); margin-bottom: 8px; }
    .no-results p { color: var(--muted); }

    /* ── SCROLL TOP ── */
    #scrollTop { position: fixed; bottom: 28px; right: 28px; width: 46px; height: 46px; border-radius: 50%; background: linear-gradient(135deg, var(--amber), var(--ember)); color: #fff; border: none; font-size: 1rem; cursor: pointer; box-shadow: 0 6px 20px rgba(255,107,53,.4); opacity: 0; pointer-events: none; transition: var(--transition); z-index: 999; display: flex; align-items: center; justify-content: center; }
    #scrollTop.visible { opacity: 1; pointer-events: auto; }
    #scrollTop:hover { transform: translateY(-3px) scale(1.08); }

    /* ── FOOTER ── */
    footer { background: #1a1a1a; padding: 40px 40px 24px; color: rgba(255,255,255,.65); }
    .footer-bottom-simple { display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 12px; max-width: 1200px; margin: 0 auto; }
    .footer-brand { font-family: 'Playfair Display', serif; font-weight: 900; font-size: 1.5rem; color: var(--amber); }
    .footer-brand span { color: var(--ember); }
    .footer-bottom-simple p { font-size: .78rem; color: rgba(255,255,255,.35); }
    .footer-socials { display: flex; gap: 8px; }
    .social-link { width: 36px; height: 36px; border-radius: 50%; background: rgba(255,255,255,.07); border: 1px solid rgba(255,255,255,.1); display: flex; align-items: center; justify-content: center; color: rgba(255,255,255,.65); font-size: .85rem; text-decoration: none; transition: var(--transition); }
    .social-link:hover { background: linear-gradient(135deg, var(--amber), var(--ember)); color: #fff; border-color: transparent; }

    /* ── REVEAL ── */
    .reveal { opacity: 0; transform: translateY(20px); transition: opacity .6s ease, transform .6s ease; }
    .reveal.visible { opacity: 1; transform: translateY(0); }
    </style>
</head>
<body>

<button id="scrollTop" aria-label="Scroll to top"><i class="fas fa-arrow-up"></i></button>

<!-- NAVBAR -->
<nav class="navbar-bb" id="mainNav">
    <a class="nav-brand" href="index.php">Brew<span>&</span>Bite</a>
    <div class="hamburger" id="hamburger" aria-label="Toggle menu" role="button" tabindex="0">
        <span></span><span></span><span></span>
    </div>
    <ul class="nav-links" id="navLinks">
        <li><a href="index.php">Home</a></li>
        <li><a href="Menu.php" class="active">Menu</a></li>
        <li><a href="AboutUs.php">About Us</a></li>
        <li><a href="ContactUs.php">Contact Us</a></li>
        <?php if(isset($_SESSION['user_id'])): ?>
            <li><a href="Dashboard.php"><i class="fas fa-user-circle"></i> <?php echo htmlspecialchars($_SESSION['user_name']); ?></a></li>
            <li><a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
        <?php else: ?>
            <li><a href="Login.php"><i class="fas fa-user"></i> Login</a></li>
        <?php endif; ?>
        <li>
            <a href="Cart.php" class="cart-link">
                <i class="fas fa-shopping-bag"></i>
                Cart <span class="cart-badge"><?php echo $cart_count; ?></span>
            </a>
        </li>
    </ul>
</nav>

<!-- PAGE HEADER -->
<div class="menu-header">
    <span class="eyebrow">Crafted with care</span>
    <h1>Our <em>Full Menu</em></h1>
    <p>Coffee, desserts, fast food, Italian cuisine browse everything we make fresh daily.</p>
    <div class="search-wrap">
        <form method="GET" action="Menu.php">
            <input type="text" name="search" placeholder="Search dishes, drinks..." value="<?php echo htmlspecialchars($search); ?>">
            <button type="submit" aria-label="Search"><i class="fas fa-search"></i></button>
        </form>
    </div>
</div>

<!-- CATEGORY FILTER BAR -->
<div class="filter-bar">
    <div class="filter-inner">
        <a href="Menu.php" class="filter-btn <?php echo $active_cat === '' && $search === '' ? 'active' : ''; ?>">All</a>
        <?php foreach($categories as $cat): ?>
            <a href="Menu.php?category=<?php echo urlencode($cat); ?>"
               class="filter-btn <?php echo $active_cat === $cat ? 'active' : ''; ?>">
                <?php echo htmlspecialchars($cat); ?>
            </a>
        <?php endforeach; ?>
    </div>
</div>

<!-- MENU BODY -->
<div class="menu-body">

<?php
$found_anything = false;

if ($search !== '') {
    // Global search — simple escaped query avoids prepared statement issues
    $like = '%' . mysqli_real_escape_string($conn, $search) . '%';
    $result = mysqli_query($conn,
        "SELECT * FROM products WHERE status='available'
         AND (item_name LIKE '$like' OR category LIKE '$like')
         ORDER BY category, id DESC"
    );
    $search_results = [];
    if ($result) {
        while ($row = mysqli_fetch_assoc($result)) $search_results[] = $row;
    }

    if (empty($search_results)) {
        echo '<div class="no-results">
            <i class="fas fa-search-minus"></i>
            <h3>No results for "' . htmlspecialchars($search) . '"</h3>
            <p>Try a different keyword or browse by category.</p>
        </div>';
    } else {
        echo '<div class="cat-section reveal">';
        echo '<h2 class="cat-title">Search Results <span class="cat-count">' . count($search_results) . ' items</span></h2>';
        echo '<div class="product-grid">';
        foreach ($search_results as $row) render_product_card($row);
        echo '</div></div>';
        $found_anything = true;
    }

} else {
    // Browse by category or all
    $cats_to_show = $active_cat !== '' ? [$active_cat] : $categories;

    foreach ($cats_to_show as $catName) {
        $safe = mysqli_real_escape_string($conn, $catName);
        $products = mysqli_query($conn,
            "SELECT * FROM products WHERE category='$safe' AND status='available' ORDER BY id DESC"
        );
        $rows = [];
        if ($products) {
            while ($r = mysqli_fetch_assoc($products)) $rows[] = $r;
        }

        if (empty($rows)) continue;
        $found_anything = true;

        echo '<div class="cat-section reveal">';
        echo '<h2 class="cat-title">' . htmlspecialchars($catName) . ' <span class="cat-count">' . count($rows) . ' items</span></h2>';
        echo '<div class="product-grid">';
        foreach ($rows as $row) render_product_card($row);
        echo '</div></div>';
    }

    if (!$found_anything) {
        echo '<div class="no-results">
            <i class="fas fa-utensils"></i>
            <h3>No items available right now</h3>
            <p>Our menu is being updated. Please check back soon!</p>
        </div>';
    }
}

function render_product_card($row) {
    $name  = htmlspecialchars($row['item_name']);
    $price = number_format($row['price']);
    $id    = (int)$row['id'];
    $image = htmlspecialchars($row['image']);
    $desc  = (isset($row['description']) && $row['description'] !== '')
        ? htmlspecialchars($row['description'])
        : 'A delightful Brew&amp;Bite creation, made fresh daily.';
    echo '
    <div class="product-card">
        <div class="product-card-img">
            <img src="images/' . $image . '" alt="' . $name . '" loading="lazy"
                 onerror="this.style.display=\'none\'; this.nextElementSibling.style.display=\'flex\';">
            <div class="img-placeholder" style="display:none;"><i class="fas fa-utensils"></i></div>
        </div>
        <div class="product-body">
            <div class="product-stars">
                <i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i>
                <i class="fas fa-star"></i><i class="fas fa-star-half-alt"></i>
                <span>(4.5)</span>
            </div>
            <div class="product-name">' . $name . '</div>
            <div class="product-desc">' . $desc . '</div>
            <div class="product-footer">
                <div class="product-price">Rs ' . $price . '</div>
                <div class="btn-group-card">
                    <a href="product_details.php?id=' . $id . '" class="view-btn"><i class="fas fa-eye"></i></a>
                    <a href="add_to_cart.php?id=' . $id . '" class="cart-btn"><i class="fas fa-plus"></i> Add</a>
                </div>
            </div>
        </div>
    </div>';
}
?>

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
const scrollBtn = document.getElementById('scrollTop');
window.addEventListener('scroll', () => {
    scrollBtn.classList.toggle('visible', window.scrollY > 300);
    document.getElementById('mainNav').classList.toggle('scrolled', window.scrollY > 10);
});
scrollBtn.addEventListener('click', () => window.scrollTo({ top: 0, behavior: 'smooth' }));

const ham = document.getElementById('hamburger');
const navLinks = document.getElementById('navLinks');
ham.addEventListener('click', () => { navLinks.classList.toggle('open'); });
ham.addEventListener('keydown', e => { if(e.key === 'Enter') ham.click(); });

const observer = new IntersectionObserver(entries => {
    entries.forEach(e => { if(e.isIntersecting) { e.target.classList.add('visible'); observer.unobserve(e.target); }});
}, { threshold: 0.08 });
document.querySelectorAll('.reveal').forEach(el => observer.observe(el));
</script>
</body>
</html>