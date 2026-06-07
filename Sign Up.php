<?php
session_start();
include 'connection.php';

if(isset($_SESSION['user_id'])){
    header("Location: Dashboard.php");
    exit();
}

$error = '';

if(isset($_POST['signup'])){
    $fullname         = trim($_POST['fullname']);
    $email            = trim($_POST['email']);
    $password         = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $gender           = $_POST['gender'];
    $dob              = $_POST['dob'];
    $phone            = trim($_POST['phone']);
    $address          = trim($_POST['address']);

    if(empty($fullname)||empty($email)||empty($password)||empty($gender)||empty($dob)||empty($phone)||empty($address)){
        $error = 'Please fill in all fields.';
    } elseif($password !== $confirm_password){
        $error = 'Passwords do not match.';
    } elseif(strlen($password) < 6){
        $error = 'Password must be at least 6 characters.';
    } else {
        // Check if email already exists
        $check = mysqli_prepare($conn, "SELECT id FROM users WHERE email = ? LIMIT 1");
        mysqli_stmt_bind_param($check, "s", $email);
        mysqli_stmt_execute($check);
        mysqli_stmt_store_result($check);
        if(mysqli_stmt_num_rows($check) > 0){
            $error = 'DUPLICATE_EMAIL';
        } else {
            $hashed = password_hash($password, PASSWORD_DEFAULT);
            $stmt = mysqli_prepare($conn,
                "INSERT INTO users(fullname,email,password,gender,dob,phone,address) VALUES(?,?,?,?,?,?,?)");
            mysqli_stmt_bind_param($stmt,"sssssss",$fullname,$email,$hashed,$gender,$dob,$phone,$address);
            if(mysqli_stmt_execute($stmt)){
                $user_id = mysqli_insert_id($conn);
                $_SESSION['user_id']    = $user_id;
                $_SESSION['user_name']  = $fullname;
                $_SESSION['user_email'] = $email;
                mysqli_stmt_close($stmt);
                header("Location: Dashboard.php");
                exit();
            } else {
                $error = 'An error occurred. Please try again.';
            }
            mysqli_stmt_close($stmt);
        }
        mysqli_stmt_close($check);
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign Up — Brew&amp;Bite</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,700;0,900;1,700&family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
    :root{
        --amber:#FF8C00;--amber-light:#FFB347;--amber-pale:#FFF3E0;
        --ember:#FF6B35;--ember-dark:#E65C2E;--cream:#FFF8F0;
        --charcoal:#2D2D2D;--muted:#888;--white:#fff;
        --transition:.35s cubic-bezier(.4,0,.2,1);--nav-h:68px;
    }
    *,*::before,*::after{box-sizing:border-box;margin:0;padding:0}
    html{scroll-behavior:smooth}
    body{font-family:'Poppins',sans-serif;background:var(--cream);color:var(--charcoal);min-height:100vh;}

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
    @media(max-width:900px){
        .hamburger{display:flex}
        .nav-links{position:fixed;top:var(--nav-h);left:0;right:0;background:rgba(255,255,255,.97);backdrop-filter:blur(18px);flex-direction:column;padding:20px 30px 30px;gap:4px;border-bottom:1px solid rgba(255,179,71,.2);transform:translateY(-120%);transition:transform .4s cubic-bezier(.4,0,.2,1);box-shadow:0 10px 30px rgba(0,0,0,.08)}
        .nav-links.open{transform:translateY(0)}
        .nav-links a{padding:12px 16px;width:100%;border-radius:10px}
        .navbar-bb{padding:0 20px}
    }

    /* PAGE */
    .auth-page{
        min-height:100vh;
        padding-top:calc(var(--nav-h) + 30px);
        padding-bottom:40px;
        display:flex;align-items:flex-start;justify-content:center;
        background:
            radial-gradient(ellipse 700px 500px at 0% 60%,rgba(255,179,71,.15) 0%,transparent 65%),
            radial-gradient(ellipse 500px 400px at 100% 20%,rgba(255,107,53,.1) 0%,transparent 60%),
            var(--cream);
        padding-left:20px;padding-right:20px;
    }
    .auth-card{
        width:100%;max-width:520px;
        background:var(--white);
        border:1px solid rgba(255,179,71,.2);
        border-radius:24px;
        padding:44px 40px;
        box-shadow:0 20px 60px rgba(255,140,0,.12);
        animation:fadeUp .5s ease both;
    }
    @keyframes fadeUp{from{opacity:0;transform:translateY(20px)}to{opacity:1;transform:translateY(0)}}
    @media(max-width:500px){.auth-card{padding:32px 22px}}

    .auth-logo{font-family:'Playfair Display',serif;font-weight:900;font-size:1.8rem;color:var(--amber);text-decoration:none;display:block;text-align:center;margin-bottom:24px;}
    .auth-logo span{color:var(--ember)}

    /* Already have account box */
    .login-here-box{
        background:var(--amber-pale);
        border:1px solid rgba(255,140,0,.2);
        border-radius:12px;
        padding:13px 16px;
        display:flex;align-items:center;justify-content:space-between;gap:12px;
        margin-bottom:26px;
        font-size:.82rem;color:var(--charcoal);
    }
    .login-here-box span{line-height:1.4}
    .login-here-box a{
        white-space:nowrap;
        background:linear-gradient(135deg,var(--amber),var(--ember));
        color:#fff;font-weight:600;font-size:.78rem;
        padding:7px 16px;border-radius:50px;text-decoration:none;
        transition:var(--transition);flex-shrink:0;
    }
    .login-here-box a:hover{transform:translateY(-1px);box-shadow:0 4px 12px rgba(255,107,53,.3);color:#fff}

    .auth-title{font-family:'Playfair Display',serif;font-size:1.5rem;font-weight:700;color:var(--charcoal);margin-bottom:6px;text-align:center}
    .auth-sub{font-size:.82rem;color:var(--muted);text-align:center;margin-bottom:28px}

    /* Two-column grid for fields */
    .fields-grid{display:grid;grid-template-columns:1fr 1fr;gap:0 16px}
    .fields-grid .full-width{grid-column:1/-1}
    @media(max-width:500px){.fields-grid{grid-template-columns:1fr}}

    .form-group{margin-bottom:16px}
    .form-group label{font-size:.78rem;font-weight:600;color:var(--charcoal);display:block;margin-bottom:6px;letter-spacing:.3px}
    .input-wrap{position:relative}
    .input-wrap i.icon{position:absolute;left:14px;top:50%;transform:translateY(-50%);color:var(--muted);font-size:.82rem;pointer-events:none}
    .input-wrap input,
    .input-wrap select,
    .input-wrap textarea{
        width:100%;padding:11px 14px 11px 38px;
        border:1.5px solid rgba(255,179,71,.25);border-radius:12px;
        font-family:'Poppins',sans-serif;font-size:.86rem;color:var(--charcoal);
        background:var(--cream);outline:none;transition:var(--transition);
        appearance:none;-webkit-appearance:none;
    }
    .input-wrap textarea{
        padding-top:11px;resize:vertical;min-height:80px;
        padding-left:38px;
    }
    .input-wrap select{cursor:pointer}
    .input-wrap input:focus,
    .input-wrap select:focus,
    .input-wrap textarea:focus{border-color:var(--amber);background:#fff;box-shadow:0 0 0 3px rgba(255,140,0,.1)}
    /* Date placeholder color */
    .input-wrap input[type=date]{color:var(--charcoal)}
    .input-wrap input[type=date]:invalid{color:var(--muted)}

    /* Password toggle */
    .input-wrap .toggle-pw{position:absolute;right:12px;top:50%;transform:translateY(-50%);background:none;border:none;color:var(--muted);cursor:pointer;font-size:.82rem;padding:0}
    .input-wrap .toggle-pw:hover{color:var(--amber)}
    .input-wrap input.has-toggle{padding-right:38px}

    /* Date label helper */
    .dob-label-note{font-size:.7rem;color:var(--muted);font-weight:400;margin-left:4px}

    .btn-auth{
        width:100%;padding:13px;
        background:linear-gradient(135deg,var(--amber),var(--ember));
        color:#fff;border:none;border-radius:12px;
        font-family:'Poppins',sans-serif;font-size:.92rem;font-weight:700;
        cursor:pointer;transition:var(--transition);
        display:flex;align-items:center;justify-content:center;gap:8px;
        margin-top:6px;
    }
    .btn-auth:hover{background:linear-gradient(135deg,var(--ember-dark),#c44d25);transform:translateY(-2px);box-shadow:0 8px 24px rgba(255,107,53,.35)}

    .alert-box{border-radius:10px;padding:11px 14px;font-size:.82rem;margin-bottom:20px;display:flex;align-items:center;gap:8px;}
    .alert-error{background:#fff0f0;border:1px solid #f5c6c6;color:#c0392b}
    .alert-success{background:#f0faf0;border:1px solid #b2dfb2;color:#27ae60}

    /* Password strength bar */
    .pw-strength{margin-top:6px;height:4px;border-radius:4px;background:rgba(255,179,71,.15);overflow:hidden}
    .pw-strength-bar{height:100%;width:0%;border-radius:4px;transition:width .3s,background .3s}
    .pw-strength-text{font-size:.68rem;color:var(--muted);margin-top:3px}
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
        <li><a href="Login.php"><i class="fas fa-user"></i> Login</a></li>
        <li><a href="Cart.php" class="cart-link"><i class="fas fa-shopping-bag"></i> Cart</a></li>
    </ul>
</nav>

<div class="auth-page">
    <div class="auth-card">

        <h2 class="auth-title">Create Your Account</h2>
        <p class="auth-sub">Join us and start ordering your favourites</p>

        <?php if($error === 'DUPLICATE_EMAIL'): ?>
        <div class="alert-box alert-error">
            <i class="fas fa-exclamation-circle"></i>
            This email is already registered. &nbsp;<a href="Login.php" style="color:var(--ember);font-weight:600;">Login instead →</a>
        </div>
        <?php elseif($error): ?>
        <div class="alert-box alert-error"><i class="fas fa-exclamation-circle"></i> <?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <form method="POST" novalidate>
            <div class="fields-grid">

                <!-- Full Name -->
                <div class="form-group full-width">
                    <label for="fullname">Full Name</label>
                    <div class="input-wrap">
                        <i class="fas fa-user icon"></i>
                        <input type="text" id="fullname" name="fullname" placeholder="Ahmed Khan"
                               value="<?= isset($_POST['fullname']) ? htmlspecialchars($_POST['fullname']) : '' ?>" required>
                    </div>
                </div>

                <!-- Email -->
                <div class="form-group full-width">
                    <label for="email">Email Address</label>
                    <div class="input-wrap">
                        <i class="fas fa-envelope icon"></i>
                        <input type="email" id="email" name="email" placeholder="your@email.com"
                               value="<?= isset($_POST['email']) ? htmlspecialchars($_POST['email']) : '' ?>" required>
                    </div>
                </div>

                <!-- Password -->
                <div class="form-group">
                    <label for="password">Password</label>
                    <div class="input-wrap">
                        <i class="fas fa-lock icon"></i>
                        <input type="password" id="password" name="password" placeholder="Min. 6 characters"
                               class="has-toggle" oninput="checkStrength(this.value)" required>
                        <button type="button" class="toggle-pw" onclick="togglePw('password','pwIcon1')" aria-label="Toggle">
                            <i class="fas fa-eye" id="pwIcon1"></i>
                        </button>
                    </div>
                    <div class="pw-strength"><div class="pw-strength-bar" id="pwBar"></div></div>
                    <div class="pw-strength-text" id="pwText"></div>
                </div>

                <!-- Confirm Password -->
                <div class="form-group">
                    <label for="confirm_password">Confirm Password</label>
                    <div class="input-wrap">
                        <i class="fas fa-lock icon"></i>
                        <input type="password" id="confirm_password" name="confirm_password" placeholder="Repeat password"
                               class="has-toggle" required>
                        <button type="button" class="toggle-pw" onclick="togglePw('confirm_password','pwIcon2')" aria-label="Toggle">
                            <i class="fas fa-eye" id="pwIcon2"></i>
                        </button>
                    </div>
                </div>

                <!-- Gender -->
                <div class="form-group">
                    <label for="gender">Gender</label>
                    <div class="input-wrap">
                        <i class="fas fa-venus-mars icon"></i>
                        <select id="gender" name="gender" required>
                            <option value="" disabled <?= !isset($_POST['gender']) ? 'selected' : '' ?>>Select gender</option>
                            <option value="M" <?= (isset($_POST['gender']) && $_POST['gender']==='M') ? 'selected' : '' ?>>Male</option>
                            <option value="F" <?= (isset($_POST['gender']) && $_POST['gender']==='F') ? 'selected' : '' ?>>Female</option>
                            <option value="O" <?= (isset($_POST['gender']) && $_POST['gender']==='O') ? 'selected' : '' ?>>Other</option>
                        </select>
                    </div>
                </div>

                <!-- Date of Birth -->
                <div class="form-group">
                    <label for="dob">Date of Birth <span class="dob-label-note">(DD/MM/YYYY)</span></label>
                    <div class="input-wrap">
                        <i class="fas fa-calendar-alt icon"></i>
                        <input type="date" id="dob" name="dob"
                               value="<?= isset($_POST['dob']) ? htmlspecialchars($_POST['dob']) : '' ?>" required>
                    </div>
                </div>

                <!-- Phone -->
                <div class="form-group full-width">
                    <label for="phone">Phone Number</label>
                    <div class="input-wrap">
                        <i class="fas fa-phone icon"></i>
                        <input type="tel" id="phone" name="phone" placeholder="03XX-XXXXXXX"
                               value="<?= isset($_POST['phone']) ? htmlspecialchars($_POST['phone']) : '' ?>" required>
                    </div>
                </div>

                <!-- Address -->
                <div class="form-group full-width">
                    <label for="address">Delivery Address</label>
                    <div class="input-wrap">
                        <i class="fas fa-map-marker-alt icon"></i>
                        <textarea id="address" name="address" placeholder="House #, Street, Area, City" required><?= isset($_POST['address']) ? htmlspecialchars($_POST['address']) : '' ?></textarea>
                    </div>
                </div>

            </div>

            <button type="submit" name="signup" class="btn-auth">
                <i class="fas fa-user-plus"></i> Create Account
            </button>
        </form>

    </div>
</div>

<script>
// Navbar
window.addEventListener('scroll',()=>document.getElementById('mainNav').classList.toggle('scrolled',window.scrollY>10));
const ham=document.getElementById('hamburger'),nl=document.getElementById('navLinks');
ham.addEventListener('click',()=>nl.classList.toggle('open'));

// Password toggle
function togglePw(fieldId, iconId){
    const pw=document.getElementById(fieldId), ic=document.getElementById(iconId);
    if(pw.type==='password'){pw.type='text';ic.className='fas fa-eye-slash';}
    else{pw.type='password';ic.className='fas fa-eye';}
}

// Password strength
function checkStrength(val){
    const bar=document.getElementById('pwBar'), txt=document.getElementById('pwText');
    let score=0;
    if(val.length>=6) score++;
    if(val.length>=10) score++;
    if(/[A-Z]/.test(val)) score++;
    if(/[0-9]/.test(val)) score++;
    if(/[^A-Za-z0-9]/.test(val)) score++;
    const map=[
        {w:'0%',  bg:'transparent', label:''},
        {w:'25%', bg:'#e74c3c',     label:'Weak'},
        {w:'50%', bg:'#e67e22',     label:'Fair'},
        {w:'75%', bg:'#f1c40f',     label:'Good'},
        {w:'100%',bg:'#27ae60',     label:'Strong'},
    ];
    const s=Math.min(score,4);
    bar.style.width=map[s].w; bar.style.background=map[s].bg;
    txt.textContent=map[s].label; txt.style.color=map[s].bg;
}
</script>
</body>
</html>