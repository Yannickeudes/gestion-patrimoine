@include('partials.layout', ['title' => 'Fiche actif', 'active' => 'actifs'])
    <div class="topbar">
        <h1 id="title">Fiche actif</h1>
        <div class="user">Connecté : <b id="userName"></b></div>
    </div>

    <div id="alertBox"></div>
    <div id="content" style="display:none">

        <!-- En-tete -->
        <div class="panel" style="padding:24px; margin-bottom:24px;">
            <div style="display:flex; justify-content:space-between; align-items:flex-start; flex-wrap:wrap; gap:16px;">
                <div>
                    <div style="font-size:20px; font-weight:800; color:#1e3a5f" id="a_designation"></div>
                    <div style="color:#7a8aa0; font-size:13px; margin-top:4px" id="a_serie"></div>
                    <div style="margin-top:10px; display:flex; gap:8px;">
                        <span class="badge" id="b_etat"></span>
                        <span class="badge" id="b_statut"></span>
                    </div>
                </div>
                <div style="display:flex; gap:8px; align-items:center; flex-wrap:wrap;">
                    <select id="newStatut" style="padding:8px 12px; border:1.5px solid #e2e8f0; border-radius:8px; font-size:13px;">
                        <option value="en_service">En service</option>
                        <option value="maintenance">Maintenance</option>
                        <option value="reforme">Réforme</option>
                        <option value="cession">Cession</option>
                        <option value="sorti">Sorti</option>
                    </select>
                    <button class="btn btn-primary btn-sm" onclick="changeStatut()">Changer statut</button>
                    <button class="btn btn-ghost btn-sm" onclick="delActif()">Supprimer</button>
                </div>
            </div>
            <div id="a_info" style="display:grid; grid-template-columns:repeat(auto-fit,minmax(180px,1fr)); gap:14px; margin-top:22px; font-size:13px;"></div>
            <div id="a_desc_wrap" style="margin-top:16px; font-size:13px; color:#475569; display:none">
                <b>Description :</b> <span id="a_description"></span>
            </div>
        </div>

        <!-- Actions -->
        <div style="display:flex; gap:10px; margin-bottom:20px; flex-wrap:wrap;">
            <button class="btn btn-teal" onclick="showTab('affectations'); toggleAff()">+ Affecter</button>
            <button class="btn btn-primary" onclick="showTab('evaluations'); toggleEval()">+ Évaluer</button>
            <a class="btn btn-ghost" href="/maintenances">+ Maintenance</a>
        </div>

        <!-- Formulaire affectation -->
        <div class="panel" id="affForm" style="display:none; margin-bottom:24px;">
            <div class="panel-head"><h2>Nouvelle affectation</h2></div>
            <form class="form-grid" id="affectationForm">
                <div><label>Service destinataire *</label><input id="af_service" required placeholder="Ministère des Finances"></div>
                <div><label>Date de début *</label><input type="date" id="af_debut" required></div>
                <div><label>Date de fin</label><input type="date" id="af_fin"></div>
            </form>
            <div class="form-actions">
                <button class="btn btn-teal" onclick="createAffectation()">Affecter</button>
                <button class="btn btn-ghost" onclick="document.getElementById('affForm').style.display='none'">Annuler</button>
            </div>
        </div>

        <!-- Formulaire evaluation -->
        <div class="panel" id="evalForm" style="display:none; margin-bottom:24px;">
            <div class="panel-head"><h2>Nouvelle évaluation</h2></div>
            <form class="form-grid" id="evaluationForm">
                <div><label>Date d'évaluation *</label><input type="date" id="ev_date" required></div>
                <div><label>Valeur estimée (FCFA)</label><input type="number" id="ev_val" min="0" step="0.01"></div>
                <div><label>État</label>
                    <select id="ev_etat">
                        <option value="">— inchangé —</option>
                        <option value="neuf">Neuf</option><option value="bon">Bon</option>
                        <option value="use">Usagé</option><option value="hors_service">Hors service</option>
                    </select>
                </div>
                <div style="grid-column:1/-1"><label>Observation</label>
                    <textarea id="ev_obs" placeholder="État constaté, dépréciation..."></textarea>
                </div>
            </form>
            <div class="form-actions">
                <button class="btn btn-teal" onclick="createEvaluation()">Enregistrer</button>
                <button class="btn btn-ghost" onclick="document.getElementById('evalForm').style.display='none'">Annuler</button>
            </div>
        </div>

        <!-- Onglets -->
        <div class="panel">
            <div class="panel-head" style="gap:6px;">
                <button class="btn btn-sm tab-btn active" data-tab="affectations" onclick="showTab('affectations')">Affectations</button>
                <button class="btn btn-sm tab-btn btn-ghost" data-tab="evaluations" onclick="showTab('evaluations')">Évaluations</button>
                <button class="btn btn-sm tab-btn btn-ghost" data-tab="maintenances" onclick="showTab('maintenances')">Maintenances</button>
            </div>
            <div style="overflow-x:auto">
                <table>
                    <thead id="tabHead"></thead>
                    <tbody id="tabBody"></tbody>
                </table>
            </div>
        </div>
    </div>
    <div id="loading" style="text-align:center; color:#94a3b8; padding:60px;">Chargement…</div>
