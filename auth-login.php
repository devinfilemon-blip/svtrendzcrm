<?php

session_start();
require_once "layouts/config.php";
$crmForceLightTheme = true;
?>
<?php include 'layouts/head-main.php';

?>
<head>
    <title><?php echo APP_NAME; ?> — Sign In</title>
    <?php include 'layouts/head.php'; ?>
    <?php include 'layouts/head-style.php'; ?>

    <style>
        .aicrm-login-page *,
        .aicrm-login-page *::before,
        .aicrm-login-page *::after{ box-sizing: border-box; }

        .auth-logo .auth-logo-dark { display: none; }

        /* Keep login inputs light even if browser dark color-scheme leaks in */
        body.crm-auth-page,
        body.crm-auth-page.crm-dark {
            color-scheme: light;
        }

        /* ============ AICRM-style split login ============ */
        :root{
            --aicrm-ink:#241b45;
            --aicrm-muted:#8b87a3;
            --aicrm-field-bg:#f2f1f8;
            --aicrm-purple-1:#2c1668;
            --aicrm-purple-2:#3c1f92;
            --aicrm-purple-3:#5330c9;
            --aicrm-blue-1:#4d7bff;
            --aicrm-blue-2:#6a90ff;
            --aicrm-mint-1:#7fe9d8;
            --aicrm-mint-2:#59d2c4;
        }

        body.aicrm-auth-body{
            margin:0;
            background:
                radial-gradient(circle at 85% 10%, rgba(255,255,255,0.08) 0%, transparent 45%),
                radial-gradient(circle at 10% 90%, rgba(255,255,255,0.06) 0%, transparent 40%),
                linear-gradient(135deg, var(--aicrm-purple-1) 0%, var(--aicrm-purple-2) 55%, var(--aicrm-purple-3) 100%) !important;
        }

        .aicrm-login-page{
            min-height:100vh;
            display:flex;
            align-items:center;
            justify-content:center;
            padding:32px 20px;
        }

        .aicrm-shell{
            width:100%;
            max-width:900px;
            display:flex;
            background:#ffffff;
            border-radius:28px;
            overflow:hidden;
            box-shadow:0 30px 70px rgba(20,10,60,0.35);
        }

        /* ---- Left illustration panel ---- */
        .aicrm-visual{
            position:relative;
            flex:0 0 42%;
            display:flex;
            align-items:center;
            justify-content:center;
            padding:40px 24px;
            background:
                radial-gradient(circle at 30% 20%, rgba(255,255,255,0.10) 0%, transparent 45%),
                linear-gradient(160deg, var(--aicrm-purple-2) 0%, var(--aicrm-purple-1) 100%);
            overflow:hidden;
        }
        .aicrm-visual::before,
        .aicrm-visual::after{
            content:"";
            position:absolute;
            border-radius:50%;
            background:rgba(255,255,255,0.06);
        }
        .aicrm-visual::before{ width:260px; height:260px; top:-90px; left:-80px; }
        .aicrm-visual::after{ width:180px; height:180px; bottom:-70px; right:-60px; background:rgba(255,255,255,0.05); }

        .aicrm-robot-wrap{ position:relative; width:100%; max-width:280px; }
        .aicrm-robot-wrap svg{ width:100%; height:auto; display:block; }

        /* ---- Right form panel ---- */
        .aicrm-form-panel{
            flex:1 1 auto;
            padding:44px 48px 36px;
            display:flex;
            flex-direction:column;
        }

        .aicrm-brand-row{
            display:flex;
            align-items:center;
            justify-content:flex-end;
            gap:10px;
            margin-bottom:34px;
        }
        .aicrm-brand-row img{ width:72px; height:72px; object-fit:contain; }
        .aicrm-brand-row .aicrm-wordmark{
            font-size:34px;
            font-weight:700;
            letter-spacing:0.2px;
            color:var(--aicrm-ink);
        }
        .aicrm-brand-row .aicrm-wordmark span{ color:var(--aicrm-blue-1); }

        .aicrm-form-panel form{ margin-top:4px; }

        .aicrm-field{ margin-bottom:18px; }
        .aicrm-field-label-row{
            display:flex;
            align-items:center;
            justify-content:space-between;
            margin-bottom:8px;
        }
        .aicrm-label{
            font-size:13.5px;
            font-weight:600;
            color:var(--aicrm-ink);
            margin:0;
        }
        .aicrm-forgot{
            font-size:13px;
            font-weight:600;
            color:var(--aicrm-blue-1);
            text-decoration:none;
            cursor:pointer;
        }
        .aicrm-forgot:hover{ text-decoration:underline; color:var(--aicrm-blue-1); }

        .aicrm-input{
            width:100%;
            border:1px solid transparent;
            background:var(--aicrm-field-bg);
            border-radius:12px;
            padding:12px 16px;
            font-size:14.5px;
            color:var(--aicrm-ink);
            outline:none;
            transition:border-color .15s ease, background .15s ease;
        }
        .aicrm-input::placeholder{ color:#a29fbb; }
        .aicrm-input:focus{
            background:#fff;
            border-color:var(--aicrm-blue-1);
            box-shadow:0 0 0 3px rgba(77,123,255,0.12);
        }

        .aicrm-remember{
            display:flex;
            align-items:center;
            gap:8px;
            margin:4px 0 22px;
        }
        .aicrm-remember input{
            width:16px; height:16px;
            accent-color:var(--aicrm-blue-1);
            cursor:pointer;
        }
        .aicrm-remember label{
            font-size:13.5px;
            color:#5c5876;
            margin:0;
            cursor:pointer;
        }

        .aicrm-submit{
            width:100%;
            border:none;
            border-radius:14px;
            padding:13px 18px;
            font-size:15.5px;
            font-weight:700;
            color:#fff;
            letter-spacing:0.2px;
            background:linear-gradient(135deg, var(--aicrm-blue-1) 0%, var(--aicrm-blue-2) 100%);
            box-shadow:0 12px 24px rgba(77,123,255,0.35);
            cursor:pointer;
            transition:transform .12s ease, box-shadow .12s ease, opacity .12s ease;
        }
        .aicrm-submit:hover{ transform:translateY(-1px); box-shadow:0 16px 28px rgba(77,123,255,0.4); }
        .aicrm-submit:disabled{ opacity:0.7; cursor:default; transform:none; }

        .aicrm-error{
            display:none;
            font-size:13px;
            font-weight:600;
            color:#e0355b;
            background:rgba(224,53,91,0.08);
            border-radius:10px;
            padding:10px 12px;
            margin-bottom:16px;
        }

        .aicrm-footer{
            margin-top:auto;
            padding-top:26px;
            text-align:center;
            font-size:12.5px;
            color:var(--aicrm-muted);
        }

        @media (max-width: 820px){
            .aicrm-visual{ display:none; }
            .aicrm-shell{ max-width:420px; border-radius:24px; }
            .aicrm-form-panel{ padding:36px 28px 28px; }
            .aicrm-brand-row{ justify-content:center; }
        }
    </style>
</head>

<body class="crm-theme crm-auth-page aicrm-auth-body">
<script>
(function () {
    try {
        document.documentElement.classList.remove('crm-dark', 'crm-dark-preload');
        document.documentElement.style.colorScheme = 'light';
        document.documentElement.style.backgroundColor = '';
        document.body.classList.remove('crm-dark');
    } catch (e) {}
})();
</script>

    <div class="aicrm-login-page">
        <div class="aicrm-shell">

            <div class="aicrm-visual">
                <div class="aicrm-robot-wrap">
                    <svg viewBox="0 0 320 320" xmlns="http://www.w3.org/2000/svg" role="img" aria-label="Friendly robot assistant">
                        <ellipse cx="170" cy="272" rx="78" ry="14" fill="rgba(0,0,0,0.18)"/>

                        <!-- speech bubble -->
                        <path d="M18 46 h150 a20 20 0 0 1 20 20 v58 a20 20 0 0 1 -20 20 h-96 l-26 26 v-26 h-28 a20 20 0 0 1 -20 -20 v-58 a20 20 0 0 1 20 -20 z"
                              fill="url(#mintGrad)"/>
                        <rect x="42" y="80" width="98" height="14" rx="7" fill="#ffffff" opacity="0.95"/>
                        <rect x="42" y="104" width="66" height="14" rx="7" fill="#ffffff" opacity="0.85"/>

                        <!-- antenna -->
                        <line x1="205" y1="70" x2="205" y2="96" stroke="#ffffff" stroke-width="5" stroke-linecap="round"/>
                        <circle cx="205" cy="60" r="9" fill="#ffffff"/>

                        <!-- ears -->
                        <rect x="118" y="150" width="16" height="34" rx="8" fill="#ffffff"/>
                        <rect x="252" y="150" width="16" height="34" rx="8" fill="#ffffff"/>

                        <!-- head -->
                        <rect x="132" y="96" width="140" height="112" rx="34" fill="#ffffff"/>
                        <rect x="152" y="122" width="100" height="62" rx="24" fill="#2f6df6"/>
                        <circle cx="184" cy="153" r="9" fill="#ffffff"/>
                        <circle cx="222" cy="153" r="9" fill="#ffffff"/>
                        <rect x="180" y="170" width="46" height="7" rx="3.5" fill="#9cc0ff" opacity="0.8"/>

                        <!-- body -->
                        <path d="M150 208 h104 a24 24 0 0 1 24 24 v18 a30 30 0 0 1 -30 30 h-92 a30 30 0 0 1 -30 -30 v-18 a24 24 0 0 1 24 -24 z" fill="#ffffff"/>
                        <circle cx="202" cy="238" r="12" fill="#e8ecf9"/>
                        <circle cx="202" cy="238" r="6" fill="#2f6df6"/>

                        <!-- feet -->
                        <rect x="158" y="256" width="30" height="16" rx="8" fill="#ffffff"/>
                        <rect x="216" y="256" width="30" height="16" rx="8" fill="#ffffff"/>

                        <defs>
                            <linearGradient id="mintGrad" x1="18" y1="46" x2="188" y2="144" gradientUnits="userSpaceOnUse">
                                <stop offset="0" stop-color="#7fe9d8"/>
                                <stop offset="1" stop-color="#4fd2c0"/>
                            </linearGradient>
                        </defs>
                    </svg>
                </div>
            </div>

            <div class="aicrm-form-panel">
                <div class="aicrm-brand-row">
                    <img src="<?php echo APP_LOGO; ?>" alt="<?php echo APP_NAME; ?> logo">
                    <span class="aicrm-wordmark">infi<span>CRM</span></span>
                </div>

                <div id="aicrmLoginError" class="aicrm-error"></div>

                <form id="loginForm" action="auth-login.php" onsubmit="loginUser(event);" autocomplete="off">
                    <div class="aicrm-field">
                        <div class="aicrm-field-label-row">
                            <label for="phone" class="aicrm-label">ID</label>
                        </div>
                        <input type="text" class="aicrm-input" id="phone" name="phone" placeholder="Enter your ID" autocomplete="username">
                    </div>

                    <div class="aicrm-field">
                        <div class="aicrm-field-label-row">
                            <label for="password" class="aicrm-label">Password</label>
                            <a href="javascript:void(0);" class="aicrm-forgot" onclick="alert('Please contact your administrator to reset your password.');">Forgot Password?</a>
                        </div>
                        <input type="password" class="aicrm-input" id="password" name="password" placeholder="Enter your password" autocomplete="current-password">
                    </div>

                    <div class="aicrm-remember">
                        <input type="checkbox" id="rememberMe">
                        <label for="rememberMe">Remember Me</label>
                    </div>

                    <button type="submit" id="loginSubmitBtn" class="aicrm-submit">Sign In</button>
                </form>

                <div class="aicrm-footer">
                    &copy; <script>document.write(new Date().getFullYear())</script> <?php echo APP_NAME; ?>. All rights reserved.
                </div>
            </div>

        </div>
    </div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
(function () {
    try {
        var savedUser = localStorage.getItem('aicrm_remember_username');
        if (savedUser) {
            document.getElementById('phone').value = savedUser;
            document.getElementById('rememberMe').checked = true;
        }
    } catch (e) {}
})();

function loginUser(event) {
    event.preventDefault();

    var phone = document.getElementById("phone").value.trim();
    var password = document.getElementById("password").value;
    var errorBox = document.getElementById("aicrmLoginError");
    var submitBtn = document.getElementById("loginSubmitBtn");

    errorBox.style.display = "none";
    errorBox.textContent = "";

    if (phone == "" || password == "") {
        errorBox.textContent = "Please enter both username and password.";
        errorBox.style.display = "block";
        return false;
    }

    try {
        if (document.getElementById('rememberMe').checked) {
            localStorage.setItem('aicrm_remember_username', phone);
        } else {
            localStorage.removeItem('aicrm_remember_username');
        }
    } catch (e) {}

    submitBtn.disabled = true;
    submitBtn.textContent = "Signing in...";

    const data = {
        action: "loginUser",
        phone: phone,
        password: password
    };

    fetch('api.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        credentials: 'same-origin',
        body: JSON.stringify(data)
    })
    .then(async function(response) {
        var text = await response.text();
        try {
            return JSON.parse(text);
        } catch (e) {
            console.error('Login raw response:', text);
            throw new Error('Invalid server response');
        }
    })
    .then(responseData => {
        if (responseData.status === "success") {
            var role = responseData.data && responseData.data.role;
            window.location.href = (role === 'Client') ? 'client-dashboard.php' : 'index.php';
        } else {
            errorBox.textContent = responseData.message || 'Login failed';
            errorBox.style.display = "block";
            submitBtn.disabled = false;
            submitBtn.textContent = "Sign In";
        }
    })
    .catch(error => {
        console.error('Error:', error);
        errorBox.textContent = "An error occurred. Please try again.";
        errorBox.style.display = "block";
        submitBtn.disabled = false;
        submitBtn.textContent = "Sign In";
    });
}
</script>

</body>
</html>
