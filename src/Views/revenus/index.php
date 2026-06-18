<?php include __DIR__ . '/../layouts/header.php';

$userId = \App\Core\Session::get('user_id');
$revenus = \App\Models\Revenue::getByUser($userId);
$categories = \App\Models\Revenue::getRevenueCategories();
$totalMensuel = \App\Models\Revenue::getMonthlyRecurring($userId);
$totalAnnuel = \App\Models\Revenue::getYearlyTotal($userId);
$budgets = \App\Models\BudgetPeriod::getAllByUser($userId);
?>

<div class="row mb-3">
    <div class="col"><h3><i class="bi bi-cash-stack"></i> Revenus</h3></div>
    <div class="col-auto">
        <button class="btn btn-sm btn-outline-success" onclick="showAddRevenuePage()">
            <i class="bi bi-plus-lg"></i> Nouveau revenu
        </button>
    </div>
</div>

<div class="row g-3 mb-4">
    <div class="col-md-4">
        <div class="card border-primary h-100">
            <div class="card-body text-center">
                <p class="text-muted small">Total ce mois</p>
                <h3 class="text-primary"><?= number_format($totalMensuel, 2) ?>€</h3>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card border-info h-100">
            <div class="card-body text-center">
                <p class="text-muted small">Total annuel estimé</p>
                <h3 class="text-info"><?= number_format($totalAnnuel, 2) ?>€</h3>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card border-success h-100">
            <div class="card-body text-center">
                <p class="text-muted small">Revenus récurrents/mois</p>
                <h3 class="text-success"><?= number_format($totalMensuel, 2) ?>€</h3>
            </div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header d-flex justify-content-between">
        <span><i class="bi bi-list"></i> Tous les revenus</span>
        <div class="d-flex gap-2">
            <select class="form-select form-select-sm" id="filterCategory" style="width:auto;" onchange="filterRevenus()">
                <option value="">Toutes catégories</option>
                <?php foreach ($categories as $c): ?>
                <option value="<?= $c['id'] ?>"><?= htmlspecialchars($c['nom']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
    </div>
    <div class="card-body table-responsive">
        <table class="table table-hover" id="revenusTable">
            <thead class="table-dark">
                <tr>
                    <th>Date</th>
                    <th>Source</th>
                    <th>Catégorie</th>
                    <th>Montant</th>
                    <th>Période</th>
                    <th>Récurrent</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($revenus)): ?>
                <tr><td colspan="7" class="text-muted text-center">Aucun revenu enregistré</td></tr>
                <?php else: foreach ($revenus as $r): ?>
                <tr data-category="<?= $r['category_id'] ?>">
                    <td><?= date('d/m/Y', strtotime($r['date_revenu'])) ?></td>
                    <td><?= htmlspecialchars($r['source'] ?: '-') ?></td>
                    <td><span class="badge bg-<?= $r['type']==='actif'?'success':'warning' ?>"><?= htmlspecialchars($r['category_nom']) ?></span></td>
                    <td class="text-success fw-bold">+<?= number_format($r['montant'], 2) ?>€</td>
                    <td><small><?= $r['start_date'] ?> → <?= $r['end_date'] ?></small></td>
                    <td><?= $r['est_recurrent'] ? '<span class="badge bg-info">'.$r['frequence'].'</span>' : '<span class="badge bg-secondary">Non</span>' ?></td>
                    <td>
                        <button class="btn btn-sm btn-outline-warning py-0" onclick="editRevenue(<?= $r['id'] ?>)"><i class="bi bi-pencil"></i></button>
                        <button class="btn btn-sm btn-outline-danger py-0" onclick="deleteRevenue(<?= $r['id'] ?>)"><i class="bi bi-x"></i></button>
                    </td>
                </tr>
                <?php endforeach; endif; ?>
            </tbody>
        </table>
    </div>
</div>

<script>
function filterRevenus() {
    const cat = document.getElementById('filterCategory').value;
    document.querySelectorAll('#revenusTable tbody tr').forEach(row => {
        if (row.cells.length > 1) {
            row.style.display = (!cat || row.dataset.category === cat) ? '' : 'none';
        }
    });
}

async function deleteRevenue(id) {
    if (!confirm('Supprimer ce revenu ?')) return;
    const data = new FormData(); data.append('id', id);
    const r = await apiFetch('api/budget.php?action=delete_revenue', {method:'POST', body:data});
    if (r?.success) location.reload();
}

async function editRevenue(id) {
    const r = await apiFetch('api/budget.php?action=get_revenue&id='+id);
    if (!r?.revenue) return;
    const rev = r.revenue;
    // Quick inline edit prompt solution
    const newMontant = prompt('Montant (€):', rev.montant);
    if (newMontant === null) return;
    const newSource = prompt('Source:', rev.source||'');
    if (newSource === null) return;
    const data = new FormData();
    data.append('id', id);
    data.append('montant', parseFloat(newMontant)||0);
    data.append('source', newSource);
    const res = await apiFetch('api/budget.php?action=update_revenue', {method:'POST', body:data});
    if (res?.success) { showToast('success', 'Revenu mis à jour'); location.reload(); }
}

function showAddRevenuePage() {
    // Redirect to budget page with modal trigger
    window.location.href = 'budget.php';
}
</script>

<?php include __DIR__ . '/../layouts/footer.php'; ?>