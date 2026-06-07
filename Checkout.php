<?php
session_start();
include('connection.php');

if(!isset($_SESSION['user_id'])){
    header("Location: Login.php");
    exit();
}

$cart = $_SESSION['cart'] ?? [];
if(empty($cart)){
    header("Location: Cart.php");
    exit();
}

// Build cart summary
$cart_items = [];
$total = 0;
foreach($cart as $pid => $qty){
    $pid = (int)$pid;
    $stmt = mysqli_prepare($conn, "SELECT * FROM products WHERE id = ? LIMIT 1");
    mysqli_stmt_bind_param($stmt, "i", $pid);
    mysqli_stmt_execute($stmt);
    $row = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
    mysqli_stmt_close($stmt);
    if($row){
        $row['qty'] = $qty;
        $row['subtotal'] = $row['price'] * $qty;
        $total += $row['subtotal'];
        $cart_items[] = $row;
    }
}
$delivery_fee = 150;
$grand_total  = $total + $delivery_fee;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout — Brew&amp;Bite</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,700;0,900;1,700&family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
    :root{--amber:#FF8C00;--amber-light:#FFB347;--amber-pale:#FFF3E0;--ember:#FF6B35;--ember-dark:#E65C2E;--cream:#FFF8F0;--charcoal:#2D2D2D;--muted:#888;--white:#fff;--radius:18px;--transition:.35s cubic-bezier(.4,0,.2,1);--nav-h:68px;}
    *,*::before,*::after{box-sizing:border-box;margin:0;padding:0}
    body{font-family:'Poppins',sans-serif;background:var(--cream);color:var(--charcoal)}
    .navbar-bb{position:fixed;top:0;left:0;right:0;height:var(--nav-h);z-index:500;display:flex;align-items:center;justify-content:space-between;padding:0 40px;background:rgba(255,255,255,.95);backdrop-filter:blur(18px);border-bottom:1px solid rgba(255,179,71,.22)}
    .nav-brand{font-family:'Playfair Display',serif;font-weight:900;font-size:1.6rem;color:var(--amber);text-decoration:none;letter-spacing:-.5px}
    .nav-brand span{color:var(--ember)}
    @media(max-width:768px){.navbar-bb{padding:0 20px}}

    .checkout-page{max-width:1100px;margin:0 auto;padding:calc(var(--nav-h) + 40px) 40px 80px}
    @media(max-width:768px){.checkout-page{padding:calc(var(--nav-h) + 24px) 20px 60px}}

    .page-title{font-family:'Playfair Display',serif;font-size:1.8rem;font-weight:900;color:var(--charcoal);margin-bottom:6px}
    .page-sub{font-size:.84rem;color:var(--muted);margin-bottom:32px}

    /* STEPS */
    .checkout-steps{display:flex;gap:0;margin-bottom:36px}
    .cstep{display:flex;align-items:center;gap:8px;font-size:.8rem;font-weight:600;color:var(--muted)}
    .cstep.active{color:var(--amber)}
    .cstep .dot{width:28px;height:28px;border-radius:50%;background:rgba(255,179,71,.2);display:flex;align-items:center;justify-content:center;font-size:.75rem;color:var(--muted)}
    .cstep.active .dot{background:linear-gradient(135deg,var(--amber),var(--ember));color:#fff}
    .cstep .line{width:40px;height:2px;background:rgba(255,179,71,.2);margin:0 6px}

    .checkout-grid{display:grid;grid-template-columns:1.2fr 1fr;gap:32px;align-items:start}
    @media(max-width:900px){.checkout-grid{grid-template-columns:1fr}}

    /* FORM */
    .checkout-form{background:var(--white);border:1px solid rgba(255,179,71,.18);border-radius:var(--radius);padding:32px}
    .form-section{font-size:.75rem;font-weight:700;text-transform:uppercase;letter-spacing:1.5px;color:var(--muted);margin-bottom:16px;padding-bottom:8px;border-bottom:1px dashed rgba(255,179,71,.25)}
    .form-group{margin-bottom:16px}
    .form-group label{font-size:.78rem;font-weight:600;color:var(--charcoal);display:block;margin-bottom:5px}
    .input-wrap{position:relative}
    .input-wrap i{position:absolute;left:13px;top:50%;transform:translateY(-50%);color:var(--muted);font-size:.82rem;pointer-events:none}
    .input-wrap input,.input-wrap select,.input-wrap textarea{width:100%;padding:11px 14px 11px 38px;border:1.5px solid rgba(255,179,71,.25);border-radius:12px;font-family:'Poppins',sans-serif;font-size:.86rem;color:var(--charcoal);background:var(--cream);outline:none;transition:var(--transition)}
    .input-wrap input:focus,.input-wrap select:focus,.input-wrap textarea:focus{border-color:var(--amber);background:#fff;box-shadow:0 0 0 3px rgba(255,140,0,.1)}
    .form-row{display:grid;grid-template-columns:1fr 1fr;gap:14px}
    @media(max-width:480px){.form-row{grid-template-columns:1fr}}

    /* PAYMENT OPTIONS */
    .pay-options{display:flex;flex-direction:column;gap:10px;margin-bottom:6px}
    .pay-option{display:flex;align-items:center;gap:12px;padding:14px 16px;border:1.5px solid rgba(255,179,71,.25);border-radius:12px;cursor:pointer;transition:var(--transition)}
    .pay-option:hover{border-color:var(--amber);background:var(--amber-pale)}
    .pay-option input[type=radio]{accent-color:var(--amber);width:16px;height:16px}
    .pay-option.selected{border-color:var(--amber);background:var(--amber-pale)}
    .pay-icon{width:36px;height:36px;border-radius:8px;background:var(--white);border:1px solid rgba(255,179,71,.2);display:flex;align-items:center;justify-content:center;font-size:.9rem;color:var(--amber)}
    .pay-label{font-size:.86rem;font-weight:600;color:var(--charcoal)}
    .pay-sub{font-size:.74rem;color:var(--muted)}

    /* ORDER SUMMARY */
    .order-summary{background:var(--white);border:1px solid rgba(255,179,71,.18);border-radius:var(--radius);padding:28px;position:sticky;top:calc(var(--nav-h) + 20px)}
    .summary-title{font-family:'Playfair Display',serif;font-size:1.15rem;font-weight:700;color:var(--charcoal);margin-bottom:18px}
    .summary-items{display:flex;flex-direction:column;gap:12px;margin-bottom:18px;max-height:260px;overflow-y:auto;padding-right:4px}
    .summary-item{display:flex;align-items:center;gap:10px}
    .si-img{width:44px;height:44px;border-radius:8px;object-fit:cover;background:var(--amber-pale);flex-shrink:0}
    .si-name{font-size:.82rem;font-weight:600;color:var(--charcoal);line-height:1.3}
    .si-qty{font-size:.74rem;color:var(--muted)}
    .si-price{font-size:.82rem;font-weight:700;color:var(--amber);margin-left:auto;flex-shrink:0}
    .summary-totals{border-top:1px solid rgba(255,179,71,.2);padding-top:14px;display:flex;flex-direction:column;gap:8px}
    .total-row{display:flex;justify-content:space-between;font-size:.84rem}
    .total-row span{color:var(--muted)}
    .total-row b{color:var(--charcoal)}
    .total-grand{display:flex;justify-content:space-between;margin-top:10px;padding-top:12px;border-top:2px solid rgba(255,179,71,.25)}
    .total-grand span{font-size:.95rem;font-weight:700;color:var(--charcoal)}
    .total-grand b{font-family:'Playfair Display',serif;font-size:1.3rem;font-weight:900;color:var(--amber)}

    .btn-place{width:100%;padding:14px;background:linear-gradient(135deg,var(--amber),var(--ember));color:#fff;border:none;border-radius:12px;font-family:'Poppins',sans-serif;font-size:.95rem;font-weight:700;cursor:pointer;transition:var(--transition);display:flex;align-items:center;justify-content:center;gap:8px;margin-top:20px}
    .btn-place:hover{background:linear-gradient(135deg,var(--ember-dark),#c44d25);transform:translateY(-2px);box-shadow:0 8px 24px rgba(255,107,53,.35)}
    .secure-badge{display:flex;align-items:center;justify-content:center;gap:6px;font-size:.74rem;color:var(--muted);margin-top:10px}
    .secure-badge i{color:var(--amber)}
    </style>
</head>
<body>

<!-- NAVBAR -->
<nav class="navbar-bb">
    <a class="nav-brand" href="index.php">Brew<span>&</span>Bite</a>
    <a href="Cart.php" style="color:var(--muted);text-decoration:none;font-size:.84rem;font-weight:500;display:flex;align-items:center;gap:6px"><i class="fas fa-arrow-left"></i> Back to Cart</a>
</nav>

<div class="checkout-page">
    <div class="page-title">Checkout</div>
    <p class="page-sub">Complete your details below to place your order.</p>

    <!-- STEPS -->
    <div class="checkout-steps">
        <div class="cstep"><div class="dot">1</div> Cart</div>
        <div class="cstep active"><div class="dot" style="margin-left:8px"><i class="fas fa-check" style="font-size:.6rem"></i></div> Details</div>
        <div class="cstep" style="margin-left:8px"><div class="dot">3</div> Confirm</div>
    </div>

    <div class="checkout-grid">
        <!-- FORM -->
        <div class="checkout-form">
            <form action="place_order.php" method="POST" id="checkoutForm" novalidate>

                <div class="form-section">Delivery Details</div>
                <div class="form-row">
                    <div class="form-group">
                        <label>Full Name *</label>
                        <div class="input-wrap"><i class="fas fa-user"></i>
                            <input type="text" name="name" placeholder="Sara Khan" required
                                   value="<?php echo isset($_SESSION['user_name']) ? htmlspecialchars($_SESSION['user_name']) : ''; ?>">
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Phone *</label>
                        <div class="input-wrap"><i class="fas fa-phone"></i>
                            <input type="tel" name="phone" placeholder="+92 300 0000000" required>
                        </div>
                    </div>
                </div>
                <div class="form-group">
                    <label>Delivery Address *</label>
                    <div class="input-wrap"><i class="fas fa-map-marker-alt"></i>
                        <input type="text" name="address" placeholder="House #, Street, Area, City" required>
                    </div>
                </div>
                <div class="form-group">
                    <label>Order Notes (optional)</label>
                    <div class="input-wrap" style="position:relative">
                        <i class="fas fa-comment-dots" style="position:absolute;left:13px;top:14px;color:var(--muted);font-size:.82rem;pointer-events:none"></i>
                        <textarea name="notes" placeholder="Any special instructions?" style="width:100%;padding:11px 14px 11px 38px;border:1.5px solid rgba(255,179,71,.25);border-radius:12px;font-family:'Poppins',sans-serif;font-size:.86rem;color:var(--charcoal);background:var(--cream);outline:none;resize:none;min-height:80px;transition:var(--transition)" onfocus="this.style.borderColor='var(--amber)'" onblur="this.style.borderColor='rgba(255,179,71,.25)'"></textarea>
                    </div>
                </div>

                <div class="form-section" style="margin-top:24px">Payment Method</div>
                <div class="pay-options">
                    <label class="pay-option selected" id="cod">
                        <input type="radio" name="payment" value="Cash on Delivery" checked onchange="selectPay(this)">
                        <div class="pay-icon"><i class="fas fa-money-bill-wave"></i></div>
                        <div><div class="pay-label">Cash on Delivery</div><div class="pay-sub">Pay when your order arrives</div></div>
                    </label>
                    <label class="pay-option" id="online">
                        <input type="radio" name="payment" value="Online Payment" onchange="selectPay(this)">
                        <div class="pay-icon"><i class="fas fa-credit-card"></i></div>
                        <div><div class="pay-label">Online Payment</div><div class="pay-sub">Card, JazzCash, EasyPaisa</div></div>
                    </label>
                </div>

                <button type="submit" class="btn-place"><i class="fas fa-check-circle"></i> Place Order Rs <?php echo number_format($grand_total); ?></button>
                <div class="secure-badge"><i class="fas fa-shield-alt"></i> Secure &amp; encrypted checkout</div>
            </form>
        </div>

        <!-- SUMMARY -->
        <div class="order-summary">
            <div class="summary-title">Order Summary <span style="font-family:'Poppins';font-size:.78rem;font-weight:500;color:var(--muted)">(<?php echo count($cart_items); ?> items)</span></div>
            <div class="summary-items">
                <?php foreach($cart_items as $item): ?>
                <div class="summary-item">
                    <img class="si-img" src="images/<?php echo htmlspecialchars($item['image']); ?>"
                         alt="<?php echo htmlspecialchars($item['item_name']); ?>"
                         onerror="this.src='images/default.png'">
                    <div style="flex:1;min-width:0">
                        <div class="si-name"><?php echo htmlspecialchars($item['item_name']); ?></div>
                        <div class="si-qty">x<?php echo $item['qty']; ?></div>
                    </div>
                    <div class="si-price">Rs <?php echo number_format($item['subtotal']); ?></div>
                </div>
                <?php endforeach; ?>
            </div>
            <div class="summary-totals">
                <div class="total-row"><span>Subtotal</span><b>Rs <?php echo number_format($total); ?></b></div>
                <div class="total-row"><span>Delivery Fee</span><b>Rs <?php echo number_format($delivery_fee); ?></b></div>
            </div>
            <div class="total-grand">
                <span>Total</span>
                <b>Rs <?php echo number_format($grand_total); ?></b>
            </div>
        </div>
    </div>
</div>

<script>
function selectPay(radio){
    document.querySelectorAll('.pay-option').forEach(el=>el.classList.remove('selected'));
    radio.closest('.pay-option').classList.add('selected');
}
</script>
</body>
</html>