</div>

@include('partials.scripts')
<script>
const ID = location.pathname.split('/').filter(Boolean).pop();
const etatBadge = e => ({neuf:'b-en_service',bon:'b-en_service',use:'b-maintenance',hors_service:'b-sorti'}[e] || 'b-sorti');
let actif = null;

function toggleAff() { const b=document.getElementById('affForm'); b.style.display=b.style.display==='none'?'block':'none'; if(b.style.display==='block')document.getElementById('af_debut').valueAsDate=new Date(); }
function toggleEval() { const b=document.getElementById('evalForm'); b.style.display=b.style.display==='none'?'block':'none'; if(b.style.display==='block')document.getElementById('ev_date').valueAsDate=new Date(); }

function info(label, value) {
    return `<div><div style="font-size:11px;color:#7a8aa0;text-transform:uppercase;font-weight:600">${label}</div>
            <div style="font-weight:600;margin-top:3px">${value ?? '-'}</div></div>`;
}

async function load() {
    actif = await api('/api/v1/assets/actifs/' + ID);
    document.getElementById('loading').style.display = 'none';
    document.getElementById('content').style.display = 'block';
    document.title = 'GPE — ' + actif.designation;
    document.getElementById('title').textContent = actif.designation;
    document.getElementById('a_designation').textContent = actif.designation;
    document.getElementById('a_serie').textContent = 'N° série : ' + actif.numero_serie;

    const be = document.getElementById('b_etat'); be.className = 'badge ' + etatBadge(actif.etat); be.textContent = actif.etat;
    const bs = document.getElementById('b_statut'); bs.className = 'badge b-' + actif.statut; bs.textContent = actif.statut.replace('_',' ');
    document.getElementById('newStatut').value = actif.statut;

    document.getElementById('a_info').innerHTML = [
        info('Catégorie', actif.categorie?.nom),
        info('Marque / Modèle', [actif.marque, actif.modele].filter(Boolean).join(' ') || null),
        info('Acquisition', (actif.date_acquisition||'').slice(0,10)),
        info('Valeur acquisition', fmt(actif.valeur_acquisition) + ' FCFA'),
        info('Valeur actuelle', fmt(actif.valeur_actuelle) + ' FCFA'),
        info('Localisation', actif.localisation ? actif.localisation.region + (actif.localisation.commune ? ' / ' + actif.localisation.commune : '') : null),
    ].join('');
    if (actif.description) {
        document.getElementById('a_desc_wrap').style.display = 'block';
        document.getElementById('a_description').textContent = actif.description;
    }
    showTab('affectations');
}

async function changeStatut() {
    try {
        await apiSend('/api/v1/assets/actifs/' + ID + '/statut', 'POST', {statut: document.getElementById('newStatut').value});
        load();
    } catch (e) { showAlert('alertBox', e.message); }
}

async function delActif() {
    if (!confirm('Supprimer définitivement cet actif ?')) return;
    try {
        await api('/api/v1/assets/actifs/' + ID, {method:'DELETE'});
        location.href = '/';
    } catch (e) { showAlert('alertBox', e.message); }
}

