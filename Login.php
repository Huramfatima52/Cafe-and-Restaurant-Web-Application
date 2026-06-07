<?php
session_start();
include('connection.php');

if(isset($_SESSION['user_id'])){
    header("Location: Dashboard.php");
    exit();
}

$error = '';
$success = '';

if(isset($_POST['login'])){
    $email    = trim($_POST['email']);
    $password = $_POST['password'];

    if(empty($email) || empty($password)){
        $error = 'Please fill in all fields.';
    } else {
        $stmt = mysqli_prepare($conn, "SELECT * FROM users WHERE email = ? LIMIT 1");
        mysqli_stmt_bind_param($stmt, "s", $email);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);

        if($user = mysqli_fetch_assoc($result)){
            $valid = password_verify($password, $user['password'])
                  || ($password === $user['password']);
            if($valid){
                $_SESSION['user_id']   = $user['id'];
                $_SESSION['user_name'] = $user['fullname'];
                $_SESSION['user_email']= $user['email'];
                header("Location: Dashboard.php");
                exit();
            } else {
                $error = 'Incorrect password. Please try again.';
            }
        } else {
            $error = 'No account found with that email.';
        }
        mysqli_stmt_close($stmt);
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login — Brew&amp;Bite</title>
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
        padding-top:var(--nav-h);
        display:flex;align-items:center;justify-content:center;
        background:
            radial-gradient(ellipse 700px 500px at 0% 60%,rgba(255,179,71,.15) 0%,transparent 65%),
            radial-gradient(ellipse 500px 400px at 100% 20%,rgba(255,107,53,.1) 0%,transparent 60%),
            var(--cream);
        padding-left:20px;padding-right:20px;
    }
    .auth-card{
        width:100%;max-width:460px;
        background:var(--white);
        border:1px solid rgba(255,179,71,.2);
        border-radius:24px;
        padding:44px 40px;
        box-shadow:0 20px 60px rgba(255,140,0,.12);
        animation:fadeUp .5s ease both;
    }
    @keyframes fadeUp{from{opacity:0;transform:translateY(20px)}to{opacity:1;transform:translateY(0)}}

    .auth-logo{
        font-family:'Playfair Display',serif;font-weight:900;font-size:1.8rem;
        color:var(--amber);text-decoration:none;display:block;text-align:center;margin-bottom:24px;
    }
    .auth-logo span{color:var(--ember)}

    .auth-title{font-family:'Playfair Display',serif;font-size:1.5rem;font-weight:700;color:var(--charcoal);margin-bottom:6px;text-align:center}
    .auth-sub{font-size:.82rem;color:var(--muted);text-align:center;margin-bottom:28px}

    .form-group{margin-bottom:18px}
    .form-group label{font-size:.78rem;font-weight:600;color:var(--charcoal);display:block;margin-bottom:6px;letter-spacing:.3px}
    .input-wrap{position:relative}
    .input-wrap i.icon{position:absolute;left:14px;top:50%;transform:translateY(-50%);color:var(--muted);font-size:.85rem;pointer-events:none}
    .input-wrap input{
        width:100%;padding:12px 14px 12px 40px;
        border:1.5px solid rgba(255,179,71,.25);border-radius:12px;
        font-family:'Poppins',sans-serif;font-size:.88rem;color:var(--charcoal);
        background:var(--cream);outline:none;transition:var(--transition);
    }
    .input-wrap input:focus{border-color:var(--amber);background:#fff;box-shadow:0 0 0 3px rgba(255,140,0,.1)}
    .input-wrap .toggle-pw{position:absolute;right:14px;top:50%;transform:translateY(-50%);background:none;border:none;color:var(--muted);cursor:pointer;font-size:.85rem;padding:0}
    .input-wrap .toggle-pw:hover{color:var(--amber)}

    .form-options{display:flex;align-items:center;justify-content:space-between;margin-bottom:20px;font-size:.8rem}
    .form-options label{display:flex;align-items:center;gap:7px;color:var(--charcoal);cursor:pointer}
    .form-options input[type=checkbox]{accent-color:var(--amber);width:15px;height:15px}
    .form-options a{color:var(--ember);text-decoration:none;font-weight:500}
    .form-options a:hover{color:var(--amber)}

    .btn-auth{
        width:100%;padding:13px;
        background:linear-gradient(135deg,var(--amber),var(--ember));
        color:#fff;border:none;border-radius:12px;
        font-family:'Poppins',sans-serif;font-size:.92rem;font-weight:700;
        cursor:pointer;transition:var(--transition);
        display:flex;align-items:center;justify-content:center;gap:8px;
    }
    .btn-auth:hover{background:linear-gradient(135deg,var(--ember-dark),#c44d25);transform:translateY(-2px);box-shadow:0 8px 24px rgba(255,107,53,.35)}

    .alert-box{
        border-radius:10px;padding:11px 14px;font-size:.82rem;margin-bottom:20px;
        display:flex;align-items:center;gap:8px;
    }
    .alert-error{background:#fff0f0;border:1px solid #f5c6c6;color:#c0392b}
    .alert-success{background:#f0faf0;border:1px solid #b2dfb2;color:#27ae60}

    .divider{display:flex;align-items:center;gap:12px;margin:20px 0}
    .divider span{flex:1;height:1px;background:rgba(255,179,71,.2)}
    .divider p{font-size:.75rem;color:var(--muted);white-space:nowrap}

    .auth-switch{text-align:center;font-size:.82rem;color:var(--muted);margin-top:22px}
    .auth-switch a{color:var(--ember);font-weight:600;text-decoration:none}
    .auth-switch a:hover{color:var(--amber)}

    /* New account prompt — shown ABOVE the form */
    .new-here-box{
        background:var(--amber-pale);
        border:1px solid rgba(255,140,0,.2);
        border-radius:12px;
        padding:13px 16px;
        display:flex;align-items:center;justify-content:space-between;gap:12px;
        margin-bottom:26px;
        font-size:.82rem;color:var(--charcoal);
    }
    .new-here-box span{line-height:1.4}
    .new-here-box a{
        white-space:nowrap;
        background:linear-gradient(135deg,var(--amber),var(--ember));
        color:#fff;font-weight:600;font-size:.78rem;
        padding:7px 16px;border-radius:50px;text-decoration:none;
        transition:var(--transition);flex-shrink:0;
    }
    .new-here-box a:hover{transform:translateY(-1px);box-shadow:0 4px 12px rgba(255,107,53,.3);color:#fff}
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
        <li><a href="Login.php" class="active"><i class="fas fa-user"></i> Login</a></li>
        <li><a href="Cart.php" class="cart-link"><i class="fas fa-shopping-bag"></i> Cart <span style="background:#fff;color:var(--ember);border-radius:50px;font-size:.72rem;font-weight:700;padding:1px 7px;">0</span></a></li>
    </ul>
</nav>

<div class="auth-page">
    <div class="auth-card">


        <!-- Login heading -->
        <h2 class="auth-title">Welcome Back</h2>
        <p class="auth-sub">Login to your account to continue</p>

        <?php if($error): ?>
        <div class="alert-box alert-error"><i class="fas fa-exclamation-circle"></i> <?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
        <?php if($success): ?>
        <div class="alert-box alert-success"><i class="fas fa-check-circle"></i> <?php echo htmlspecialchars($success); ?></div>
        <?php endif; ?>

        <form method="POST" novalidate>
            <div class="form-group">
                <label for="email">Email Address</label>
                <div class="input-wrap">
                    <i class="fas fa-envelope icon"></i>
                    <input type="email" id="email" name="email" placeholder="your@email.com"
                           value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>" required>
                </div>
            </div>
            <div class="form-group">
                <label for="password">Password</label>
                <div class="input-wrap">
                    <i class="fas fa-lock icon"></i>
                    <input type="password" id="password" name="password" placeholder="Enter your password" required>
                    <button type="button" class="toggle-pw" onclick="togglePw()" aria-label="Toggle password">
                        <i class="fas fa-eye" id="pwIcon"></i>
                    </button>
                </div>
            </div>
            <div class="form-options">
                <label><input type="checkbox" name="remember"> Remember me</label>
                <a href="forgotpassword.php">Forgot password?</a>
            </div>
            <button type="submit" name="login" class="btn-auth">
                <i class="fas fa-sign-in-alt"></i> Login
            </button>
            <div class="new-here-box">
            <span>Don't have an account yet?</span>
            <br><br>
            <a href="Sign Up.php">Create Account</a>
        </div> 
        </form>

    </div>
</div>

<script>
document.getElementById('mainNav').classList.toggle('scrolled', window.scrollY > 10);
window.addEventListener('scroll', () => document.getElementById('mainNav').classList.toggle('scrolled', window.scrollY > 10));
const ham = document.getElementById('hamburger');
const navLinks = document.getElementById('navLinks');
ham.addEventListener('click', () => navLinks.classList.toggle('open'));

function togglePw(){
    const pw = document.getElementById('password');
    const ic = document.getElementById('pwIcon');
    if(pw.type === 'password'){ pw.type = 'text'; ic.className = 'fas fa-eye-slash'; }
    else { pw.type = 'password'; ic.className = 'fas fa-eye'; }
}
</script>
</body>
</html>