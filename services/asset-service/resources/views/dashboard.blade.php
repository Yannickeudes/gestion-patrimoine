<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>GPE - Tableau de bord</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<nav class="navbar navbar-dark bg-dark">
    <div class="container-fluid">
        <span class="navbar-brand">Gestion du Patrimoine de l'Etat</span>
        <div>
            <span class="text-light me-3" id="userName"></span>
            <button class="btn btn-outline-light btn-sm" onclick="logout()">Deconnexion</button>
        </div>
    </div>
</nav>

<div class="container-fluid py-4">
    <h5 class="mb-3">Tableau de bord</h5>
    <div class="row g-3 mb-4" id="stats"></div>

    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <strong>Actifs du patrimoine</strong>
            <div>
                <input id="search" class="form-control form-control-sm d-inline-block" style="width:220px" placeholder="Rechercher...">
                <select id="statutFilter" class="form-select form-select-sm d-inline-block" style="width:160px">
                    <option value="">Tous les statuts</option>
                    <option value="en_service">En service</option>
                    <option value="maintenance">Maintenance</option>
                    <option value="reforme">Reforme</option>
                    <option value="cession">Cession</option>
                    <option value="sorti">Sorti</option>
                </select>
            </div>
        </div>
        <div class="card-body p-0">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th>N serie</th><th>Designation</th><th>Categorie</th><th>Marque</th>
                        <th>Acquisition</th><th>Valeur act.</th><th>Etat</th><th>Statut</th><th>Localisation</th>
                    </tr>
                </thead>
                <tbody id="actifsBody"></tbody>
            </table>
        </div>
    </div>
</div>

<script>
const token = localStorage.getItem('gpe_token');
if (!token) window.location.href = '/login';
const user = JSON.parse(localStorage.getItem('gpe_user') || '{}');
document.getElementById('userName').textContent = user.name || '';

const headers = {'Authorization': 'Bearer ' + token, 'Content-Type': 'application/json'};
const fmt = n => new Intl.NumberFormat('fr-FR').format(n || 0);

async function api(path) {
    const res = await fetch(path, {headers});
    if (res.status === 401) { logout(); throw new Error('unauthorized'); }
    return res.json();
}

function logout() {
    fetch('/api/v1/auth/logout', {method: 'POST', headers});
    localStorage.clear();
    window.location.href = '/login';
}

async function loadStats() {
    const r = await api('/api/v1/assets/rapports/patrimoine');
    const cards = [
        ['Total actifs', r.total_actifs],
        ['Valeur totale (FCFA)', fmt(r.valeur_totale)],
        ['Valeur acquisition', fmt(r.valeur_acquisition_totale)],
        ['Affectations en cours', r.affectations_en_cours],
    ];
    document.getElementById('stats').innerHTML = cards.map(([t, v]) =>
        `<div class="col-md-3"><div class="card text-center"><div class="card-body py-3">
         <div class="fs-4 fw-bold">${v}</div><div class="text-muted small">${t}</div></div></div></div>`).join('');
}

async function loadActifs() {
    const params = new URLSearchParams();
    if (document.getElementById('search').value) params.set('search', document.getElementById('search').value);
    if (document.getElementById('statutFilter').value) params.set('statut', document.getElementById('statutFilter').value);
    const data = await api('/api/v1/assets/actifs?' + params);
    document.getElementById('actifsBody').innerHTML = (data.data || []).map(a => `
        <tr>
            <td>${a.numero_serie}</td>
            <td>${a.designation}</td>
            <td>${a.categorie?.nom || '-'}</td>
            <td>${a.marque || '-'}</td>
            <td>${a.date_acquisition || '-'}</td>
            <td>${fmt(a.valeur_actuelle)}</td>
            <td>${a.etat}</td>
            <td><span class="badge bg-secondary">${a.statut}</span></td>
            <td>${a.localisation ? a.localisation.region + ' / ' + (a.localisation.commune || '') : '-'}</td>
        </tr>`).join('') || '<tr><td colspan="9" class="text-center text-muted py-4">Aucun actif</td></tr>';
}

document.getElementById('search').addEventListener('input', loadActifs);
document.getElementById('statutFilter').addEventListener('change', loadActifs);

loadStats();
loadActifs();
</script>
</body>
</html>