async function createAffectation() {
    try {
        await apiSend('/api/v1/assets/affectations', 'POST', {
            actif_id: parseInt(ID),
            service_destinataire: document.getElementById('af_service').value,
            date_debut: document.getElementById('af_debut').value,
            date_fin: document.getElementById('af_fin').value || null,
        });
        toggleAff(); document.getElementById('affectationForm').reset();
        load();
    } catch (e) { showAlert('alertBox', e.message); }
}

async function cloturerAffectation(id) {
    try {
        await apiSend('/api/v1/assets/affectations/' + id + '/cloturer', 'PATCH', {date_fin: new Date().toISOString().slice(0,10)});
        load();
    } catch (e) { showAlert('alertBox', e.message); }
}

async function createEvaluation() {
    try {
        const body = {
            actif_id: parseInt(ID),
            date_eval: document.getElementById('ev_date').value,
            observation: document.getElementById('ev_obs').value || null,
        };
        const v = document.getElementById('ev_val').value, et = document.getElementById('ev_etat').value;
        if (v) body.valeur_estimee = parseFloat(v);
        if (et) body.etat = et;
        await apiSend('/api/v1/assets/evaluations', 'POST', body);
        toggleEval(); document.getElementById('evaluationForm').reset();
        load();
    } catch (e) { showAlert('alertBox', e.message); }
}

const TABS = {
    affectations: {
        head: '<tr><th>Service</th><th>Début</th><th>Fin</th><th></th></tr>',
        rows: a => (a.affectations || []).map(x => `
            <tr><td><b>${x.service_destinataire}</b></td>
            <td>${(x.date_debut||'').slice(0,10)}</td>
            <td>${x.date_fin ? x.date_fin.slice(0,10) : '<span class="badge b-en_service">en cours</span>'}</td>
            <td>${!x.date_fin ? `<button class="btn btn-sm btn-ghost" onclick="cloturerAffectation(${x.id})">Clôturer</button>` : ''}</td></tr>`).join('')
            || '<tr><td colspan="4" class="empty">Aucune affectation</td></tr>',
    },
    evaluations: {
        head: '<tr><th>Date</th><th>Valeur estimée</th><th>État</th><th>Observation</th></tr>',
        rows: a => (a.evaluations || []).map(x => `
            <tr><td>${(x.date_eval||'').slice(0,10)}</td>
            <td>${x.valeur_estimee != null ? fmt(x.valeur_estimee) : '-'}</td>
            <td>${x.etat ? `<span class="badge ${etatBadge(x.etat)}">${x.etat}</span>` : '-'}</td>
            <td>${x.observation || '-'}</td></tr>`).join('')
            || '<tr><td colspan="4" class="empty">Aucune évaluation</td></tr>',
    },
    maintenances: {
        head: '<tr><th>#</th><th>Type</th><th>Demande</th><th>Description</th><th>Coût</th><th>Statut</th></tr>',
        rows: null, // charge separement
    },
};

async function showTab(name) {
    document.querySelectorAll('.tab-btn').forEach(b => {
        b.className = 'btn btn-sm tab-btn ' + (b.dataset.tab === name ? 'active btn-primary' : 'btn-ghost');
    });
    document.getElementById('tabHead').innerHTML = TABS[name].head;
    if (name === 'maintenances') {
        const d = await api('/api/v1/maintenance/maintenances?actif_id=' + ID + '&per_page=50');
        document.getElementById('tabBody').innerHTML = (d.data || []).map(m => `
            <tr><td>${m.id}</td>
            <td><span class="badge b-${m.type}">${m.type === 'preventive' ? 'Préventive' : 'Corrective'}</span></td>
            <td>${(m.date_demande||'').slice(0,10)}</td>
            <td>${m.description || '-'}</td>
            <td>${m.cout != null ? fmt(m.cout) : '-'}</td>
            <td><span class="badge b-${m.statut}">${m.statut.replace('_',' ')}</span></td></tr>`).join('')
            || '<tr><td colspan="6" class="empty">Aucune maintenance</td></tr>';
    } else {
        document.getElementById('tabBody').innerHTML = TABS[name].rows(actif);
    }
}

load().catch(e => { document.getElementById('loading').textContent = 'Erreur: ' + e.message; });
</script>
</body>
</html>
