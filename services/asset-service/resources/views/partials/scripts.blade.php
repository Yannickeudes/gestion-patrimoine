<script>
const token = localStorage.getItem('gpe_token');
if (!token) window.location.href = '/login';
const user = JSON.parse(localStorage.getItem('gpe_user') || '{}');
const un = document.getElementById('userName');
if (un) un.textContent = user.name || '';

const headers = {'Authorization': 'Bearer ' + token, 'Content-Type': 'application/json'};
const fmt = n => new Intl.NumberFormat('fr-FR').format(n || 0);

async function api(path, options = {}) {
    const res = await fetch(path, {...options, headers: {...headers, ...(options.headers || {})}});
    if (res.status === 401) { logout(); throw new Error('unauthorized'); }
    if (res.status === 204) return null;
    const text = await res.text();
    let data; try { data = JSON.parse(text); } catch { data = text; }
    if (!res.ok) throw new Error((data && data.message) || ('Erreur ' + res.status));
    return data;
}
async function apiSend(path, method, body) {
    return api(path, {method, body: body ? JSON.stringify(body) : undefined});
}

function logout() {
    fetch('/api/v1/auth/logout', {method: 'POST', headers});
    localStorage.clear();
    window.location.href = '/login';
}

function showAlert(id, msg, type = 'err') {
    const el = document.getElementById(id);
    if (!el) return;
    el.className = 'alert alert-' + type;
    el.textContent = msg;
    el.style.display = 'block';
}
</script>
