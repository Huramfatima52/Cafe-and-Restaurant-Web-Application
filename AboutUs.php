<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>About Us — Brew&amp;Bite</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,700;0,900;1,700&family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
    :root{--amber:#FF8C00;--amber-light:#FFB347;--amber-pale:#FFF3E0;--ember:#FF6B35;--ember-dark:#E65C2E;--cream:#FFF8F0;--charcoal:#2D2D2D;--muted:#888;--white:#fff;--radius:18px;--transition:.35s cubic-bezier(.4,0,.2,1);--nav-h:68px;}
    *,*::before,*::after{box-sizing:border-box;margin:0;padding:0}
    html{scroll-behavior:smooth}
    body{font-family:'Poppins',sans-serif;background:var(--cream);color:var(--charcoal);overflow-x:hidden}

    /* NAVBAR */
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
    @media(max-width:900px){.hamburger{display:flex}.nav-links{position:fixed;top:var(--nav-h);left:0;right:0;background:rgba(255,255,255,.97);backdrop-filter:blur(18px);flex-direction:column;padding:20px 30px 30px;gap:4px;border-bottom:1px solid rgba(255,179,71,.2);transform:translateY(-120%);transition:transform .4s cubic-bezier(.4,0,.2,1);box-shadow:0 10px 30px rgba(0,0,0,.08)}.nav-links.open{transform:translateY(0)}.nav-links a{padding:12px 16px;width:100%;border-radius:10px}.navbar-bb{padding:0 20px}}

    /* HERO BANNER — coffee emoji removed */
    .about-hero{
        padding-top:var(--nav-h);
        min-height:50vh;
        display:flex;align-items:center;
        background:linear-gradient(135deg,rgba(255,179,71,.18) 0%,rgba(255,107,53,.1) 100%),var(--cream);
        position:relative;overflow:hidden;
    }
    /* ☕ emoji pseudo-element removed */
    .about-hero-inner{max-width:1200px;margin:0 auto;padding:60px 40px;position:relative;z-index:1}
    .eyebrow{display:inline-block;font-size:.7rem;font-weight:700;letter-spacing:2.5px;text-transform:uppercase;color:var(--ember);margin-bottom:10px}
    .about-hero h1{font-family:'Playfair Display',serif;font-size:clamp(2.2rem,5vw,3.4rem);font-weight:900;color:var(--charcoal);margin-bottom:14px;line-height:1.1}
    .about-hero h1 em{font-style:italic;background:linear-gradient(90deg,var(--amber),var(--ember));-webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text}
    .about-hero p{color:var(--muted);font-size:.98rem;max-width:500px;line-height:1.7;margin-bottom:28px}
    .btn-primary-bb{display:inline-flex;align-items:center;gap:9px;padding:13px 28px;background:linear-gradient(135deg,var(--amber),var(--ember));color:#fff;font-family:'Poppins',sans-serif;font-size:.9rem;font-weight:600;border-radius:50px;text-decoration:none;transition:var(--transition)}
    .btn-primary-bb:hover{transform:translateY(-2px);box-shadow:0 8px 24px rgba(255,107,53,.35);color:#fff}

    /* STATS */
    .stats-bar{background:var(--white);border-bottom:1px solid rgba(255,179,71,.15);padding:28px 40px}
    .stats-inner{max-width:1200px;margin:0 auto;display:grid;grid-template-columns:repeat(4,1fr);gap:20px}
    .stat-item{text-align:center}
    .stat-num{font-family:'Playfair Display',serif;font-size:2rem;font-weight:900;color:var(--amber)}
    .stat-label{font-size:.78rem;color:var(--muted);font-weight:500;margin-top:2px}
    @media(max-width:600px){.stats-inner{grid-template-columns:repeat(2,1fr)}}

    /* SECTIONS */
    section{padding:80px 0}
    .section-inner{max-width:1200px;margin:0 auto;padding:0 40px}
    @media(max-width:768px){.section-inner{padding:0 20px}section{padding:50px 0}}
    .section-title{font-family:'Playfair Display',serif;font-size:clamp(1.6rem,3vw,2.2rem);font-weight:900;color:var(--charcoal);margin-bottom:12px}
    .section-sub{font-size:.9rem;color:var(--muted);line-height:1.7;max-width:500px}

    /* STORY */
    .story-grid{display:grid;grid-template-columns:1fr 1fr;gap:70px;align-items:center}
    @media(max-width:768px){.story-grid{grid-template-columns:1fr;gap:30px}}
    .story-img-wrap{position:relative}

    /* STORY IMAGE */
    .story-img-box{
        width:100%;
        aspect-ratio:4/3;
        border-radius:24px;
        overflow:hidden;
        box-shadow:0 24px 50px rgba(255,140,0,.2);
    }
    .story-img-box img{
        width:100%;height:100%;
        object-fit:cover;
        display:block;
        border-radius:24px;
    }

    .story-year-badge{position:absolute;bottom:-16px;right:-16px;background:var(--charcoal);color:#fff;border-radius:16px;padding:16px 22px;text-align:center;box-shadow:0 8px 24px rgba(0,0,0,.2)}
    .story-year-badge .yr{font-family:'Playfair Display',serif;font-size:1.8rem;font-weight:900;color:var(--amber);line-height:1}
    .story-year-badge .yl{font-size:.72rem;color:rgba(255,255,255,.6);margin-top:2px}
    .story-text .eyebrow{margin-bottom:10px}
    .story-text p{font-size:.9rem;color:var(--muted);line-height:1.75;margin-bottom:14px}
    .story-list{list-style:none;display:flex;flex-direction:column;gap:10px;margin:20px 0}
    .story-list li{display:flex;align-items:center;gap:10px;font-size:.88rem;color:var(--charcoal);font-weight:500}
    .story-list li i{color:var(--amber);font-size:.9rem;flex-shrink:0}

    /* VALUES */
    .values-section{background:var(--white)}
    .values-grid{display:grid;grid-template-columns:repeat(3,1fr);gap:22px}
    @media(max-width:900px){.values-grid{grid-template-columns:repeat(2,1fr)}}
    @media(max-width:500px){.values-grid{grid-template-columns:1fr}}
    .value-card{background:var(--cream);border:1px solid rgba(255,179,71,.18);border-radius:var(--radius);padding:28px 22px;transition:var(--transition);position:relative;overflow:hidden}
    .value-card::after{content:'';position:absolute;bottom:0;left:0;right:0;height:3px;background:linear-gradient(90deg,var(--amber),var(--ember));transform:scaleX(0);transform-origin:left;transition:var(--transition)}
    .value-card:hover{transform:translateY(-5px);box-shadow:0 16px 40px rgba(255,140,0,.15);background:var(--white)}
    .value-card:hover::after{transform:scaleX(1)}
    .value-icon{width:52px;height:52px;border-radius:14px;background:linear-gradient(135deg,var(--amber),var(--ember));display:flex;align-items:center;justify-content:center;font-size:1.2rem;color:#fff;margin-bottom:16px;box-shadow:0 6px 16px rgba(255,107,53,.28);transition:var(--transition)}
    .value-card:hover .value-icon{transform:rotate(-5deg) scale(1.1)}
    .value-title{font-weight:700;font-size:.95rem;color:var(--charcoal);margin-bottom:6px}
    .value-desc{font-size:.82rem;color:var(--muted);line-height:1.65}

    /* TEAM */
    .team-grid{display:grid;grid-template-columns:repeat(3,1fr);gap:24px}
    @media(max-width:768px){.team-grid{grid-template-columns:1fr}}
    .team-card{background:var(--white);border:1px solid rgba(255,179,71,.16);border-radius:var(--radius);padding:32px 24px;text-align:center;transition:var(--transition);box-shadow:0 4px 18px rgba(255,140,0,.08)}
    .team-card:hover{transform:translateY(-6px);box-shadow:0 18px 44px rgba(255,140,0,.18)}
    .team-avatar{width:90px;height:90px;border-radius:50%;background:linear-gradient(135deg,var(--amber),var(--ember));display:flex;align-items:center;justify-content:center;font-family:'Playfair Display',serif;font-size:1.8rem;font-weight:700;color:#fff;margin:0 auto 16px;box-shadow:0 6px 20px rgba(255,107,53,.3)}
    .team-name{font-weight:700;font-size:1.05rem;color:var(--charcoal);margin-bottom:4px}
    .team-role{font-size:.78rem;color:var(--ember);font-weight:600;text-transform:uppercase;letter-spacing:1px;margin-bottom:12px}
    .team-bio{font-size:.82rem;color:var(--muted);line-height:1.65}

    /* REVEAL */
    .reveal{opacity:0;transform:translateY(24px);transition:opacity .7s ease,transform .7s ease}
    .reveal.visible{opacity:1;transform:translateY(0)}
    .reveal-delay-1{transition-delay:.1s}.reveal-delay-2{transition-delay:.2s}.reveal-delay-3{transition-delay:.3s}

    /* FOOTER */
    footer{background:#1a1a1a;padding:40px;color:rgba(255,255,255,.65)}
    .footer-bottom-simple{display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:12px;max-width:1200px;margin:0 auto}
    .footer-brand{font-family:'Playfair Display',serif;font-weight:900;font-size:1.5rem;color:var(--amber)}
    .footer-brand span{color:var(--ember)}
    .footer-bottom-simple p{font-size:.78rem;color:rgba(255,255,255,.35)}
    .footer-socials{display:flex;gap:8px}
    .social-link{width:36px;height:36px;border-radius:50%;background:rgba(255,255,255,.07);border:1px solid rgba(255,255,255,.1);display:flex;align-items:center;justify-content:center;color:rgba(255,255,255,.65);font-size:.85rem;text-decoration:none;transition:var(--transition)}
    .social-link:hover{background:linear-gradient(135deg,var(--amber),var(--ember));color:#fff;border-color:transparent}
    </style>
</head>
<body>

<!-- NAVBAR -->
<nav class="navbar-bb" id="mainNav">
    <a class="nav-brand" href="index.php">Brew<span>&</span>Bite</a>
    <div class="hamburger" id="hamburger" aria-label="Toggle menu" role="button" tabindex="0"><span></span><span></span><span></span></div>
    <ul class="nav-links" id="navLinks">
        <li><a href="index.php">Home</a></li>
        <li><a href="Menu.php">Menu</a></li>
        <li><a href="AboutUs.php" class="active">About Us</a></li>
        <li><a href="ContactUs.php">Contact Us</a></li>
        <li><a href="Login.php"><i class="fas fa-user"></i> Login</a></li>
        <li><a href="Cart.php" class="cart-link"><i class="fas fa-shopping-bag"></i> Cart</a></li>
    </ul>
</nav>

<!-- HERO — no coffee emoji -->
<div class="about-hero">
    <div class="about-hero-inner">
        <h1>We're More Than<br><em>Coffee &amp; Food</em></h1>
        <p>Brew &amp; Bite was born from a simple belief that great food lovingly made has the power to bring people together. We've been doing that every single day since 2019.</p>
        <a href="Menu.php" class="btn-primary-bb"><i class=""></i> Explore Our Menu</a>
    </div>
</div>

<!-- STATS BAR -->
<div class="stats-bar">
    <div class="stats-inner">
        <div class="stat-item reveal"><div class="stat-num">2K+</div><div class="stat-label">Happy Customers</div></div>
        <div class="stat-item reveal reveal-delay-1"><div class="stat-num">40+</div><div class="stat-label">Menu Items</div></div>
        <div class="stat-item reveal reveal-delay-2"><div class="stat-num">5+</div><div class="stat-label">Years of Service</div></div>
        <div class="stat-item reveal reveal-delay-3"><div class="stat-num">4.8★</div><div class="stat-label">Average Rating</div></div>
    </div>
</div>

<!-- OUR STORY -->
<section style="background:var(--cream)">
    <div class="section-inner">
        <div class="story-grid">
            <div class="reveal">
                <div class="story-img-wrap">
                    <div class="story-img-box">
                
                        <img src="images/aboutbgi.jpg" alt="Our Café Story">
                    </div>
                </div>
            </div>
            <div class="story-text reveal reveal-delay-1">
                <span class="eyebrow">Our Story</span>
                <h2 class="section-title">From a Small Dream to a Favourite Spot</h2>
                <p>Brew &amp; Bite started as a tiny corner cafe with a big dream to serve the best coffee and freshest food in Islamabad. What began as a passion project between two friends quickly grew into a beloved community space.</p>
                <p>Today we serve hundreds of customers daily, offering everything from hand crafted espresso drinks and Italian pasta to loaded burgers and decadent desserts all under one cozy roof.</p>
                <ul class="story-list">
                    <li><i class="fas fa-check-circle"></i> Freshly sourced, seasonal ingredients</li>
                    <li><i class="fas fa-check-circle"></i> Recipes perfected over years of service</li>
                    <li><i class="fas fa-check-circle"></i> A team that genuinely loves food</li>
                    <li><i class="fas fa-check-circle"></i> A space designed for comfort and community</li>
                </ul>
            </div>
        </div>
    </div>
</section>

<!-- OUR VALUES -->
<section class="values-section">
    <div class="section-inner">
        <div style="text-align:center;max-width:500px;margin:0 auto 48px" class="reveal">
            <span class="eyebrow">What We Stand For</span>
            <h2 class="section-title">Our Core Values</h2>
            <p class="section-sub">Everything we do is guided by these principles.</p>
        </div>
        <div class="values-grid">
            <?php
            $values = [
                ['fas fa-leaf',       'Fresh Always',       'We never compromise on freshness. Every ingredient is sourced daily and every dish is made to order.'],
                ['fas fa-heart',      'Made with Love',     'Our kitchen team pours genuine care into every single dish you can taste the difference.'],
                ['fas fa-users',      'Community First',    'We exist because of our community. We support local suppliers and give back whenever we can.'],
                ['fas fa-shield-alt', 'Safe & Hygienic',    'Our kitchen maintains the highest food safety standards. Spotless, certified and always inspected.'],
                ['fas fa-bolt',       'Fast & Reliable',    'Whether dining in or ordering delivery, we respect your time. Hot food, quickly, every time.'],
                ['fas fa-star',       'Always Improving',   'We constantly refine our recipes and service. Feedback is our fuel we never stop getting better.'],
            ];
            foreach($values as $i=>$v):
                $d = $i % 3 === 0 ? '' : 'reveal-delay-'.min($i%3,3);
            ?>
            <div class="value-card reveal <?= $d ?>">
                <div class="value-icon"><i class="<?= $v[0] ?>"></i></div>
                <div class="value-title"><?= $v[1] ?></div>
                <div class="value-desc"><?= $v[2] ?></div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- OUR TEAM -->
<section style="background:var(--cream)">
    <div class="section-inner">
        <div style="text-align:center;max-width:480px;margin:0 auto 48px" class="reveal">
            <span class="eyebrow">The People Behind the Magic</span>
            <h2 class="section-title">Meet Our Team</h2>
            <p class="section-sub">Passionate, skilled and dedicated to your every visit.</p>
        </div>
        <div class="team-grid">
            <div class="team-card reveal">
                <div class="team-avatar">AK</div>
                <div class="team-name">Ahmed Khan</div>
                <div class="team-role">Head Chef</div>
                <div class="team-bio">12 years of culinary experience across Lahore, Karachi and Dubai. Specialises in Italian cuisine and signature sauces.</div>
            </div>
            <div class="team-card reveal reveal-delay-1">
                <div class="team-avatar">SM</div>
                <div class="team-name">Sara Mirza</div>
                <div class="team-role">Head Barista</div>
                <div class="team-bio">Certified barista with a passion for specialty coffee. Has won regional latte-art competitions two years running.</div>
            </div>
            <div class="team-card reveal reveal-delay-2">
                <div class="team-avatar">ZA</div>
                <div class="team-name">Zaid Akhtar</div>
                <div class="team-role">Pastry Chef</div>
                <div class="team-bio">Trained in Paris, Zaid brings artisan dessert techniques to every cake, cookie and pastry we serve.</div>
            </div>
        </div>
    </div>
</section>

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
// Navbar scroll effect
window.addEventListener('scroll',()=>document.getElementById('mainNav').classList.toggle('scrolled',window.scrollY>10));

// Hamburger
const ham=document.getElementById('hamburger'),nl=document.getElementById('navLinks');
ham.addEventListener('click',()=>nl.classList.toggle('open'));

// Scroll reveal
const obs=new IntersectionObserver(entries=>{entries.forEach(e=>{if(e.isIntersecting){e.target.classList.add('visible');obs.unobserve(e.target)}})},{threshold:.1});
document.querySelectorAll('.reveal').forEach(el=>obs.observe(el));

</script>
</body>
</html>
