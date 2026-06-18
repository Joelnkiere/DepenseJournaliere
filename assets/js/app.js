// Budget Manager - Main Application JS

function showToast(type, message) {
    const container = document.getElementById('toastContainer') || document.body;
    const toast = document.createElement('div');
    toast.className = `toast align-items-center text-bg-${type} border-0 show`;
    toast.setAttribute('role', 'alert');
    toast.innerHTML = `
        <div class="d-flex">
            <div class="toast-body">${message}</div>
            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
        </div>
    `;
    container.appendChild(toast);
    setTimeout(() => { toast.remove(); }, 4000);
}

async function apiFetch(url, options = {}) {
    try {
        const resp = await fetch(url, options);
        return await resp.json();
    } catch (err) {
        showToast('danger', 'Erreur réseau : ' + err.message);
        return null;
    }
}

function showAddExpenseModal() {
    const el = document.getElementById('addExpenseModal');
    if (el) new bootstrap.Modal(el).show();
}

function showAddGoalModal() {
    const el = document.getElementById('addGoalModal');
    if (el) new bootstrap.Modal(el).show();
}

function showTransferModal() {
    const el = document.getElementById('transferModal');
    if (el) new bootstrap.Modal(el).show();
}

function editRevenu() {
    const el = document.getElementById('editRevenuModal');
    if (el) new bootstrap.Modal(el).show();
}

async function deleteExpense(id) {
    if (!confirm('Supprimer cette dépense ?')) return;
    const data = new FormData();
    data.append('id', id);
    const result = await apiFetch('api/expenses.php?action=delete', { method: 'POST', body: data });
    if (result && result.success) {
        showToast('success', 'Dépense supprimée');
        setTimeout(() => location.reload(), 300);
    }
}

async function cloturerMois(mois) {
    if (!confirm(`Clôturer le budget de ${mois} ? Cette action est irréversible.`)) return;
    const data = new FormData();
    data.append('mois', mois);
    const result = await apiFetch('api/budget.php?action=cloturer', { method: 'POST', body: data });
    if (result && result.success) {
        showToast('success', result.message);
        setTimeout(() => location.reload(), 500);
    } else if (result) {
        showToast('danger', result.error || 'Erreur');
    }
}

async function loadExpenses(type) {
    const tableId = type === 'prevu' ? 'prevuTable' : 'expensesTable';
    const table = document.getElementById(tableId);
    if (!table) return;
    const tbody = table.querySelector('tbody');
    if (!tbody) return;

    const data = await apiFetch(`api/expenses.php?action=list&type=${type}&mois=${moisActuel}`);
    tbody.innerHTML = '';

    if (!data || data.length === 0) {
        tbody.innerHTML = '<tr><td colspan="5" class="text-center text-muted py-3">Aucune dépense</td></tr>';
        return;
    }

    data.forEach(exp => {
        const tr = document.createElement('tr');
        tr.innerHTML = `
            <td>${new Date(exp.date_depense).toLocaleDateString('fr-FR')}</td>
            <td><span class="badge bg-secondary">${exp.category_nom || ''}</span></td>
            <td class="text-danger fw-bold">-${parseFloat(exp.montant).toFixed(2)}€</td>
            <td>${exp.description || '-'}</td>
            <td>
                <button class="btn btn-sm btn-outline-danger" onclick="deleteExpense(${exp.id})">
                    <i class="bi bi-trash"></i>
                </button>
            </td>
        `;
        tbody.appendChild(tr);
    });
}

