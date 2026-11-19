@include('partials.layout', ['title' => 'Actifs', 'active' => 'actifs'])
    <div class="topbar">
        <h1>Actifs du patrimoine</h1>
        <div class="user">Connecté : <b id="userName"></b></div>
    </div>

    <div id="alertBox"></div>

    <div class="panel">
        <div class="panel-head">
            <h2>Inventaire des actifs</h2>
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
                <button class="btn btn-primary" onclick="toggleForm()">+ Nouvel actif</button>
            </div>
        </div>

        <div id="formBox" style="display:none; border-bottom:1px solid #eef1f6;">
            <form class="form-grid" id="actifForm">
                <div><label>N° série *</label><input id="a_serie" required placeholder="VEH-2025-001"></div>
                <div><label>Désignation *</label><input id="a_desc" required placeholder="Toyota Hilux"></div>
                <div><label>Catégorie *</label><select id="a_cat" required></select></div>
                <div><label>Localisation</label><select id="a_loc"><option value="">—</option></select></div>
                <div><label>Marque</label><input id="a_marque"></div>
                <div><label>Modèle</label><input id="a_modele"></div>
                <div><label>Date d'acquisition *</label><input type="date" id="a_date" required></div>
                <div><label>Valeur acquisition (FCFA) *</label><input type="number" id="a_val" required min="0" step="0.01"></div>
                <div><label>Valeur actuelle (FCFA)</label><input type="number" id="a_valact" min="0" step="0.01" placeholder="= valeur acquisition"></div>
                <div><label>État *</label>
                    <select id="a_etat" required>
                        <option value="neuf">Neuf</option><option value="bon">Bon</option>
                        <option value="use">Usagé</option><option value="hors_service">Hors service</option>
                    </select>
                </div>
                <div><label>Statut</label>
                    <select id="a_statut">
                        <option value="en_service">En service</option><option value="maintenance">Maintenance</option>
                        <option value="reforme">Réforme</option><option value="cession">Cession</option>
                        <option value="sorti">Sorti</option>
                    </select>
                </div>
                <div style="grid-column:1/-1"><label>Description</label>
                    <textarea id="a_description" placeholder="Caractéristiques, remarques..."></textarea>
                </div>
            </form>
            <div class="form-actions">
                <button class="btn btn-teal" onclick="createActif()">Créer l'actif</button>
                <button class="btn btn-ghost" onclick="toggleForm()">Annuler</button>
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
                <tbody id="body"></tbody>
            </table>
        </div>
        <div class="panel-head" style="border-top:1px solid #eef1f6; border-bottom:none;">
            <span id="pager" class="user" style="font-size:12px;color:#64748b"></span>
            <button class="btn btn-ghost btn-sm" id="nextBtn" onclick="nextPage()">Page suivante →</button>
        </div>
    </div>
</div>

@include('partials.scripts')
<script>
let page = 1, lastPage = 1;
const etatBadge = e => ({neuf:'b-en_service',bon:'b-en_service',use:'b-maintenance',hors_service:'b-sorti'}[e] || 'b-sorti');

function toggleForm() {
    const b = document.getElementById('formBox');
    b.style.display = b.style.display === 'none' ? 'block' : 'none';
}

async function loadRefs() {
    const [cats, locs] = await Promise.all([
        api('/api/v1/assets/categories?per_page=100'),
        api('/api/v1/assets/localisations?per_page=100'),
    ]);
    document.getElementById('a_cat').innerHTML = (cats.data || cats || []).map(c =>
        `<option value="${c.id}">${c.nom}</option>`).join('');
    document.getElementById('a_loc').innerHTML = '<option value="">—</option>' +
        (locs.data || locs || []).map(l =>
        `<option value="${l.id}">${l.region}${l.commune ? ' / ' + l.commune : ''}</option>`).join('');
}

async function load() {
    const p = new URLSearchParams({page});
    if (document.getElementById('search').value) p.set('search', document.getElementById('search').value);
    if (document.getElementById('statutFilter').value) p.set('statut', document.getElementById('statutFilter').value);
    const data = await api('/api/v1/assets/actifs?' + p);
    lastPage = data.last_page || 1;
    document.getElementById('pager').textContent = `Page ${data.current_page} / ${lastPage} — ${data.total} actif(s)`;
    document.getElementById('nextBtn').style.display = data.current_page < lastPage ? '' : 'none';
    document.getElementById('body').innerHTML = (data.data || []).map(a => `
        <tr style="cursor:pointer" onclick="location.href='/actifs/${a.id}'">
            <td><b>${a.numero_serie}</b></td>
            <td>${a.designation}</td>
            <td>${a.categorie?.nom || '-'}</td>
            <td>${(a.date_acquisition || '-').slice(0,10)}</td>
            <td><b>${fmt(a.valeur_actuelle)}</b></td>
            <td><span class="badge ${etatBadge(a.etat)}">${a.etat}</span></td>
            <td><span class="badge b-${a.statut}">${a.statut.replace('_',' ')}</span></td>
            <td>${a.localisation ? a.localisation.region : '-'}</td>
        </tr>`).join('') || '<tr><td colspan="8" class="empty">Aucun actif</td></tr>';
}
function nextPage() { if (page < lastPage) { page++; load(); } }

async function createActif() {
    try {
        await apiSend('/api/v1/assets/actifs', 'POST', {
            numero_serie: document.getElementById('a_serie').value,
            designation: document.getElementById('a_desc').value,
            categorie_id: parseInt(document.getElementById('a_cat').value),
            localisation_id: document.getElementById('a_loc').value || null,
            marque: document.getElementById('a_marque').value || null,
            modele: document.getElementById('a_modele').value || null,
            date_acquisition: document.getElementById('a_date').value,
            valeur_acquisition: parseFloat(document.getElementById('a_val').value),
            valeur_actuelle: document.getElementById('a_valact').value ? parseFloat(document.getElementById('a_valact').value) : undefined,
            etat: document.getElementById('a_etat').value,
            statut: document.getElementById('a_statut').value,
            description: document.getElementById('a_description').value || null,
        });
        toggleForm();
        document.getElementById('actifForm').reset();
        load();
    } catch (e) { showAlert('alertBox', typeof e === 'object' ? JSON.stringify(e) : e.message); }
}

document.getElementById('search').addEventListener('input', () => { page = 1; load(); });
document.getElementById('statutFilter').addEventListener('change', () => { page = 1; load(); });
loadRefs(); load();
</script>
</body>
</html>
