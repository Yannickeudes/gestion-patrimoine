@include('partials.layout', ['title' => 'Utilisateurs', 'active' => 'utilisateurs'])
    <div class="topbar">
        <h1>Utilisateurs</h1>
        <div class="user">Connecté : <b id="userName"></b></div>
    </div>

    <div id="alertBox"></div>

    <div class="panel">
        <div class="panel-head">
            <h2>Comptes utilisateurs</h2>
            <button class="btn btn-primary" onclick="toggleForm()">+ Nouvel utilisateur</button>
        </div>

        <div id="formBox" style="display:none; border-bottom:1px solid #eef1f6;">
            <form class="form-grid" id="userForm">
                <div><label>Nom *</label><input id="u_name" required></div>
                <div><label>Email *</label><input type="email" id="u_email" required></div>
                <div><label>Mot de passe *</label><input type="password" id="u_pass" required minlength="8"></div>
                <div><label>Rôle *</label>
                    <select id="u_role" required>
                        <option value="agent">Agent</option>
                        <option value="responsable">Responsable</option>
                        <option value="admin">Administrateur</option>
                    </select>
                </div>
            </form>
            <div class="form-actions">
                <button class="btn btn-teal" onclick="createUser()">Créer</button>
                <button class="btn btn-ghost" onclick="toggleForm()">Annuler</button>
            </div>
        </div>

        <div style="overflow-x:auto">
            <table>
                <thead><tr><th>Nom</th><th>Email</th><th>Rôle</th><th>Depuis</th></tr></thead>
                <tbody id="body"></tbody>
            </table>
        </div>
    </div>
</div>

@include('partials.scripts')
<script>
const roleBadge = r => ({admin:'b-sorti', responsable:'b-cession', agent:'b-en_service'}[r] || 'b-en_service');

function toggleForm() {
    const b = document.getElementById('formBox');
    b.style.display = b.style.display === 'none' ? 'block' : 'none';
}

async function load() {
    try {
        const data = await api('/api/v1/auth/users?per_page=100');
        const users = data.data || data || [];
        document.getElementById('body').innerHTML = users.map(u => `
            <tr><td><b>${u.name}</b></td><td>${u.email}</td>
            <td><span class="badge ${roleBadge(u.role)}">${u.role}</span></td>
            <td>${(u.created_at || '-').slice(0,10)}</td></tr>`).join('')
            || '<tr><td colspan="4" class="empty">Aucun utilisateur</td></tr>';
    } catch (e) {
        document.getElementById('body').innerHTML =
            '<tr><td colspan="4" class="empty">Accès réservé aux administrateurs</td></tr>';
        showAlert('alertBox', 'Seul un administrateur peut gérer les utilisateurs', 'warn');
    }
}

async function createUser() {
    try {
        await apiSend('/api/v1/auth/users', 'POST', {
            name: document.getElementById('u_name').value,
            email: document.getElementById('u_email').value,
            password: document.getElementById('u_pass').value,
            password_confirmation: document.getElementById('u_pass').value,
            role: document.getElementById('u_role').value,
        });
        toggleForm();
        document.getElementById('userForm').reset();
        load();
    } catch (e) {
        showAlert('alertBox', typeof e === 'object' ? JSON.stringify(e) : e.message || 'Erreur');
    }
}
load();
</script>
</body>
</html>
