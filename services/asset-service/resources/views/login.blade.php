<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>GPE - Connexion</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light d-flex align-items-center" style="min-height:100vh">
<div class="container" style="max-width:420px">
    <div class="card shadow">
        <div class="card-body p-4">
            <h4 class="mb-1">Gestion du Patrimoine</h4>
            <p class="text-muted small mb-4">Connectez-vous pour acceder a la plateforme.</p>
            <div id="error" class="alert alert-danger d-none"></div>
            <form id="loginForm">
                <div class="mb-3">
                    <label class="form-label">Email</label>
                    <input type="email" class="form-control" id="email" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Mot de passe</label>
                    <input type="password" class="form-control" id="password" required>
                </div>
                <button class="btn btn-primary w-100" type="submit">Se connecter</button>
            </form>
        </div>
    </div>
</div>
<script>
document.getElementById('loginForm').addEventListener('submit', async (e) => {
    e.preventDefault();
    const err = document.getElementById('error');
    err.classList.add('d-none');
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
        err.classList.remove('d-none');
        return;
    }
    localStorage.setItem('gpe_token', data.token);
    localStorage.setItem('gpe_user', JSON.stringify(data.user));
    window.location.href = '/';
});
if (localStorage.getItem('gpe_token')) window.location.href = '/';
</script>
</body>
</html>