document.addEventListener('DOMContentLoaded', () => {
    const formHandlers = {
        addExpenseForm: async (form) => {
            const data = new FormData(form);
            const result = await apiFetch('api/expenses.php?action=add', { method: 'POST', body: data });
            if (result?.success) {
                showToast('success', 'Dépense ajoutée !');
                const modal = bootstrap.Modal.getInstance(document.getElementById('addExpenseModal'));
                if (modal) modal.hide();
                form.reset();
                setTimeout(() => location.reload(), 500);
            } else if (result) showToast('danger', result.error || 'Erreur');
        },
        editRevenuForm: async (form) => {
            const data = new FormData(form);
            const result = await apiFetch('api/budget.php?action=save_revenue', { method: 'POST', body: data });
            if (result?.success) {
                showToast('success', 'Revenu mis à jour !');
                const modal = bootstrap.Modal.getInstance(document.getElementById('editRevenuModal'));
                if (modal) modal.hide();
                const el = document.getElementById('revenuDisplay');
                if (el) el.textContent = result.revenu.toFixed(2) + '€';
                setTimeout(() => location.reload(), 500);
            } else if (result) showToast('danger', result.error || 'Erreur');
        },
        previsionsForm: async (form) => {
            const inputs = form.querySelectorAll('.prev-input');
            const previsions = [];
            inputs.forEach(inp => {
                const val = parseFloat(inp.value) || 0;
                if (val > 0) previsions.push({ category_id: parseInt(inp.dataset.category), montant: val });
            });
            const data = new FormData();
            data.append('mois', document.querySelector('[name=mois]')?.value || new Date().toISOString().slice(0, 7));
            data.append('previsions', JSON.stringify(previsions));
            const result = await apiFetch('api/budget.php?action=save_previsions', { method: 'POST', body: data });
            if (result?.success) showToast('success', 'Prévisions enregistrées !');
            else if (result) showToast('danger', result.error || 'Erreur');
        },
        revenuForm: async (form) => {
            const data = new FormData(form);
            const result = await apiFetch('api/budget.php?action=save_revenue', { method: 'POST', body: data });
            if (result?.success) { showToast('success', 'Revenu enregistré !'); setTimeout(() => location.reload(), 500); }
            else if (result) showToast('danger', result.error || 'Erreur');
        },
        addGoalForm: async (form) => {
            const data = new FormData(form);
            const result = await apiFetch('api/savings.php?action=add', { method: 'POST', body: data });
            if (result?.success) {
                showToast('success', 'Objectif créé !');
                const modal = bootstrap.Modal.getInstance(document.getElementById('addGoalModal'));
                if (modal) modal.hide();
                form.reset();
                setTimeout(() => location.reload(), 500);
            } else if (result) showToast('danger', result.error || 'Erreur');
        },
        transferForm: async (form) => {
            const data = new FormData(form);
            const result = await apiFetch('api/savings.php?action=add_funds', { method: 'POST', body: data });
            if (result?.success) {
                showToast('success', 'Fonds transférés vers l\'épargne !');
                const modal = bootstrap.Modal.getInstance(document.getElementById('transferModal'));
                if (modal) modal.hide();
                setTimeout(() => location.reload(), 500);
            } else if (result) showToast('danger', result.error || 'Erreur');
        },
    };

    Object.entries(formHandlers).forEach(([id, handler]) => {
        const form = document.getElementById(id);
        if (form) {
            form.addEventListener('submit', async (e) => { e.preventDefault(); await handler(form); });
        }
    });

    // Charts
    if (typeof categoryData !== 'undefined' && categoryData?.length > 0) {
        const ctx = document.getElementById('categoryPieChart');
        if (ctx) {
            new Chart(ctx, {
                type: 'pie',
                data: {
                    labels: categoryData.map(c => c.nom),
                    datasets: [{
                        data: categoryData.map(c => parseFloat(c.total) || 0),
                        backgroundColor: ['#dc3545','#fd7e14','#ffc107','#20c997','#0dcaf0','#0d6efd','#6610f2','#d63384','#6f42c1','#198754','#adb5bd','#495057','#212529'],
                    }]
                },
                options: { responsive: true, plugins: { legend: { position: 'right', labels: { padding: 15 } } } }
            });
        }
    }

    const lineCtx = document.getElementById('dailyLineChart');
    if (lineCtx && typeof moisActuel !== 'undefined') {
        (async () => {
            const data = await apiFetch(`api/expenses.php?action=list&type=reel&mois=${moisActuel}`);
            if (data?.length) {
                const daily = {};
                data.forEach(e => { daily[e.date_depense] = (daily[e.date_depense] || 0) + parseFloat(e.montant); });
                const sorted = Object.entries(daily).sort((a, b) => a[0].localeCompare(b[0]));
                new Chart(lineCtx, {
                    type: 'line',
                    data: {
                        labels: sorted.map(s => s[0].slice(-2) + '/' + s[0].slice(5, 7)),
                        datasets: [{ label: 'Dépenses quotidiennes', data: sorted.map(s => s[1]), borderColor: '#0dcaf0', backgroundColor: 'rgba(13,202,240,0.1)', fill: true, tension: 0.3 }]
                    },
                    options: { responsive: true, plugins: { legend: { display: false } }, scales: { y: { beginAtZero: true } } }
                });
            }
        })();
    }
});