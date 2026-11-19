@include('partials.layout', ['title' => 'Rapports', 'active' => 'rapports'])
    <div class="topbar">
        <h1>Rapports patrimoniaux</h1>
        <div class="user">Connecté : <b id="userName"></b></div>
    </div>

    <div id="alertBox"></div>
    <div class="kpis" id="kpis"></div>

    <div class="two-col">
        <div class="panel">
            <div class="panel-head"><h2>Par statut</h2></div>
            <table>
                <thead><tr><th>Statut</th><th>Total</th><th>Valeur (FCFA)</th></tr></thead>
                <tbody id="parStatut"></tbody>
            </table>
        </div>
        <div class="panel">
            <div class="panel-head"><h2>Par catégorie</h2></div>
            <table>
                <thead><tr><th>Catégorie</th><th>Actifs</th><th>Valeur (FCFA)</th></tr></thead>
                <tbody id="parCategorie"></tbody>
            </table>
        </div>
    </div>

    <div class="panel">
        <div class="panel-head"><h2>Actifs dormants <span id="dormantCount" class="badge b-maintenance"></span></h2></div>
        <div style="overflow-x:auto">
            <table>
                <thead><tr><th>N° série</th><th>Désignation</th><th>Catégorie</th><th>Acquisition</th><th>Valeur</th></tr></thead>
                <tbody id="dormants"></tbody>
            </table>
        </div>
    </div>
</div>

@include('partials.scripts')
<script>
async function load() {
    const r = await api('/api/v1/assets/rapports/patrimoine');
    document.getElementById('kpis').innerHTML = [
        ['Total actifs', r.total_actifs, '◈', 'ic-blue'],
        ['Valeur totale', fmt(r.valeur_totale) + ' FCFA', '▤', 'ic-teal'],
        ['Valeur acquisition', fmt(r.valeur_acquisition_totale) + ' FCFA', '⇄', 'ic-slate'],
        ['Affectations en cours', r.affectations_en_cours, '⇄', 'ic-amber'],
    ].map(([t,v,i,c]) => `<div class="kpi"><div class="icon ${c}">${i}</div>
        <div class="lbl">${t}</div><div class="val">${v}</div></div>`).join('');

    document.getElementById('parStatut').innerHTML = (r.par_statut || []).map(s => `
        <tr><td><span class="badge b-${s.statut}">${s.statut.replace('_',' ')}</span></td>
        <td>${s.total}</td><td>${fmt(s.valeur)}</td></tr>`).join('')
        || '<tr><td colspan="3" class="empty">Aucune donnée</td></tr>';

    document.getElementById('parCategorie').innerHTML = (r.par_categorie || []).map(c => `
        <tr><td>${c.nom}</td><td>${c.actifs_count ?? c.actifs?.length ?? '-'}</td>
        <td>${fmt(c.actifs_sum_valeur_actuelle ?? c.valeur ?? 0)}</td></tr>`).join('')
        || '<tr><td colspan="3" class="empty">Aucune donnée</td></tr>';

    const d = await api('/api/v1/assets/rapports/actifs-dormants');
    document.getElementById('dormantCount').textContent = d.count || 0;
    document.getElementById('dormants').innerHTML = (d.actifs || []).map(a => `
        <tr><td><b>${a.numero_serie}</b></td><td>${a.designation}</td>
        <td>${a.categorie?.nom || '-'}</td><td>${(a.date_acquisition||'-').slice(0,10)}</td>
        <td>${fmt(a.valeur_actuelle)}</td></tr>`).join('')
        || '<tr><td colspan="5" class="empty">Aucun actif dormant</td></tr>';
}
load().catch(e => showAlert('alertBox', 'Erreur: ' + e.message));
</script>
</body>
</html>
