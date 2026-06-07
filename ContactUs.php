<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contact Us — Brew&amp;Bite</title>
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
    .hamburger{display:none;flex-direction:column;gap:5px;cursor:pointer;padding:6px}
    .hamburger span{width:24px;height:2.5px;background:var(--charcoal);border-radius:2px;transition:var(--transition)}
    @media(max-width:900px){.hamburger{display:flex}.nav-links{position:fixed;top:var(--nav-h);left:0;right:0;background:rgba(255,255,255,.97);backdrop-filter:blur(18px);flex-direction:column;padding:20px 30px 30px;gap:4px;border-bottom:1px solid rgba(255,179,71,.2);transform:translateY(-120%);transition:transform .4s cubic-bezier(.4,0,.2,1);box-shadow:0 10px 30px rgba(0,0,0,.08)}.nav-links.open{transform:translateY(0)}.nav-links a{padding:12px 16px;width:100%;border-radius:10px}.navbar-bb{padding:0 20px}}

    /* PAGE HEADER */
    .contact-header{padding:calc(var(--nav-h) + 50px) 40px 50px;background:linear-gradient(135deg,rgba(255,179,71,.14) 0%,rgba(255,107,53,.08) 100%),var(--cream);text-align:center}
    .eyebrow{display:inline-block;font-size:.7rem;font-weight:700;letter-spacing:2.5px;text-transform:uppercase;color:var(--ember);margin-bottom:10px}
    .contact-header h1{font-family:'Playfair Display',serif;font-size:clamp(2rem,5vw,3rem);font-weight:900;color:var(--charcoal);margin-bottom:12px}
    .contact-header h1 em{font-style:italic;background:linear-gradient(90deg,var(--amber),var(--ember));-webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text}
    .contact-header p{color:var(--muted);font-size:.92rem;max-width:420px;margin:0 auto;line-height:1.7}

    /* MAIN GRID */
    .contact-body{max-width:1200px;margin:0 auto;padding:60px 40px 80px}
    @media(max-width:768px){.contact-body{padding:40px 20px 60px}}
    .contact-grid{display:grid;grid-template-columns:1fr 1.4fr;gap:50px;align-items:start}
    @media(max-width:900px){.contact-grid{grid-template-columns:1fr;gap:36px}}

    /* INFO CARDS */
    .info-cards{display:flex;flex-direction:column;gap:16px}
    .info-card{background:var(--white);border:1px solid rgba(255,179,71,.18);border-radius:16px;padding:20px 22px;display:flex;align-items:flex-start;gap:16px;transition:var(--transition)}
    .info-card:hover{transform:translateY(-3px);box-shadow:0 12px 30px rgba(255,140,0,.14)}
    .info-icon{width:46px;height:46px;border-radius:12px;background:linear-gradient(135deg,var(--amber),var(--ember));display:flex;align-items:center;justify-content:center;color:#fff;font-size:1rem;flex-shrink:0}
    .info-label{font-size:.72rem;font-weight:700;text-transform:uppercase;letter-spacing:1.5px;color:var(--muted);margin-bottom:4px}
    .info-value{font-size:.9rem;color:var(--charcoal);font-weight:500;line-height:1.5}
    .info-value a{color:var(--ember);text-decoration:none}
    .info-value a:hover{color:var(--amber)}

    /* HOURS */
    .hours-card{background:var(--white);border:1px solid rgba(255,179,71,.18);border-radius:16px;padding:22px;margin-top:16px}
    .hours-title{font-weight:700;font-size:.88rem;color:var(--charcoal);margin-bottom:14px;display:flex;align-items:center;gap:8px}
    .hours-title i{color:var(--amber)}
    .hours-row{display:flex;justify-content:space-between;padding:8px 0;border-bottom:1px dashed rgba(255,179,71,.2);font-size:.84rem}
    .hours-row:last-child{border-bottom:none}
    .hours-row b{color:var(--charcoal);font-weight:600}
    .hours-row span{color:var(--muted)}
    .hours-row .open{color:#27ae60;font-weight:600}

    /* FORM */
    .contact-form-wrap{background:var(--white);border:1px solid rgba(255,179,71,.2);border-radius:var(--radius);padding:38px;box-shadow:0 8px 32px rgba(255,140,0,.1)}
    .form-title{font-family:'Playfair Display',serif;font-size:1.4rem;font-weight:700;color:var(--charcoal);margin-bottom:6px}
    .form-sub{font-size:.82rem;color:var(--muted);margin-bottom:26px}
    .form-row{display:grid;grid-template-columns:1fr 1fr;gap:16px}
    @media(max-width:500px){.form-row{grid-template-columns:1fr}}
    .form-group{margin-bottom:18px}
    .form-group label{font-size:.78rem;font-weight:600;color:var(--charcoal);display:block;margin-bottom:5px;letter-spacing:.3px}
    .input-wrap{position:relative}
    .input-wrap i{position:absolute;left:13px;top:50%;transform:translateY(-50%);color:var(--muted);font-size:.82rem;pointer-events:none}
    .input-wrap input,.input-wrap textarea,.input-wrap select{width:100%;padding:11px 14px 11px 38px;border:1.5px solid rgba(255,179,71,.25);border-radius:12px;font-family:'Poppins',sans-serif;font-size:.86rem;color:var(--charcoal);background:var(--cream);outline:none;transition:var(--transition)}
    .input-wrap textarea{padding:11px 14px 11px 38px;resize:vertical;min-height:130px}
    .input-wrap.top-icon i{top:16px;transform:none}
    .input-wrap input:focus,.input-wrap textarea:focus,.input-wrap select:focus{border-color:var(--amber);background:#fff;box-shadow:0 0 0 3px rgba(255,140,0,.1)}
    .btn-submit{width:100%;padding:13px;background:linear-gradient(135deg,var(--amber),var(--ember));color:#fff;border:none;border-radius:12px;font-family:'Poppins',sans-serif;font-size:.92rem;font-weight:700;cursor:pointer;transition:var(--transition);display:flex;align-items:center;justify-content:center;gap:8px}
    .btn-submit:hover{background:linear-gradient(135deg,var(--ember-dark),#c44d25);transform:translateY(-2px);box-shadow:0 8px 24px rgba(255,107,53,.35)}
    .alert-box{border-radius:10px;padding:11px 14px;font-size:.82rem;margin-bottom:18px;display:flex;align-items:center;gap:8px}
    .alert-success{background:#f0faf0;border:1px solid #b2dfb2;color:#27ae60}
    .char-count{font-size:.72rem;color:var(--muted);text-align:right;margin-top:3px}

    /* REVEAL */
    .reveal{opacity:0;transform:translateY(22px);transition:opacity .6s ease,transform .6s ease}
    .reveal.visible{opacity:1;transform:translateY(0)}
    .reveal-delay-1{transition-delay:.1s}.reveal-delay-2{transition-delay:.2s}

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
        <li><a href="AboutUs.php">About Us</a></li>
        <li><a href="ContactUs.php" class="active">Contact Us</a></li>
        <li><a href="Login.php"><i class="fas fa-user"></i> Login</a></li>
        <li><a href="Cart.php" class="cart-link"><i class="fas fa-shopping-bag"></i> Cart</a></li>
    </ul>
</nav>

<!-- HEADER -->
<div class="contact-header">
    <span class="eyebrow">We'd love to hear from you</span>
    <h1>Get in <em>Touch</em></h1>
    <p>Have a question, feedback or want to make a reservation? Drop us a message and we'll get back to you shortly.</p>
</div>

<!-- BODY -->
<div class="contact-body">
    <div class="contact-grid">

        <!-- LEFT: INFO -->
        <div>
            <div class="info-cards reveal">
                <div class="info-card">
                    <div class="info-icon"><i class="fas fa-map-marker-alt"></i></div>
                    <div>
                        <div class="info-label">Location</div>
                        <div class="info-value">123 Cafe Street, F-7 Markaz<br>Islamabad, Pakistan</div>
                    </div>
                </div>
                <div class="info-card">
                    <div class="info-icon"><i class="fas fa-phone-alt"></i></div>
                    <div>
                        <div class="info-label">Phone</div>
                        <div class="info-value"><a href="tel:+923001234567">+92 300 1234567</a></div>
                    </div>
                </div>
                <div class="info-card">
                    <div class="info-icon"><i class="fas fa-envelope"></i></div>
                    <div>
                        <div class="info-label">Email</div>
                        <div class="info-value"><a href="mailto:hello@brewnbite.pk">hello@brewnbite.pk</a></div>
                    </div>
                </div>
                <div class="info-card">
                    <div class="info-icon"><i class="fab fa-instagram"></i></div>
                    <div>
                        <div class="info-label">Follow Us</div>
                        <div class="info-value"><a href="#">@brewnbite.pk</a></div>
                    </div>
                </div>
            </div>
            <div class="hours-card reveal reveal-delay-1">
                <div class="hours-title"><i class="fas fa-clock"></i> Opening Hours</div>
                <div class="hours-row"><b>Monday – Friday</b><span class="open">9:00 AM – 11:00 PM</span></div>
                <div class="hours-row"><b>Saturday – Sunday</b><span class="open">10:00 AM – 12:00 AM</span></div>
            </div>
        </div>

        <!-- RIGHT: FORM -->
        <div class="contact-form-wrap reveal reveal-delay-2">
            <div class="form-title">Send Us a Message</div>
            <p class="form-sub">We typically respond within a few hours.</p>
            <div id="successMsg" class="alert-box alert-success" style="display:none">
                <i class="fas fa-check-circle"></i> Your message has been sent! We'll reply soon.
            </div>
            <form id="contactForm" novalidate>
                <div class="form-row">
                    <div class="form-group">
                        <label>Your Name </label>
                        <div class="input-wrap">
                            <i class="fas fa-user"></i>
                            <input type="text" id="c_name" placeholder="Sara Khan" required>
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Email Address </label>
                        <div class="input-wrap">
                            <i class="fas fa-envelope"></i>
                            <input type="email" id="c_email" placeholder="your@email.com" required>
                        </div>
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label>Phone (optional)</label>
                        <div class="input-wrap">
                            <i class="fas fa-phone"></i>
                            <input type="tel" id="c_phone" placeholder="+92 300 0000000">
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Subject </label>
                        <div class="input-wrap">
                            <i class="fas fa-tag"></i>
                            <select id="c_subject" required>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="form-group">
                    <label>Your Message *</label>
                    <div class="input-wrap top-icon">
                        <i class="fas fa-comment-dots"></i>
                        <textarea id="c_message" placeholder="Tell us how we can help..." required maxlength="500" oninput="updateCount(this)"></textarea>
                    </div>
                    <div class="char-count"><span id="charCount">0</span>/500</div>
                </div>
                <button type="submit" class="btn-submit">
                    <i class="fas fa-paper-plane"></i> Send Message
                </button>
            </form>
        </div>
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
const obs=new IntersectionObserver(entries=>{entries.forEach(e=>{if(e.isIntersecting){e.target.classList.add('visible');obs.unobserve(e.target)}})},{threshold:.1});
document.querySelectorAll('.reveal').forEach(el=>obs.observe(el));

function updateCount(el){ document.getElementById('charCount').textContent = el.value.length; }

document.getElementById('contactForm').addEventListener('submit', function(e){
    e.preventDefault();
    const name=document.getElementById('c_name').value.trim();
    const email=document.getElementById('c_email').value.trim();
    const subject=document.getElementById('c_subject').value;
    const msg=document.getElementById('c_message').value.trim();
    if(!name||!email||!subject||!msg){ alert('Please fill in all required fields.'); return; }
    const btn=this.querySelector('.btn-submit');
    btn.innerHTML='<i class="fas fa-spinner fa-spin"></i> Sending...';
    btn.disabled=true;
    setTimeout(()=>{
        document.getElementById('successMsg').style.display='flex';
        this.reset();
        document.getElementById('charCount').textContent='0';
        btn.innerHTML='<i class="fas fa-paper-plane"></i> Send Message';
        btn.disabled=false;
        document.getElementById('successMsg').scrollIntoView({behavior:'smooth',block:'nearest'});
    },1200);
});
</script>
</body>
</html>