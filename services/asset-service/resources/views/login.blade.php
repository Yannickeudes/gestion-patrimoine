<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>GPE — Connexion</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        * { margin:0; padding:0; box-sizing:border-box; font-family:'Inter',sans-serif; }
        body { min-height:100vh; display:flex; }
        .brand {
            flex:1; background:linear-gradient(160deg,#1e3a5f 0%,#16304f 60%,#0f2540 100%);
            color:#fff; display:flex; flex-direction:column; justify-content:center;
            padding:60px; position:relative; overflow:hidden;
        }
        .brand::after {
            content:''; position:absolute; right:-120px; top:-120px; width:380px; height:380px;
            border-radius:50%; background:rgba(20,184,166,.12);
        }
        .brand .logo { display:flex; align-items:center; gap:12px; margin-bottom:48px; }
        .brand .logo .mark {
            width:42px; height:42px; border-radius:10px; background:#14b8a6;
            display:flex; align-items:center; justify-content:center; font-weight:800; font-size:18px;
        }
        .brand h1 { font-size:34px; font-weight:800; line-height:1.2; margin-bottom:14px; }
        .brand h1 span { color:#14b8a6; }
        .brand p { color:#9db4d0; font-size:15px; max-width:380px; line-height:1.6; }
        .brand .chips { margin-top:40px; display:flex; gap:10px; flex-wrap:wrap; }
        .brand .chip {
            background:rgba(255,255,255,.07); border:1px solid rgba(255,255,255,.12);
            padding:8px 14px; border-radius:20px; font-size:12.5px; color:#c8d8ec;
        }
        .form-side { width:520px; display:flex; align-items:center; justify-content:center; background:#f4f6fa; padding:40px; }
        .card-login {
            background:#fff; border-radius:16px; padding:40px; width:100%; max-width:380px;
            box-shadow:0 10px 40px rgba(30,58,95,.10);
        }
        .card-login h2 { font-size:22px; font-weight:700; color:#1e3a5f; margin-bottom:6px; }
        .card-login .sub { color:#7a8aa0; font-size:13.5px; margin-bottom:26px; }
        label { display:block; font-size:12.5px; font-weight:600; color:#4a5a70; margin-bottom:6px; }
        input {
            width:100%; padding:11px 14px; border:1.5px solid #e2e8f0; border-radius:9px;
            font-size:14px; margin-bottom:16px; outline:none; transition:border .15s;
        }
        input:focus { border-color:#14b8a6; }
        button {
            width:100%; padding:12px; border:none; border-radius:9px; cursor:pointer;
            background:#1e3a5f; color:#fff; font-size:14.5px; font-weight:600; transition:background .15s;
        }
        button:hover { background:#14b8a6; }
        #error {
            display:none; background:#fef2f2; color:#b91c1c; border:1px solid #fecaca;
            padding:10px 14px; border-radius:9px; font-size:13px; margin-bottom:16px;
        }
        .demo-hint { margin-top:22px; font-size:12px; color:#94a3b8; text-align:center; line-height:1.6; }
        .demo-hint code { background:#eef2f7; padding:2px 7px; border-radius:5px; color:#1e3a5f; }
        @media (max-width:860px) { .brand { display:none; } .form-side { width:100%; } }
    </style>
</head>
<body>
<div class="brand">
    <div class="logo"><div class="mark">G</div><div style="font-weight:700;font-size:17px">GPE</div></div>
    <h1>Gestion du<br><span>Patrimoine de l'État</span></h1>
    <p>Inventaire, affectations, évaluations et maintenance des biens de l'État — une vision consolidée du patrimoine national.</p>
    <div class="chips">
        <div class="chip">Immobilier</div>
        <div class="chip">Véhicules</div>
        <div class="chip">Informatique</div>
        <div class="chip">Mobilier</div>
    </div>
</div>
<div class="form-side">
    <div class="card-login">
        <h2>Connexion</h2>
        <p class="sub">Accédez à la plateforme GPE</p>
        <div id="error"></div>
        <form id="loginForm">
            <label for="email">Adresse email</label>
            <input type="email" id="email" placeholder="admin@gpe.local" required>
            <label for="password">Mot de passe</label>
            <input type="password" id="password" placeholder="••••••••" required>
            <button type="submit">Se connecter</button>
        </form>
        <p class="demo-hint">Compte démo : <code>admin@gpe.local</code> / <code>password</code></p>
    </div>
</div>
<script>
document.getElementById('loginForm').addEventListener('submit', async (e) => {
    e.preventDefault();
    const err = document.getElementById('error');
    err.style.display = 'none';
    try {
        const res = await fetch('/api/v1/auth/login', {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify({
                email: document.getElementById('email').value,
                password: document.getElementById('password').value,
            }),
        });
        const data = await res.json();
        if (!res.ok) {
            err.textContent = data.message || 'Identifiants incorrects.';
            err.style.display = 'block';
            return;
        }
        localStorage.setItem('gpe_token', data.token);
        localStorage.setItem('gpe_user', JSON.stringify(data.user));
        window.location.href = '/';
    } catch (ex) {
        err.textContent = 'Service indisponible, réessayez.';
        err.style.display = 'block';
    }
});
if (localStorage.getItem('gpe_token')) window.location.href = '/';
</script>
</body>
</html>
