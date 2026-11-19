<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>GPE — Tableau de bord</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        * { margin:0; padding:0; box-sizing:border-box; font-family:'Inter',sans-serif; }
        body { background:#f4f6fa; color:#1f2d3d; display:flex; min-height:100vh; }

        /* Sidebar */
        .sidebar {
            width:230px; background:#1e3a5f; color:#c8d8ec; display:flex;
            flex-direction:column; position:fixed; top:0; bottom:0; left:0; z-index:10;
        }
        .sidebar .brand { display:flex; align-items:center; gap:10px; padding:20px 18px; color:#fff; }
        .sidebar .brand .mark {
            width:34px; height:34px; border-radius:8px; background:#14b8a6;
            display:flex; align-items:center; justify-content:center; font-weight:800;
        }
        .sidebar nav { padding:10px 12px; flex:1; }
        .sidebar .nav-label { font-size:10.5px; text-transform:uppercase; letter-spacing:.08em; color:#6f86a8; padding:14px 10px 6px; }
        .sidebar a {
            display:flex; align-items:center; gap:11px; padding:10px 12px; margin:2px 0;
            border-radius:9px; color:#c8d8ec; text-decoration:none; font-size:13.5px; font-weight:500;
        }
        .sidebar a:hover { background:rgba(255,255,255,.07); color:#fff; }
        .sidebar a.active { background:#14b8a6; color:#fff; }
        .sidebar a .ico { width:18px; text-align:center; }
        .sidebar .logout { margin:12px; padding:10px; border-radius:9px; background:rgba(255,255,255,.06);
            color:#e8b4b4; border:none; cursor:pointer; font-size:13px; font-weight:600; width:calc(100% - 24px); }
        .sidebar .logout:hover { background:rgba(255,255,255,.12); color:#fff; }

        /* Main */
        .main { margin-left:230px; flex:1; padding:28px 32px; min-width:0; }
        .topbar { display:flex; justify-content:space-between; align-items:center; margin-bottom:24px; }
        .topbar h1 { font-size:22px; font-weight:700; color:#1e3a5f; }
        .topbar .user { font-size:13px; color:#64748b; }
        .topbar .user b { color:#1e3a5f; }

        .kpis { display:grid; grid-template-columns:repeat(4,1fr); gap:16px; margin-bottom:24px; }
        .kpi { background:#fff; border-radius:14px; padding:18px 20px; box-shadow:0 1px 4px rgba(30,58,95,.07); }
        .kpi .lbl { font-size:12px; color:#7a8aa0; font-weight:600; text-transform:uppercase; letter-spacing:.04em; }
        .kpi .val { font-size:24px; font-weight:800; color:#1e3a5f; margin-top:6px; }
        .kpi .sub { font-size:11.5px; color:#94a3b8; margin-top:4px; }
        .kpi .icon { float:right; width:38px; height:38px; border-radius:10px; display:flex;
            align-items:center; justify-content:center; font-size:17px; }
        .ic-blue { background:#e8f0fe; } .ic-teal { background:#e0f7f4; }
        .ic-amber { background:#fef3e2; } .ic-slate { background:#eef1f6; }

        .panel { background:#fff; border-radius:14px; box-shadow:0 1px 4px rgba(30,58,95,.07); overflow:hidden; }
        .panel-head { display:flex; justify-content:space-between; align-items:center; padding:16px 20px; border-bottom:1px solid #eef1f6; flex-wrap:wrap; gap:10px; }
        .panel-head h2 { font-size:15px; font-weight:700; color:#1e3a5f; }
        .filters { display:flex; gap:10px; }
        .filters input, .filters select {
            padding:8px 12px; border:1.5px solid #e2e8f0; border-radius:8px; font-size:13px;
            font-family:'Inter',sans-serif; outline:none; background:#fff;
        }
        .filters input:focus, .filters select:focus { border-color:#14b8a6; }
        .filters input { width:220px; }
        table { width:100%; border-collapse:collapse; font-size:13px; }
        thead th {
            text-align:left; padding:11px 16px; font-size:11px; text-transform:uppercase;
            letter-spacing:.05em; color:#7a8aa0; background:#f8fafc; border-bottom:1px solid #eef1f6;
        }
        tbody td { padding:12px 16px; border-bottom:1px solid #f1f4f8; vertical-align:middle; }
        tbody tr:hover { background:#f8fafc; }
        .badge { display:inline-block; padding:4px 10px; border-radius:20px; font-size:11px; font-weight:600; }
        .b-en_service { background:#dcfce7; color:#15803d; }
        .b-maintenance { background:#fef3c7; color:#b45309; }
        .b-reforme { background:#e2e8f0; color:#475569; }
        .b-cession { background:#dbeafe; color:#1d4ed8; }
        .b-sorti { background:#fee2e2; color:#b91c1c; }
        .empty { text-align:center; color:#94a3b8; padding:40px !important; }
        @media (max-width:900px) {
            .sidebar { width:64px; } .sidebar .brand span, .sidebar nav a span, .sidebar .nav-label, .sidebar .logout { display:none; }
            .main { margin-left:64px; padding:20px; }
            .kpis { grid-template-columns:repeat(2,1fr); }
        }
    </style>
</head>
<body>
<aside class="sidebar">
    <div class="brand"><div class="mark">G</div><span style="font-weight:700">GPE</span></div>
    <nav>
        <div class="nav-label">Principal</div>
        <a href="/" class="active"><span class="ico">▦</span><span>Tableau de bord</span></a>
        <a href="#"><span class="ico">◈</span><span>Actifs</span></a>
        <a href="#"><span class="ico">⚒</span><span>Maintenances</span></a>
        <a href="#"><span class="ico">▤</span><span>Rapports</span></a>
        <div class="nav-label">Administration</div>
        <a href="#"><span class="ico">☰</span><span>Utilisateurs</span></a>
        <a href="#"><span class="ico">⚙</span><span>Paramètres</span></a>
    </nav>
    <button class="logout" onclick="logout()">⏻ Déconnexion</button>
</aside>

<div class="main">
    <div class="topbar">
        <h1>Tableau de bord</h1>
        <div class="user">Connecté : <b id="userName"></b></div>
    </div>

    <div class="kpis" id="stats"></div>

    <div class="panel">
        <div class="panel-head">
            <h2>Actifs du patrimoine</h2>
            <div class="filters">
                <input id="search" placeholder="Rechercher (désignation, n° série)…">
                <select id="statutFilter">
                    <option value="">Tous les statuts</option>
                    <option value="en_service">En service</option>
                    <option value="maintenance">Maintenance</option>
                    <option value="reforme">Réforme</option>
                    <option value="cession">Cession</option>
                    <option value="sorti">Sorti</option>
                </select>
            </div>
        </div>
        <div style="overflow-x:auto">
            <table>
                <thead>
                    <tr>
                        <th>N° série</th><th>Désignation</th><th>Catégorie</th><th>Acquisition</th>
                        <th>Valeur actuelle</th><th>État</th><th>Statut</th><th>Localisation</th>
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
const etatBadge = e => ({neuf:'b-en_service',bon:'b-en_service',use:'b-maintenance',hors_service:'b-sorti'}[e] || 'b-sorti');

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
        ['Total actifs', r.total_actifs, '◈', 'ic-blue', 'biens enregistrés'],
        ['Valeur totale', fmt(r.valeur_totale) + ' FCFA', '▤', 'ic-teal', 'patrimoine estimé'],
        ['Valeur acquisition', fmt(r.valeur_acquisition_totale) + ' FCFA', '⇄', 'ic-slate', 'coût d\'origine'],
        ['Affectations', r.affectations_en_cours, '⇄', 'ic-amber', 'biens affectés'],
    ];
    document.getElementById('stats').innerHTML = cards.map(([t, v, i, c, s]) =>
        `<div class="kpi"><div class="icon ${c}">${i}</div>
         <div class="lbl">${t}</div><div class="val">${v}</div><div class="sub">${s}</div></div>`).join('');
}

async function loadActifs() {
    const params = new URLSearchParams();
    if (document.getElementById('search').value) params.set('search', document.getElementById('search').value);
    if (document.getElementById('statutFilter').value) params.set('statut', document.getElementById('statutFilter').value);
    const data = await api('/api/v1/assets/actifs?' + params);
    const rows = (data.data || []).map(a => `
        <tr>
            <td><b>${a.numero_serie}</b></td>
            <td>${a.designation}</td>
            <td>${a.categorie?.nom || '-'}</td>
            <td>${(a.date_acquisition || '-').slice(0,10)}</td>
            <td><b>${fmt(a.valeur_actuelle)}</b></td>
            <td><span class="badge ${etatBadge(a.etat)}">${a.etat}</span></td>
            <td><span class="badge b-${a.statut}">${a.statut.replace('_',' ')}</span></td>
            <td>${a.localisation ? a.localisation.region : '-'}</td>
        </tr>`).join('');
    document.getElementById('actifsBody').innerHTML = rows ||
        '<tr><td colspan="8" class="empty">Aucun actif trouvé</td></tr>';
}

document.getElementById('search').addEventListener('input', loadActifs);
document.getElementById('statutFilter').addEventListener('change', loadActifs);

loadStats();
loadActifs();
</script>
</body>
</html>
