<?php
session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Placed — Brew&amp;Bite</title>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,700;0,900;1,700&family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
    :root{--amber:#FF8C00;--amber-light:#FFB347;--amber-pale:#FFF3E0;--ember:#FF6B35;--ember-dark:#E65C2E;--cream:#FFF8F0;--charcoal:#2D2D2D;--muted:#888;--white:#fff;--transition:.35s cubic-bezier(.4,0,.2,1);}
    *,*::before,*::after{box-sizing:border-box;margin:0;padding:0}
    body{font-family:'Poppins',sans-serif;background:var(--cream);color:var(--charcoal);min-height:100vh;display:flex;align-items:center;justify-content:center;padding:20px;
        background:radial-gradient(ellipse 800px 600px at 50% 50%,rgba(255,179,71,.14) 0%,transparent 70%),var(--cream);}

    .success-card{
        background:var(--white);border:1px solid rgba(255,179,71,.2);border-radius:28px;
        padding:52px 44px;text-align:center;max-width:480px;width:100%;
        box-shadow:0 24px 64px rgba(255,140,0,.14);
        animation:popIn .6s cubic-bezier(.34,1.56,.64,1) both;
    }
    @keyframes popIn{from{opacity:0;transform:scale(.85) translateY(30px)}to{opacity:1;transform:scale(1) translateY(0)}}
    @media(max-width:480px){.success-card{padding:36px 24px}}

    .success-icon-wrap{
        width:96px;height:96px;border-radius:50%;
        background:linear-gradient(135deg,var(--amber),var(--ember));
        display:flex;align-items:center;justify-content:center;
        margin:0 auto 24px;
        box-shadow:0 12px 32px rgba(255,107,53,.35);
        animation:checkPop .5s .4s cubic-bezier(.34,1.56,.64,1) both;
    }
    @keyframes checkPop{from{transform:scale(0)}to{transform:scale(1)}}
    .success-icon-wrap i{font-size:2.4rem;color:#fff}

    .brand{font-family:'Playfair Display',serif;font-weight:900;font-size:1.4rem;color:var(--amber);margin-bottom:20px;display:block}
    .brand span{color:var(--ember)}

    h1{font-family:'Playfair Display',serif;font-size:1.9rem;font-weight:900;color:var(--charcoal);margin-bottom:10px}
    h1 em{font-style:italic;background:linear-gradient(90deg,var(--amber),var(--ember));-webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text}
    .sub{font-size:.9rem;color:var(--muted);line-height:1.7;margin-bottom:32px}

    .order-info{background:var(--amber-pale);border-radius:14px;padding:18px 20px;margin-bottom:30px;text-align:left}
    .info-row{display:flex;align-items:center;gap:10px;font-size:.84rem;padding:5px 0}
    .info-row i{color:var(--amber);width:18px;flex-shrink:0}
    .info-row span{color:var(--charcoal);font-weight:500}

    .btn-group{display:flex;gap:12px;flex-wrap:wrap;justify-content:center}
    .btn-primary-bb{display:inline-flex;align-items:center;gap:8px;padding:12px 24px;background:linear-gradient(135deg,var(--amber),var(--ember));color:#fff;font-family:'Poppins',sans-serif;font-size:.88rem;font-weight:700;border-radius:50px;text-decoration:none;transition:var(--transition)}
    .btn-primary-bb:hover{background:linear-gradient(135deg,var(--ember-dark),#c44d25);transform:translateY(-2px);box-shadow:0 6px 20px rgba(255,107,53,.35);color:#fff}
    .btn-outline-bb{display:inline-flex;align-items:center;gap:8px;padding:11px 22px;background:transparent;color:var(--charcoal);font-family:'Poppins',sans-serif;font-size:.88rem;font-weight:600;border-radius:50px;text-decoration:none;border:1.5px solid rgba(255,179,71,.3);transition:var(--transition)}
    .btn-outline-bb:hover{border-color:var(--amber);color:var(--amber);background:var(--amber-pale)}

    .steps{display:flex;justify-content:center;gap:0;margin-top:30px}
    .step{display:flex;flex-direction:column;align-items:center;position:relative;flex:1;max-width:100px}
    .step:not(:last-child)::after{content:'';position:absolute;top:16px;left:60%;width:80%;height:2px;background:rgba(255,179,71,.3)}
    .step-dot{width:32px;height:32px;border-radius:50%;background:linear-gradient(135deg,var(--amber),var(--ember));display:flex;align-items:center;justify-content:center;color:#fff;font-size:.8rem;margin-bottom:6px;box-shadow:0 4px 12px rgba(255,107,53,.3);position:relative;z-index:1}
    .step-label{font-size:.7rem;font-weight:600;color:var(--muted);text-align:center;line-height:1.3}
    </style>
</head>
<body>
<div class="success-card">
    <a class="brand" href="index.php">Brew<span>&</span>Bite</a>

    <div class="success-icon-wrap">
        <i class="fas fa-check"></i>
    </div>

    <h1>Order <em>Confirmed!</em></h1>
    <p class="sub">Your order has been placed successfully. We're already preparing your food with love thank you for choosing Brew&amp;Bite!</p>

    <div class="order-info">
        <div class="info-row"><i class="fas fa-clock"></i><span>Estimated time: 25–35 minutes</span></div>
        <div class="info-row"><i class="fas fa-fire"></i><span>Prepared fresh to order</span></div>
        <div class="info-row"><i class="fas fa-envelope"></i><span>Confirmation sent to your email</span></div>
    </div>

    <div class="steps">
        <div class="step">
            <div class="step-dot"><i class="fas fa-check"></i></div>
            <div class="step-label">Order Placed</div>
        </div>
        <div class="step">
            <div class="step-dot" style="background:rgba(255,179,71,.3);box-shadow:none"><i class="fas fa-fire" style="color:var(--amber)"></i></div>
            <div class="step-label">Preparing</div>
        </div>
        <div class="step">
            <div class="step-dot" style="background:rgba(255,179,71,.3);box-shadow:none"><i class="fas fa-motorcycle" style="color:var(--amber)"></i></div>
            <div class="step-label">On the Way</div>
        </div>
        <div class="step">
            <div class="step-dot" style="background:rgba(255,179,71,.3);box-shadow:none"><i class="fas fa-home" style="color:var(--amber)"></i></div>
            <div class="step-label">Delivered</div>
        </div>
    </div>

    <div class="btn-group" style="margin-top:28px">
        <?php if(isset($_SESSION['user_id'])): ?>
        <a href="Dashboard.php" class="btn-primary-bb"><i class="fas fa-list-alt"></i> My Orders</a>
        <?php endif; ?>
        <a href="Menu.php" class="btn-outline-bb"><i class=""></i> Order Again</a>
    </div>
</div>
</body>
</html>