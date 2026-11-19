@include('partials.layout', ['title' => 'Tableau de bord', 'active' => 'dashboard'])
    <div class="topbar">
        <h1>Tableau de bord</h1>
        <div class="user">Connecté : <b id="userName"></b></div>
    </div>

    <div class="kpis" id="stats"></div>

    <div class="panel">
        <div class="panel-head">
            <h2>Actifs du patrimoine <a href="/actifs" style="font-size:12px;font-weight:500;color:#14b8a6;text-decoration:none;margin-left:8px">Voir tout →</a></h2>
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

@include('partials.scripts')
<script>
const etatBadge = e => ({neuf:'b-en_service',bon:'b-en_service',use:'b-maintenance',hors_service:'b-sorti'}[e] || 'b-sorti');

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
        <tr style="cursor:pointer" onclick="location.href='/actifs/${a.id}'">
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
