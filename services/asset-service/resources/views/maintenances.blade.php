@include('partials.layout', ['title' => 'Maintenances', 'active' => 'maintenances'])
    <div class="topbar">
        <h1>Maintenances</h1>
        <div class="user">Connecté : <b id="userName"></b></div>
    </div>

    <div id="alertBox"></div>

    <div class="panel">
        <div class="panel-head">
            <h2>Ordres de maintenance</h2>
            <div class="filters">
                <select id="statutFilter">
                    <option value="">Tous les statuts</option>
                    <option value="ouvert">Ouvert</option>
                    <option value="en_cours">En cours</option>
                    <option value="clos">Clôturé</option>
                </select>
                <select id="typeFilter">
                    <option value="">Tous les types</option>
                    <option value="preventive">Préventive</option>
                    <option value="corrective">Corrective</option>
                </select>
                <button class="btn btn-primary" onclick="toggleForm()">+ Nouvelle maintenance</button>
            </div>
        </div>

        <div id="formBox" style="display:none; border-bottom:1px solid #eef1f6;">
            <form class="form-grid" id="maintForm">
                <div><label>ID de l'actif *</label><input type="number" id="f_actif" required min="1"></div>
                <div><label>Type *</label>
                    <select id="f_type" required>
                        <option value="preventive">Préventive</option>
                        <option value="corrective">Corrective</option>
                    </select>
                </div>
                <div><label>Date de demande *</label><input type="date" id="f_date" required></div>
                <div><label>Coût (FCFA)</label><input type="number" id="f_cout" min="0" step="0.01" placeholder="0"></div>
                <div style="grid-column:1/-1"><label>Description *</label>
                    <textarea id="f_desc" required placeholder="Décrivez l'intervention..."></textarea>
                </div>
            </form>
            <div class="form-actions">
                <button class="btn btn-teal" onclick="createMaint()">Créer</button>
                <button class="btn btn-ghost" onclick="toggleForm()">Annuler</button>
            </div>
        </div>

        <div style="overflow-x:auto">
            <table>
                <thead>
                    <tr>
                        <th>#</th><th>Actif</th><th>Type</th><th>Demande</th><th>Description</th>
                        <th>Coût</th><th>Statut</th><th>Actions</th>
                    </tr>
                </thead>
                <tbody id="body"></tbody>
            </table>
        </div>
    </div>
</div>

@include('partials.scripts')
<script>
function toggleForm() {
    const b = document.getElementById('formBox');
    b.style.display = b.style.display === 'none' ? 'block' : 'none';
    if (b.style.display === 'block') document.getElementById('f_date').valueAsDate = new Date();
}

async function load() {
    const p = new URLSearchParams();
    if (document.getElementById('statutFilter').value) p.set('statut', document.getElementById('statutFilter').value);
    if (document.getElementById('typeFilter').value) p.set('type', document.getElementById('typeFilter').value);
    const data = await api('/api/v1/maintenance/maintenances?' + p);
    document.getElementById('body').innerHTML = (data.data || []).map(m => `
        <tr>
            <td>${m.id}</td>
            <td><b>#${m.actif_id}</b></td>
            <td><span class="badge b-${m.type}">${m.type === 'preventive' ? 'Préventive' : 'Corrective'}</span></td>
            <td>${(m.date_demande || '-').slice(0,10)}</td>
            <td style="max-width:260px">${m.description || '-'}</td>
            <td>${m.cout != null ? fmt(m.cout) : '-'}</td>
            <td><span class="badge b-${m.statut}">${m.statut.replace('_',' ')}</span></td>
            <td>
                ${m.statut !== 'clos' ? `
                    <button class="btn btn-sm btn-ghost" onclick="clos(${m.id})">Clôturer</button>
                    <button class="btn btn-sm btn-danger" onclick="del(${m.id})">Suppr.</button>` : '—'}
            </td>
        </tr>`).join('') || '<tr><td colspan="8" class="empty">Aucune maintenance</td></tr>';
}

async function createMaint() {
    try {
        await apiSend('/api/v1/maintenance/maintenances', 'POST', {
            actif_id: parseInt(document.getElementById('f_actif').value),
            type: document.getElementById('f_type').value,
            date_demande: document.getElementById('f_date').value,
            description: document.getElementById('f_desc').value,
            cout: document.getElementById('f_cout').value || null,
        });
        toggleForm();
        document.getElementById('maintForm').reset();
        load();
    } catch (e) {
        showAlert('alertBox', e.message || 'Erreur lors de la création');
    }
}

async function clos(id) {
    try {
        const r = await apiSend('/api/v1/maintenance/maintenances/' + id, 'PUT', {statut: 'clos'});
        if (r && r.message) showAlert('alertBox', r.message); else load();
    } catch (e) { showAlert('alertBox', 'Erreur: ' + e.message); }
}
async function del(id) {
    if (!confirm('Supprimer cette maintenance ?')) return;
    try {
        await api('/api/v1/maintenance/maintenances/' + id, {method: 'DELETE'});
        load();
    } catch (e) { showAlert('alertBox', 'Erreur: ' + e.message); }
}

document.getElementById('statutFilter').addEventListener('change', load);
document.getElementById('typeFilter').addEventListener('change', load);
load();
</script>
</body>
</html>
