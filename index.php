<?php
session_start();
include('connection.php');

$cart_count = array_sum($_SESSION['cart'] ?? []);

/* Featured Products */
$featured = mysqli_query($conn,"
    SELECT * FROM products
    WHERE featured='yes'
    AND status='available'
    ORDER BY id DESC
    LIMIT 6
");

/* Live Reviews — tries common table names automatically */
$live_reviews = [];
$review_tables = ['reviews','review','ratings','rating','feedback','testimonials'];
foreach ($review_tables as $tbl) {
    $rq = mysqli_query($conn, "SELECT * FROM `$tbl` ORDER BY id DESC LIMIT 6");
    if ($rq && mysqli_num_rows($rq) > 0) {
        while ($rrow = mysqli_fetch_assoc($rq)) $live_reviews[] = $rrow;
        break;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Brew & Bite — Premium Café & Restaurant. Coffee, Desserts, Fast Food and Italian Cuisine in one place.">
    <title>Brew&amp;Bite — Café &amp; Restaurant</title>

    <!-- Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,700;0,900;1,700&family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

    <style>
    /* ════════════════════════════════════════
       ROOT DESIGN TOKENS
    ════════════════════════════════════════ */
    :root {
        --amber:        #FF8C00;
        --amber-light:  #FFB347;
        --amber-pale:   #FFF3E0;
        --ember:        #FF6B35;
        --ember-dark:   #E65C2E;
        --cream:        #FFF8F0;
        --charcoal:     #2D2D2D;
        --muted:        #888;
        --white:        #ffffff;
        --card-shadow:  0 8px 32px rgba(255,140,0,.12);
        --radius:       18px;
        --transition:   .35s cubic-bezier(.4,0,.2,1);
        --nav-h:        68px;
    }

    /* ════════════════════════════════════════
       RESET & BASE
    ════════════════════════════════════════ */
    *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
    html { scroll-behavior: smooth; }

    body {
        font-family: 'Poppins', sans-serif;
        background: var(--cream);
        color: var(--charcoal);
        overflow-x: hidden;
    }

    /* ════════════════════════════════════════
       SCROLL-TO-TOP
    ════════════════════════════════════════ */
    #scrollTop {
        position: fixed;
        bottom: 28px; right: 28px;
        width: 46px; height: 46px;
        border-radius: 50%;
        background: linear-gradient(135deg, var(--amber), var(--ember));
        color: #fff;
        border: none;
        font-size: 1rem;
        cursor: pointer;
        box-shadow: 0 6px 20px rgba(255,107,53,.4);
        opacity: 0; pointer-events: none;
        transition: var(--transition);
        z-index: 999;
        display: flex; align-items: center; justify-content: center;
    }
    #scrollTop.visible { opacity: 1; pointer-events: auto; }
    #scrollTop:hover { transform: translateY(-3px) scale(1.08); }

    /* ════════════════════════════════════════
       NAVBAR
    ════════════════════════════════════════ */
    .navbar-bb {
        position: fixed;
        top: 0; left: 0; right: 0;
        height: var(--nav-h);
        z-index: 500;
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 0 40px;
        background: rgba(255,255,255,.9);
        backdrop-filter: blur(18px);
        -webkit-backdrop-filter: blur(18px);
        border-bottom: 1px solid rgba(255,179,71,.22);
        transition: box-shadow .3s;
    }
    .navbar-bb.scrolled { box-shadow: 0 4px 24px rgba(255,140,0,.12); }

    .nav-brand {
        font-family: 'Playfair Display', serif;
        font-weight: 900;
        font-size: 1.6rem;
        color: var(--amber);
        text-decoration: none;
        letter-spacing: -.5px;
    }
    .nav-brand span { color: var(--ember); }

    .nav-links {
        display: flex;
        align-items: center;
        gap: 6px;
        list-style: none;
    }
    .nav-links a {
        color: var(--charcoal);
        text-decoration: none;
        font-size: .85rem;
        font-weight: 500;
        padding: 7px 14px;
        border-radius: 50px;
        transition: var(--transition);
    }
    .nav-links a:hover,
    .nav-links a.active {
        background: var(--amber-pale);
        color: var(--amber);
    }
    .nav-links .cart-link {
        display: flex; align-items: center; gap: 6px;
        background: linear-gradient(135deg, var(--amber), var(--ember));
        color: #fff !important;
        padding: 8px 18px;
        border-radius: 50px;
        font-weight: 600;
    }
    .nav-links .cart-link:hover {
        background: linear-gradient(135deg, var(--ember-dark), #c44d25);
        transform: translateY(-1px);
        box-shadow: 0 4px 14px rgba(255,107,53,.35);
    }
    .cart-badge {
        background: #fff;
        color: var(--ember);
        border-radius: 50px;
        font-size: .72rem;
        font-weight: 700;
        padding: 1px 7px;
        min-width: 22px;
        text-align: center;
    }

    /* Hamburger */
    .hamburger {
        display: none;
        flex-direction: column;
        gap: 5px;
        cursor: pointer;
        padding: 6px;
    }
    .hamburger span {
        width: 24px; height: 2.5px;
        background: var(--charcoal);
        border-radius: 2px;
        transition: var(--transition);
    }

    /* Mobile nav */
    @media (max-width: 900px) {
        .hamburger { display: flex; }
        .nav-links {
            position: fixed;
            top: var(--nav-h); left: 0; right: 0;
            background: rgba(255,255,255,.97);
            backdrop-filter: blur(18px);
            flex-direction: column;
            padding: 20px 30px 30px;
            gap: 4px;
            border-bottom: 1px solid rgba(255,179,71,.2);
            transform: translateY(-120%);
            transition: transform .4s cubic-bezier(.4,0,.2,1);
            box-shadow: 0 10px 30px rgba(0,0,0,.08);
        }
        .nav-links.open { transform: translateY(0); }
        .nav-links a { padding: 12px 16px; width: 100%; border-radius: 10px; }
    }

    /* ════════════════════════════════════════
       HERO
    ════════════════════════════════════════ */
    .hero-section {
        min-height: 100vh;
        padding-top: var(--nav-h);
        display: flex;
        align-items: center;
        position: relative;
        overflow: hidden;
        background:
            radial-gradient(ellipse 900px 600px at 0% 50%, rgba(255,179,71,.22) 0%, transparent 65%),
            radial-gradient(ellipse 600px 500px at 100% 20%, rgba(255,107,53,.13) 0%, transparent 60%),
            var(--cream);
    }
    .hero-section::before {
        content: '';
        position: absolute;
        width: 520px; height: 520px;
        border-radius: 50%;
        background: radial-gradient(circle, rgba(255,179,71,.18) 0%, transparent 70%);
        top: -100px; right: -100px;
        animation: floatBlob 8s ease-in-out infinite alternate;
        pointer-events: none;
    }
    .hero-section::after { content: none; }
    @keyframes floatBlob {
        from { transform: scale(1); }
        to   { transform: scale(1.04); }
    }

    .hero-inner {
        position: relative;
        z-index: 1;
        max-width: 1200px;
        margin: 0 auto;
        padding: 60px 40px;
        width: 100%;
    }
    .hero-h1 {
        font-family: 'Playfair Display', serif;
        font-size: clamp(2.4rem, 6vw, 4.2rem);
        font-weight: 900;
        line-height: 1.1;
        color: var(--charcoal);
        margin-bottom: 20px;
        max-width: 680px;
        animation: fadeUp .7s .08s ease both;
    }
    .hero-h1 em {
        font-style: italic;
        background: linear-gradient(90deg, var(--amber), var(--ember));
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        background-clip: text;
    }
    .hero-sub {
        font-size: 1.05rem;
        color: var(--muted);
        max-width: 480px;
        margin-bottom: 36px;
        line-height: 1.7;
        animation: fadeUp .7s .16s ease both;
    }
    .hero-actions {
        display: flex; gap: 14px; flex-wrap: wrap;
        animation: fadeUp .7s .24s ease both;
        margin-bottom: 60px;
    }
    .btn-primary-bb {
        display: inline-flex; align-items: center; gap: 9px;
        padding: 14px 30px;
        background: linear-gradient(135deg, var(--amber), var(--ember));
        color: #fff;
        font-family: 'Poppins', sans-serif;
        font-size: .9rem; font-weight: 600;
        border-radius: 50px;
        text-decoration: none;
        border: none; cursor: pointer;
        box-shadow: 0 6px 22px rgba(255,107,53,.38);
        transition: var(--transition);
    }
    .btn-primary-bb:hover {
        transform: translateY(-3px) scale(1.03);
        box-shadow: 0 10px 32px rgba(255,107,53,.48);
        color: #fff;
    }
    .btn-outline-bb {
        display: inline-flex; align-items: center; gap: 9px;
        padding: 13px 28px;
        background: transparent;
        color: var(--ember);
        font-family: 'Poppins', sans-serif;
        font-size: .9rem; font-weight: 600;
        border-radius: 50px;
        text-decoration: none;
        border: 2px solid var(--ember);
        transition: var(--transition);
        cursor: pointer;
    }
    .btn-outline-bb:hover {
        background: var(--ember);
        color: #fff;
        box-shadow: 0 6px 20px rgba(255,107,53,.3);
        transform: translateY(-2px);
    }

    /* Hero stats */
    .hero-stats {
        display: flex; gap: 40px; flex-wrap: wrap;
        animation: fadeUp .7s .32s ease both;
    }
    .hero-stat { text-align: left; }
    .hero-stat-num {
        font-family: 'Playfair Display', serif;
        font-size: 2rem; font-weight: 900;
        color: var(--amber);
        line-height: 1;
    }
    .hero-stat-label {
        font-size: .75rem; color: var(--muted);
        font-weight: 500; margin-top: 3px;
        letter-spacing: .5px;
    }
    .hero-stat-divider {
        width: 1px; background: rgba(255,179,71,.3);
        align-self: stretch;
    }

    /* ════════════════════════════════════════
       SECTION SHARED
    ════════════════════════════════════════ */
    section { padding: 90px 0; }
    .section-inner { max-width: 1200px; margin: 0 auto; padding: 0 40px; }

    .section-eyebrow {
        display: inline-block;
        font-size: .7rem; font-weight: 700;
        letter-spacing: 2.5px; text-transform: uppercase;
        color: var(--ember);
        margin-bottom: 10px;
    }
    .section-title {
        font-family: 'Playfair Display', serif;
        font-size: clamp(1.7rem, 3.5vw, 2.6rem);
        font-weight: 900;
        color: var(--charcoal);
        margin-bottom: 12px;
    }
    .section-sub {
        font-size: .9rem; color: var(--muted);
        max-width: 480px;
        line-height: 1.7;
    }
    .section-header { margin-bottom: 48px; }

    /* Reveal on scroll */
    .reveal {
        opacity: 0;
        transform: translateY(30px);
        transition: opacity .7s ease, transform .7s ease;
    }
    .reveal.visible { opacity: 1; transform: translateY(0); }
    .reveal-delay-1 { transition-delay: .1s; }
    .reveal-delay-2 { transition-delay: .2s; }
    .reveal-delay-3 { transition-delay: .3s; }
    .reveal-delay-4 { transition-delay: .4s; }

    /* ════════════════════════════════════════
       FEATURED / MENU CARDS
    ════════════════════════════════════════ */
    .featured-section { background: var(--cream); }

    .products-grid {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 24px;
    }
    .product-card {
        background: var(--white);
        border: 1px solid rgba(255,179,71,.16);
        border-radius: var(--radius);
        overflow: hidden;
        transition: var(--transition);
        box-shadow: var(--card-shadow);
        position: relative;
        display: flex; flex-direction: column;
    }
    .product-card:hover {
        transform: translateY(-7px);
        box-shadow: 0 20px 50px rgba(255,140,0,.2);
    }

    /* ── FIXED IMAGE AREA ── */
    .product-card-img {
        position: relative;
        width: 100%;
        height: 230px;
        overflow: hidden;
        /* Soft warm gradient so contain-mode images look intentional */
        background: radial-gradient(ellipse at center, #fff6e8 0%, var(--amber-pale) 100%);
        flex-shrink: 0;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    .product-card-img img {
        width: 100%;
        height: 100%;
        /* contain = full image always visible, nothing cropped */
        object-fit: contain;
        object-position: center center;
        padding: 12px;          /* small breathing room around the image */
        display: block;
        transition: transform .5s ease;
    }
    .product-card:hover .product-card-img img { transform: scale(1.06); }

    /* fallback placeholder when image is missing */
    .product-card-img .img-placeholder {
        width: 100%; height: 100%;
        display: flex; align-items: center; justify-content: center;
        font-size: 4rem;
        color: var(--amber-light);
        background: var(--amber-pale);
    }
    /* ───────────────────── */

    .product-badge {
        position: absolute;
        top: 12px; left: 12px;
        background: linear-gradient(135deg, var(--amber), var(--ember));
        color: #fff;
        font-size: .65rem; font-weight: 700;
        padding: 4px 10px; border-radius: 50px;
        text-transform: uppercase; letter-spacing: 1px;
        z-index: 2;
    }
    .product-quick {
        position: absolute;
        top: 12px; right: 12px;
        width: 34px; height: 34px;
        border-radius: 50%;
        background: rgba(255,255,255,.92);
        border: none; cursor: pointer;
        display: flex; align-items: center; justify-content: center;
        color: var(--amber);
        font-size: .85rem;
        transition: var(--transition);
        opacity: 0;
        backdrop-filter: blur(6px);
        z-index: 2;
    }
    .product-card:hover .product-quick { opacity: 1; }
    .product-quick:hover { background: var(--amber); color: #fff; }

    .product-body {
        padding: 16px 18px 18px;
        flex: 1;
        display: flex;
        flex-direction: column;
        min-height: 180px;
    }
    .product-stars {
        display: flex; align-items: center; gap: 3px;
        margin-bottom: 7px;
        flex-shrink: 0;
    }
    .product-stars i { color: var(--amber); font-size: .72rem; }
    .product-stars span { font-size: .72rem; color: var(--muted); margin-left: 4px; }
    .product-name {
        font-weight: 700;
        font-size: .95rem;
        color: var(--charcoal);
        margin-bottom: 5px;
        line-height: 1.35;
        flex-shrink: 0;
    }
    .product-desc {
        font-size: .78rem;
        color: var(--muted);
        line-height: 1.55;
        margin-bottom: 14px;
        flex: 1;
        display: -webkit-box;
        -webkit-line-clamp: 3;
        -webkit-box-orient: vertical;
        overflow: hidden;
    }
    .product-footer {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 10px;
        margin-top: auto;
        flex-shrink: 0;
        padding-top: 12px;
        border-top: 1px solid rgba(255,179,71,.12);
    }
    .product-price {
        font-family: 'Playfair Display', serif;
        font-size: 1.2rem; font-weight: 700;
        color: var(--amber);
        white-space: nowrap;
    }
    .product-price small {
        font-family: 'Poppins', sans-serif;
        font-size: .68rem; color: var(--muted); font-weight: 400;
    }
    .add-cart-btn {
        display: inline-flex; align-items: center; gap: 6px;
        padding: 9px 16px;
        background: linear-gradient(135deg, var(--amber), var(--ember));
        color: #fff;
        text-decoration: none;
        border-radius: 50px;
        font-size: .78rem; font-weight: 600;
        transition: var(--transition);
        border: none; cursor: pointer;
        white-space: nowrap;
        flex-shrink: 0;
    }
    .add-cart-btn:hover {
        background: linear-gradient(135deg, var(--ember-dark), #c44d25);
        transform: scale(1.05);
        color: #fff;
        box-shadow: 0 4px 14px rgba(255,107,53,.35);
    }

    /* Empty state */
    .empty-state {
        grid-column: 1/-1;
        text-align: center;
        padding: 60px 20px;
    }
    .empty-state i { font-size: 3.5rem; color: var(--amber-light); margin-bottom: 16px; }
    .empty-state p { color: var(--muted); font-size: .9rem; }

    /* ════════════════════════════════════════
       WHY CHOOSE US
    ════════════════════════════════════════ */
    .why-section { background: var(--white); }

    .why-grid {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 24px;
    }
    .why-card {
        background: var(--cream);
        border: 1px solid rgba(255,179,71,.18);
        border-radius: var(--radius);
        padding: 32px 26px;
        transition: var(--transition);
        position: relative;
        overflow: hidden;
    }
    .why-card::after {
        content: '';
        position: absolute;
        bottom: 0; left: 0; right: 0;
        height: 3px;
        background: linear-gradient(90deg, var(--amber), var(--ember));
        transform: scaleX(0);
        transform-origin: left;
        transition: var(--transition);
    }
    .why-card:hover {
        transform: translateY(-6px);
        box-shadow: 0 18px 44px rgba(255,140,0,.16);
        background: var(--white);
    }
    .why-card:hover::after { transform: scaleX(1); }
    .why-icon {
        width: 54px; height: 54px;
        border-radius: 14px;
        background: linear-gradient(135deg, var(--amber), var(--ember));
        display: flex; align-items: center; justify-content: center;
        font-size: 1.3rem; color: #fff;
        margin-bottom: 18px;
        box-shadow: 0 6px 18px rgba(255,107,53,.3);
        transition: var(--transition);
    }
    .why-card:hover .why-icon { transform: rotate(-5deg) scale(1.1); }
    .why-title {
        font-weight: 700; font-size: 1rem;
        color: var(--charcoal);
        margin-bottom: 8px;
    }
    .why-desc { font-size: .84rem; color: var(--muted); line-height: 1.65; }

    /* ════════════════════════════════════════
       TESTIMONIALS
    ════════════════════════════════════════ */
    .testimonials-section { background: var(--cream); }

    .testi-grid {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 22px;
    }
    .testi-card {
        background: var(--white);
        border: 1px solid rgba(255,179,71,.16);
        border-radius: var(--radius);
        padding: 28px 24px;
        box-shadow: var(--card-shadow);
        transition: var(--transition);
        position: relative;
    }
    .testi-card:hover { transform: translateY(-5px); box-shadow: 0 16px 40px rgba(255,140,0,.18); }
    .testi-quote {
        font-size: 3rem; line-height: 1;
        color: var(--amber-light);
        font-family: 'Playfair Display', serif;
        margin-bottom: 10px;
    }
    .testi-text {
        font-size: .88rem; color: var(--charcoal);
        line-height: 1.7; font-style: italic;
        margin-bottom: 20px;
    }
    .testi-stars { display: flex; gap: 3px; margin-bottom: 16px; }
    .testi-stars i { color: var(--amber); font-size: .8rem; }
    .testi-author { display: flex; align-items: center; gap: 12px; }
    .testi-avatar {
        width: 44px; height: 44px;
        border-radius: 50%;
        background: linear-gradient(135deg, var(--amber), var(--ember));
        display: flex; align-items: center; justify-content: center;
        color: #fff; font-weight: 700; font-size: .95rem;
        flex-shrink: 0;
    }
    .testi-name { font-weight: 600; font-size: .88rem; color: var(--charcoal); }
    .testi-role { font-size: .75rem; color: var(--muted); }

    /* ════════════════════════════════════════
       FOOTER
    ════════════════════════════════════════ */
    footer {
        background: #1a1a1a;
        padding: 70px 0 0;
        color: rgba(255,255,255,.65);
    }
    .footer-grid {
        display: grid;
        grid-template-columns: 2fr 1fr 1fr 1.5fr;
        gap: 50px;
        max-width: 1200px;
        margin: 0 auto;
        padding: 0 40px;
    }
    .footer-brand {
        font-family: 'Playfair Display', serif;
        font-weight: 900; font-size: 1.7rem;
        color: var(--amber);
        margin-bottom: 14px;
        display: block;
    }
    .footer-brand span { color: var(--ember); }
    .footer-desc {
        font-size: .84rem; line-height: 1.7;
        margin-bottom: 22px;
        color: rgba(255,255,255,.5);
    }
    .footer-socials { display: flex; gap: 10px; }
    .social-link {
        width: 38px; height: 38px;
        border-radius: 50%;
        background: rgba(255,255,255,.07);
        border: 1px solid rgba(255,255,255,.1);
        display: flex; align-items: center; justify-content: center;
        color: rgba(255,255,255,.65);
        font-size: .9rem;
        text-decoration: none;
        transition: var(--transition);
    }
    .social-link:hover {
        background: linear-gradient(135deg, var(--amber), var(--ember));
        color: #fff;
        border-color: transparent;
        transform: translateY(-2px);
    }
    .footer-col h4 {
        font-size: .82rem; font-weight: 700;
        color: #fff; letter-spacing: 1.5px;
        text-transform: uppercase; margin-bottom: 18px;
    }
    .footer-links { list-style: none; display: flex; flex-direction: column; gap: 10px; }
    .footer-links a {
        color: rgba(255,255,255,.5);
        text-decoration: none;
        font-size: .84rem;
        transition: var(--transition);
        display: inline-flex; align-items: center; gap: 6px;
    }
    .footer-links a::before {
        content: '›';
        color: var(--amber);
        font-size: 1rem;
    }
    .footer-links a:hover { color: var(--amber); padding-left: 4px; }
    .footer-contact { display: flex; flex-direction: column; gap: 12px; }
    .contact-item {
        display: flex; gap: 12px; align-items: flex-start;
    }
    .contact-item i {
        color: var(--amber);
        margin-top: 2px; flex-shrink: 0;
        font-size: .9rem;
    }
    .contact-item span { font-size: .84rem; color: rgba(255,255,255,.55); line-height: 1.55; }
    .footer-bottom {
        margin-top: 50px;
        border-top: 1px solid rgba(255,255,255,.07);
        padding: 20px 40px;
        max-width: 1200px;
        margin-left: auto; margin-right: auto;
        display: flex; align-items: center; justify-content: space-between;
        flex-wrap: wrap; gap: 10px;
    }
    .footer-bottom p { font-size: .78rem; color: rgba(255,255,255,.35); }
    .footer-bottom-links { display: flex; gap: 20px; }
    .footer-bottom-links a {
        font-size: .78rem; color: rgba(255,255,255,.35);
        text-decoration: none; transition: var(--transition);
    }
    .footer-bottom-links a:hover { color: var(--amber); }

    /* ════════════════════════════════════════
       ANIMATIONS
    ════════════════════════════════════════ */
    @keyframes fadeUp {
        from { opacity: 0; transform: translateY(24px); }
        to   { opacity: 1; transform: translateY(0); }
    }

    /* ════════════════════════════════════════
       RESPONSIVE
    ════════════════════════════════════════ */
    @media (max-width: 1024px) {
        .products-grid { grid-template-columns: repeat(2, 1fr); }
        .why-grid { grid-template-columns: repeat(2, 1fr); }
        .testi-grid { grid-template-columns: repeat(2, 1fr); }
        .footer-grid { grid-template-columns: 1fr 1fr; gap: 36px; }
    }
    @media (max-width: 768px) {
        .section-inner { padding: 0 20px; }
        section { padding: 60px 0; }
        .hero-inner { padding: 40px 20px; }
        .hero-stats { gap: 22px; }
        .hero-stat-divider { display: none; }
        .products-grid { grid-template-columns: 1fr; }
        .why-grid { grid-template-columns: 1fr; }
        .testi-grid { grid-template-columns: 1fr; }
        .footer-grid { grid-template-columns: 1fr; gap: 28px; padding: 0 20px; }
        .navbar-bb { padding: 0 20px; }
    }
    @media (max-width: 480px) {
        .hero-actions { flex-direction: column; }
        .btn-primary-bb, .btn-outline-bb { width: 100%; justify-content: center; }
        .footer-bottom { flex-direction: column; text-align: center; }
        .product-card-img { height: 200px; }
    }
    </style>
</head>
<body>

<!-- ══════════════════════════════════════
     SCROLL TO TOP
══════════════════════════════════════ -->
<button id="scrollTop" aria-label="Scroll to top"><i class="fas fa-arrow-up"></i></button>

<!-- ══════════════════════════════════════
     NAVBAR
══════════════════════════════════════ -->
<nav class="navbar-bb" id="mainNav">
    <a class="nav-brand" href="index.php">Brew<span>&</span>Bite</a>

    <div class="hamburger" id="hamburger" aria-label="Toggle menu" role="button" tabindex="0">
        <span></span><span></span><span></span>
    </div>

    <ul class="nav-links" id="navLinks">
        <li><a href="index.php" class="active">Home</a></li>
        <li><a href="Menu.php">Menu</a></li>
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

<!-- ══════════════════════════════════════
     HERO
══════════════════════════════════════ -->
<header class="hero-section">
    <div class="hero-inner">
        <h1 class="hero-h1">Start Your Day with<br><em>Fresh Brew</em> &amp; Tasty Bites</h1>
        <p class="hero-sub">Coffee • Desserts • Fast Food • Italian Cuisine all crafted with love and served fresh, every single day.</p>
        <div class="hero-actions">
            <a href="Cart.php" class="btn-primary-bb"><i class="fas fa-shopping-bag"></i> Order Now</a>
            <a href="Menu.php" class="btn-outline-bb"><i class="fas fa-book-open"></i> View Menu</a>
        </div>
        <div class="hero-stats">
            <div class="hero-stat">
                <div class="hero-stat-num">2K+</div>
                <div class="hero-stat-label">Happy Customers</div>
            </div>
            <div class="hero-stat-divider"></div>
            <div class="hero-stat">
                <div class="hero-stat-num">80+</div>
                <div class="hero-stat-label">Menu Items</div>
            </div>
            <div class="hero-stat-divider"></div>
            <div class="hero-stat">
                <div class="hero-stat-num">5+</div>
                <div class="hero-stat-label">Years of Service</div>
            </div>
            <div class="hero-stat-divider"></div>
            <div class="hero-stat">
                <div class="hero-stat-num">15K+</div>
                <div class="hero-stat-label">Orders Delivered</div>
            </div>
        </div>
    </div>
</header>

<!-- ══════════════════════════════════════
     FEATURED MENU
══════════════════════════════════════ -->
<section class="featured-section">
    <div class="section-inner">
        <div class="section-header reveal" style="display:flex; align-items:flex-end; justify-content:space-between; flex-wrap:wrap; gap:16px;">
            <div>
                <span class="section-eyebrow">Staff Picks</span>
                <h2 class="section-title">Brewed Favorites</h2>
                <p class="section-sub">Our most loved items, handpicked by our team.</p>
            </div>
            <a href="Menu.php" class="btn-outline-bb" style="margin-bottom:14px;">
                <i class="fas fa-arrow-right"></i> See Full Menu
            </a>
        </div>

        <div class="products-grid">
            <?php if($featured && mysqli_num_rows($featured) > 0):
                $delay = 0;
                while($row = mysqli_fetch_assoc($featured)):
                    $delay_class = $delay > 0 ? 'reveal-delay-' . min($delay, 4) : '';
                    $delay++;
            ?>
            <div class="product-card reveal <?php echo $delay_class; ?>">
                <div class="product-card-img">
                    <img
                        src="images/<?php echo htmlspecialchars($row['image']); ?>"
                        alt="<?php echo htmlspecialchars($row['item_name']); ?>"
                        loading="lazy"
                        onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';"
                    >
                    <div class="img-placeholder" style="display:none;">
                        <i class="fas fa-utensils"></i>
                    </div>
                    <button class="product-quick" title="Quick View"><i class="fas fa-eye"></i></button>
                </div>
                <div class="product-body">
                    <div class="product-stars">
                        <i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i>
                        <i class="fas fa-star"></i><i class="fas fa-star-half-alt"></i>
                        <span>(4.5)</span>
                    </div>
                    <div class="product-name"><?php echo htmlspecialchars($row['item_name']); ?></div>
                    <div class="product-desc">
                        <?php echo isset($row['description']) ? htmlspecialchars(substr($row['description'],0,80)).'...' : 'A delightful Brew&Bite creation, made fresh daily.'; ?>
                    </div>
                    <div class="product-footer">
                        <div class="product-price">
                            Rs <?php echo number_format($row['price']); ?>
                            <!-- <small></small> -->
                        </div>
                        <a href="add_to_cart.php?id=<?php echo (int)$row['id']; ?>" class="add-cart-btn">
                            <i class="fas fa-plus"></i> Add
                        </a>
                    </div>
                </div>
            </div>
            <?php endwhile; else: ?>
            <div class="empty-state">
                <i class="fas fa-coffee"></i>
                <p>No featured items right now. Check back soon!</p>
                <a href="Menu.php" class="btn-primary-bb" style="margin-top:16px; display:inline-flex;">
                    <i class="fas fa-utensils"></i> Browse Full Menu
                </a>
            </div>
            <?php endif; ?>
        </div>
    </div>
</section>

<!-- ══════════════════════════════════════
     WHY CHOOSE US
══════════════════════════════════════ -->
<section class="why-section">
    <div class="section-inner">
        <div class="section-header reveal" style="text-align:center; max-width:500px; margin:0 auto 48px;">
            <span class="section-eyebrow">Our Promise</span>
            <h2 class="section-title">Why Choose Brew&amp;Bite?</h2>
            <p class="section-sub">We don't just serve food  we craft experiences worth coming back for.</p>
        </div>
        <div class="why-grid">
            <?php
            $why = [
                ['fas fa-leaf',       'Fresh Ingredients',    'Every dish uses locally sourced, seasonal produce picked fresh daily.'],
                ['fas fa-bolt',       'Fast Delivery',        'Hot food at your door in under 30 minutes, guaranteed.'],
                ['fas fa-gem',        'Premium Quality',      'Restaurant-grade ingredients and preparation in every single order.'],
                ['fas fa-hat-chef',   'Experienced Chefs',    'Our kitchen team brings 10+ years of culinary excellence.'],
                ['fas fa-shield-alt', 'Hygienic Environment', 'Strict food safety standards and spotless kitchen conditions always.'],
                ['fas fa-heart',      'Excellent Service',    'Our team goes above and beyond to make every visit memorable.'],
            ];
            foreach($why as $i => $w):
                $d = $i % 4 == 0 ? '' : 'reveal-delay-'.min(($i%4),4);
            ?>
            <div class="why-card reveal <?php echo $d; ?>">
                <div class="why-icon"><i class="<?php echo $w[0]; ?>"></i></div>
                <div class="why-title"><?php echo $w[1]; ?></div>
                <div class="why-desc"><?php echo $w[2]; ?></div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- ══════════════════════════════════════
     TESTIMONIALS
══════════════════════════════════════ -->
<section class="testimonials-section">
    <div class="section-inner">
        <div class="section-header reveal" style="text-align:center; max-width:480px; margin:0 auto 48px;">
            <span class="section-eyebrow">Customer Love</span>
            <h2 class="section-title">What People Are Saying</h2>
            <p class="section-sub">Real words from our regulars who keep coming back.</p>
        </div>
        <div class="testi-grid">
            <?php
            // Use pre-fetched live reviews from top of page
            $review_rows = $live_reviews;

            if (!empty($review_rows)):
                foreach ($review_rows as $i => $r):
                    $d = $i == 0 ? '' : 'reveal-delay-' . min($i, 4);
                    // detect column names flexibly
                    $name    = $r['fullname'] ?? $r['name'] ?? $r['username'] ?? $r['user_name'] ?? 'Customer';
                    $comment = $r['comment']  ?? $r['review'] ?? $r['message'] ?? $r['feedback'] ?? $r['text'] ?? '';
                    $stars   = (int)($r['rating'] ?? $r['stars'] ?? 5);
                    $role    = $r['role'] ?? $r['designation'] ?? 'Verified Customer';
                    $init    = strtoupper(mb_substr(trim($name), 0, 1));
                    if (empty($comment)) continue;
            ?>
            <div class="testi-card reveal <?php echo $d; ?>">
                <div class="testi-quote">"</div>
                <p class="testi-text"><?php echo htmlspecialchars($comment); ?></p>
                <div class="testi-stars">
                    <?php for($s=1;$s<=5;$s++) echo '<i class="'.($s<=$stars ? 'fas' : 'far').' fa-star"></i>'; ?>
                </div>
                <div class="testi-author">
                    <div class="testi-avatar"><?php echo htmlspecialchars($init); ?></div>
                    <div>
                        <div class="testi-name"><?php echo htmlspecialchars($name); ?></div>
                        <div class="testi-role"><?php echo htmlspecialchars($role); ?></div>
                    </div>
                </div>
            </div>
            <?php endforeach; else: ?>
            <!-- No reviews yet — show a friendly prompt -->
            <div style="grid-column:1/-1; text-align:center; padding:40px 20px;">
                <i class="fas fa-comment-dots" style="font-size:2.5rem; color:var(--amber-light); margin-bottom:14px; display:block;"></i>
                <p style="color:var(--muted); font-size:.9rem;">No reviews yet. Be the first to share your experience!</p>
            </div>
            <?php endif; ?>
        </div>
    </div>
</section>

<!-- ══════════════════════════════════════
     FOOTER
══════════════════════════════════════ -->
<footer>
    <div class="footer-grid">
        <div>
            <span class="footer-brand">Brew<span>&</span>Bite</span>
            <p class="footer-desc">A premium café and restaurant crafting memorable dining experiences since 2019. Coffee, cuisine and community all under one roof.</p>
            <div class="footer-socials">
                <a href="#" class="social-link"><i class="fab fa-instagram"></i></a>
                <a href="#" class="social-link"><i class="fab fa-facebook"></i></a>
                <a href="#" class="social-link"><i class="fab fa-twitter"></i></a>
                <a href="#" class="social-link"><i class="fab fa-tiktok"></i></a>
            </div>
        </div>
        <div class="footer-col">
            <h4>Quick Links</h4>
            <ul class="footer-links">
                <li><a href="index.php">Home</a></li>
                <li><a href="Menu.php">Menu</a></li>
                <li><a href="AboutUs.php">About Us</a></li>
                <li><a href="ContactUs.php">Contact</a></li>
                <li><a href="Dashboard.php">My Account</a></li>
                <li><a href="Cart.php">My Cart</a></li>
            </ul>
        </div>
        <div class="footer-col">
            <h4>Categories</h4>
            <ul class="footer-links">
                <li><a href="Menu.php?category=coffee">Coffee</a></li>
                <li><a href="Menu.php?category=sweets">Sweets &amp; Desserts</a></li>
                <li><a href="Menu.php?category=italian">Italian Corner</a></li>
                <li><a href="Menu.php?category=sandwiches">Sandwiches &amp; Wraps</a></li>
                <li><a href="Menu.php?category=drinks">Cold Drinks &amp; Refreshers</a></li>
            </ul>
        </div>
        <div class="footer-col">
            <h4>Contact Us</h4>
            <div class="footer-contact">
                <div class="contact-item">
                    <i class="fas fa-map-marker-alt"></i>
                    <span>123 Café Street, F-7 Markaz, Islamabad, Pakistan</span>
                </div>
                <div class="contact-item">
                    <i class="fas fa-phone"></i>
                    <span>+92 300 1234567</span>
                </div>
                <div class="contact-item">
                    <i class="fas fa-envelope"></i>
                    <span>hello@brewnbite.pk</span>
                </div>
                <div class="contact-item">
                    <i class="fas fa-clock"></i>
                    <span>Mon–Fri: 8AM–11PM<br>Sat–Sun: 9AM–12AM</span>
                </div>
            </div>
        </div>
    </div>
    <div class="footer-bottom">
        <p>&copy; 2026 Brew&amp;Bite. All rights reserved.</p>
        <div class="footer-bottom-links">
            <a href="#">Privacy Policy</a>
            <a href="#">Terms of Service</a>
            <a href="#">Cookies</a>
        </div>
    </div>
</footer>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<script>
/* ── Scroll-to-top ── */
const scrollBtn = document.getElementById('scrollTop');
window.addEventListener('scroll', () => {
    scrollBtn.classList.toggle('visible', window.scrollY > 400);
    document.getElementById('mainNav').classList.toggle('scrolled', window.scrollY > 10);
});
scrollBtn.addEventListener('click', () => window.scrollTo({ top: 0, behavior: 'smooth' }));

/* ── Hamburger ── */
const ham = document.getElementById('hamburger');
const navLinks = document.getElementById('navLinks');
ham.addEventListener('click', () => {
    navLinks.classList.toggle('open');
    ham.classList.toggle('open');
});
ham.addEventListener('keydown', e => { if(e.key==='Enter') ham.click(); });

/* ── Scroll reveal ── */
const observer = new IntersectionObserver((entries) => {
    entries.forEach(e => {
        if(e.isIntersecting) {
            e.target.classList.add('visible');
            observer.unobserve(e.target);
        }
    });
}, { threshold: 0.12 });
document.querySelectorAll('.reveal').forEach(el => observer.observe(el));

/* ── Animated counters (hero stats) ── */
function animateCounter(el, target, suffix='') {
    let start = 0;
    const dur = 1800;
    const step = target / (dur / 16);
    const timer = setInterval(() => {
        start = Math.min(start + step, target);
        el.textContent = (start >= 1000 ? (start/1000).toFixed(1)+'K' : Math.floor(start)) + suffix;
        if(start >= target) clearInterval(timer);
    }, 16);
}
const heroObserver = new IntersectionObserver((entries) => {
    entries.forEach(e => {
        if(e.isIntersecting) {
            const nums = [
                { el: document.querySelectorAll('.hero-stat-num')[0], val: 2000,  suffix:'+' },
                { el: document.querySelectorAll('.hero-stat-num')[1], val: 80,    suffix:'+' },
                { el: document.querySelectorAll('.hero-stat-num')[2], val: 5,     suffix:'+' },
                { el: document.querySelectorAll('.hero-stat-num')[3], val: 15000, suffix:'+' },
            ];
            nums.forEach(n => animateCounter(n.el, n.val, n.suffix));
            heroObserver.disconnect();
        }
    });
}, { threshold: 0.4 });
const heroStats = document.querySelector('.hero-stats');
if(heroStats) heroObserver.observe(heroStats);
</script>

</body>
</html>