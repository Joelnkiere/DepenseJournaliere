<?php include __DIR__ . '/../layouts/header.php';

$userId = \App\Core\Session::get('user_id');
$periodId = $_GET['period_id'] ?? null;
$activePeriod = $periodId ? \App\Models\BudgetPeriod::getById((int)$periodId) : \App\Models\BudgetPeriod::getOrCreateActive($userId);
$allPeriods = \App\Models\BudgetPeriod::getAllByUser($userId);
$categories = \App\Models\Category::getAll();
$accounts = \App\Models\Account::getByUser($userId);
$previsions = $activePeriod ? \App\Models\BudgetPeriod::getPrevisions($activePeriod['id']) : [];
$revenues = $activePeriod ? \App\Models\Revenue::getByUser($userId, $activePeriod['id']) : [];
$totalRevenus = $activePeriod ? \App\Models\Revenue::getTotalByBudget($activePeriod['id']) : 0;
$totalDepenses = $activePeriod ? \App\Models\Expense::getTotalByPeriod($activePeriod['id']) : 0;
$joursEcoules = $activePeriod ? \App\Models\BudgetPeriod::getDaysElapsed($activePeriod['id']) : 0;
$joursTotal = $activePeriod ? \App\Models\BudgetPeriod::getTotalDays($activePeriod['id']) : 1;
$recurrentMensuel = \App\Models\Revenue::getMonthlyRecurring($userId);

// Type helpers
$periodTypes = [
    'daily' => 'Journalier',
    'weekly' => 'Hebdomadaire',
    'monthly' => 'Mensuel',
    'yearly' => 'Annuel',
    'custom' => 'Personnalisé',
];
?>

<div class="row mb-3">
    <div class="col">
        <h3><i class="bi bi-piggy-bank"></i> Budget <span class="badge bg-<?= $activePeriod['type'] === 'daily' ? 'info' : ($activePeriod['type'] === 'weekly' ? 'success' : ($activePeriod['type'] === 'yearly' ? 'warning' : 'primary')) ?>"><?= $periodTypes[$activePeriod['type']] ?? 'Mensuel' ?></span></h3>
    </div>
    <div class="col-auto d-flex align-items-center gap-2">
        <select class="form-select form-select-sm" onchange="if(this.value) window.location='budget.php?period_id='+this.value">
            <option value="">Périodes</option>
            <?php foreach ($allPeriods as $p): ?>
            <option value="<?= $p['id'] ?>" <?= $activePeriod && $p['id']===$activePeriod['id'] ? 'selected' : '' ?>>
                <?= htmlspecialchars($p['nom'] ?: $p['start_date']) ?> <?= $p['cloture'] ? '🔒' : '' ?>
            </option>
            <?php endforeach; ?>
        </select>
        <button class="btn btn-sm btn-outline-primary" onclick="new bootstrap.Modal(document.getElementById('newPeriodModal')).show()">
            <i class="bi bi-plus-lg"></i> Nouvelle période
        </button>
    </div>
</div>

<?php if (!$activePeriod): ?>
<div class="alert alert-info">Aucune période active. Créez-en une !</div>
<?php else: ?>

<!-- KPI adaptés à la période -->
<div class="row g-3 mb-4">
    <div class="col-md-3">
        <div class="card border-primary h-100">
            <div class="card-body">
                <p class="text-muted small mb-0">Revenus</p>
                <h4 class="text-primary mb-0"><?= number_format($totalRevenus, 2) ?>€</h4>
                <small class="text-muted">dont <?= number_format($recurrentMensuel, 2) ?>€/mois récurrents</small>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-danger h-100">
            <div class="card-body">
                <p class="text-muted small mb-0">Dépensé</p>
                <h4 class="text-danger mb-0"><?= number_format($totalDepenses, 2) ?>€</h4>
                <small class="text-muted"><?= $joursEcoules ? round($totalDepenses / $joursEcoules, 2) : 0 ?>€/jour</small>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-<?= ($totalRevenus - $totalDepenses) >= 0 ? 'success' : 'danger' ?> h-100">
            <div class="card-body">
                <p class="text-muted small mb-0">Balance</p>
                <h4 class="text-<?= ($totalRevenus - $totalDepenses) >= 0 ? 'success' : 'danger' ?> mb-0"><?= number_format($totalRevenus - $totalDepenses, 2) ?>€</h4>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-info h-100">
            <div class="card-body">
                <p class="text-muted small mb-0">Progression</p>
                <h4 class="text-info mb-0"><?= round(($joursEcoules / $joursTotal) * 100) ?>%</h4>
                <div class="progress mt-1" style="height:4px;">
                    <div class="progress-bar bg-info" style="width:<?= ($joursEcoules / $joursTotal) * 100 ?>%"></div>
                </div>
                <small class="text-muted">Jour <?= $joursEcoules ?>/<?= $joursTotal ?></small>
            </div>
        </div>
    </div>
</div>

<div class="row g-3">
    <div class="col-md-7">
        <div class="card">
            <div class="card-header d-flex justify-content-between">
                <span><i class="bi bi-sliders"></i> Budget prévisionnel</span>
                <small class="text-muted"><?= $activePeriod['start_date'] ?> → <?= $activePeriod['end_date'] ?></small>
            </div>
            <div class="card-body">
                <form id="previsionsForm">
                    <div class="row g-3">
                        <?php foreach ($categories as $cat):
                            $prevMontant = 0;
                            foreach ($previsions as $p) {
                                if ((int)$p['category_id'] === (int)$cat['id']) { $prevMontant = (float)$p['montant_prevu']; break; }
                            }
                        ?>
                        <div class="col-md-6">
                            <label class="form-label small"><?= htmlspecialchars($cat['nom']) ?>
                                <span class="badge bg-<?= $cat['type']==='besoin'?'primary':($cat['type']==='envie'?'success':'info') ?>"><?= $cat['type'] ?></span>
                            </label>
                            <div class="input-group">
                                <span class="input-group-text">€</span>
                                <input type="number" step="0.01" class="form-control prev-input" data-category="<?= $cat['id'] ?>" value="<?= $prevMontant ?>">
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <input type="hidden" name="period_id" value="<?= $activePeriod['id'] ?>">
                    <button type="submit" class="btn btn-primary mt-3"><i class="bi bi-save"></i> Enregistrer</button>
                </form>
            </div>
        </div>
    </div>
    <div class="col-md-5">
        <div class="card">
            <div class="card-header"><i class="bi bi-cash-stack"></i> Sources de revenus</div>
            <div class="card-body">
                <button class="btn btn-sm btn-outline-success w-100 mb-3" onclick="showAddRevenueModal()">
                    <i class="bi bi-plus-lg"></i> Ajouter un revenu
                </button>
                <div class="table-responsive">
                    <table class="table table-sm">
                        <thead class="table-dark">
                            <tr><th>Source</th><th>Montant</th><th>Date</th><th></th></tr>
                        </thead>
                        <tbody>
                            <?php if (empty($revenues)): ?>
                            <tr><td colspan="4" class="text-muted text-center">Aucun revenu</td></tr>
                            <?php else: foreach ($revenues as $r): ?>
                            <tr>
                                <td><?= htmlspecialchars($r['category_nom']) ?><br><small class="text-muted"><?= htmlspecialchars($r['source'] ?: '') ?></small></td>
                                <td class="text-success fw-bold">+<?= number_format($r['montant'], 2) ?>€</td>
                                <td><?= date('d/m', strtotime($r['date_revenu'])) ?></td>
                                <td><button class="btn btn-sm btn-outline-danger py-0" onclick="deleteRevenue(<?= $r['id'] ?>)"><i class="bi bi-x"></i></button></td>
                            </tr>
                            <?php endforeach; endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <div class="card mt-3">
            <div class="card-header"><i class="bi bi-archive"></i> Actions</div>
            <div class="card-body">
                <?php if (!$activePeriod['cloture']): ?>
                <button class="btn btn-warning w-100 mb-2" onclick="cloturerPeriod(<?= $activePeriod['id'] ?>)">
                    <i class="bi bi-archive"></i> Clôturer la période
                </button>
                <?php endif; ?>
                <button class="btn btn-outline-danger w-100" onclick="deletePeriod(<?= $activePeriod['id'] ?>)">
                    <i class="bi bi-trash"></i> Supprimer
                </button>
            </div>
        </div>
    </div>
</div>

<?php endif; ?>

<!-- New Period Modal -->
<div class="modal fade" id="newPeriodModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header"><h5><i class="bi bi-calendar-plus"></i> Nouvelle période</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
            <form id="newPeriodForm">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Type de période</label>
                        <select name="type" class="form-select" id="periodTypeSelect" required>
                            <option value="daily">Journalière</option>
                            <option value="weekly">Hebdomadaire</option>
                            <option value="monthly" selected>Mensuelle</option>
                            <option value="yearly">Annuelle</option>
                            <option value="custom">Personnalisée</option>
                        </select>
                    </div>
                    <div class="row" id="customDates" style="display:none;">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Date début</label>
                            <input type="date" name="start_date" class="form-control">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Date fin</label>
                            <input type="date" name="end_date" class="form-control">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Nom (optionnel)</label>
                        <input type="text" name="nom" class="form-control" placeholder="Ex: Vacances été 2026">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                    <button type="submit" class="btn btn-primary"><i class="bi bi-check-lg"></i> Créer</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Add Revenue Modal -->
<div class="modal fade" id="addRevenueModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header"><h5><i class="bi bi-cash-stack"></i> Nouveau revenu</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
            <form id="addRevenueForm">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Catégorie de revenu</label>
                        <select name="category_id" class="form-select" required>
                            <?php foreach (\App\Models\Revenue::getRevenueCategories() as $rc): ?>
                            <option value="<?= $rc['id'] ?>"><?= htmlspecialchars($rc['nom']) ?> (<?= $rc['type'] ?>)</option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Montant (€)</label>
                        <input type="number" step="0.01" name="montant" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Source / Employeur</label>
                        <input type="text" name="source" class="form-control" placeholder="Ex: Entreprise ABC, Client X">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Date de réception</label>
                        <input type="date" name="date_revenu" class="form-control" value="<?= date('Y-m-d') ?>" required>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Compte de destination</label>
                            <select name="account_id" class="form-select">
                                <option value="">Sélectionner...</option>
                                <?php foreach ($accounts as $a): ?>
                                <option value="<?= $a['id'] ?>"><?= htmlspecialchars($a['nom']) ?> (<?= number_format($a['solde_actuel'], 0) ?>€)</option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Récurrent ?</label>
                            <select name="est_recurrent" class="form-select" onchange="document.getElementById('freqField').style.display=this.value==='1'?'block':'none'">
                                <option value="0">Non</option>
                                <option value="1">Oui</option>
                            </select>
                        </div>
                    </div>
                    <div id="freqField" style="display:none;">
                        <div class="mb-3">
                            <label class="form-label">Fréquence</label>
                            <select name="frequence" class="form-select">
                                <option value="mensuel">Mensuel</option>
                                <option value="trimestriel">Trimestriel</option>
                                <option value="annuel">Annuel</option>
                            </select>
                        </div>
                    </div>
                    <input type="hidden" name="period_id" value="<?= $activePeriod['id'] ?? '' ?>">
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                    <button type="submit" class="btn btn-success"><i class="bi bi-check-lg"></i> Ajouter</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.getElementById('periodTypeSelect').addEventListener('change', function() {
    document.getElementById('customDates').style.display = this.value === 'custom' ? 'flex' : 'none';
});

document.getElementById('newPeriodForm').addEventListener('submit', async (e) => {
    e.preventDefault();
    const data = new FormData(e.target);
    const r = await apiFetch('api/budget.php?action=create_period', {method:'POST', body:data});
    if (r?.success) { showToast('success', 'Période créée'); location.href='budget.php?period_id='+r.id; }
    else if (r) showToast('danger', r.error||'Erreur');
});

document.getElementById('addRevenueForm').addEventListener('submit', async (e) => {
    e.preventDefault();
    const data = new FormData(e.target);
    const r = await apiFetch('api/budget.php?action=add_revenue', {method:'POST', body:data});
    if (r?.success) { showToast('success', 'Revenu ajouté !'); location.reload(); }
    else if (r) showToast('danger', r.error||'Erreur');
});

async function deleteRevenue(id) {
    if (!confirm('Supprimer ce revenu ?')) return;
    const data = new FormData(); data.append('id', id);
    const r = await apiFetch('api/budget.php?action=delete_revenue', {method:'POST', body:data});
    if (r?.success) location.reload();
}

async function cloturerPeriod(id) {
    if (!confirm('Clôturer cette période ?')) return;
    const data = new FormData(); data.append('id', id);
    const r = await apiFetch('api/budget.php?action=cloturer_period', {method:'POST', body:data});
    if (r?.success) { showToast('success', 'Période clôturée'); location.reload(); }
}

async function deletePeriod(id) {
    if (!confirm('Supprimer définitivement cette période et toutes ses dépenses ?')) return;
    const data = new FormData(); data.append('id', id);
    const r = await apiFetch('api/budget.php?action=delete_period', {method:'POST', body:data});
    if (r?.success) { showToast('success', 'Période supprimée'); location.reload(); }
}

const form = document.getElementById('previsionsForm');
if (form) {
    form.addEventListener('submit', async (e) => {
        e.preventDefault();
        const inputs = form.querySelectorAll('.prev-input');
        const previsions = [];
        inputs.forEach(inp => {
            const val = parseFloat(inp.value) || 0;
            if (val > 0) previsions.push({category_id: parseInt(inp.dataset.category), montant: val});
        });
        const data = new FormData();
        data.append('period_id', form.querySelector('[name=period_id]').value);
        data.append('previsions', JSON.stringify(previsions));
        const r = await apiFetch('api/budget.php?action=save_previsions_period', {method:'POST', body:data});
        if (r?.success) showToast('success', 'Prévisions enregistrées');
        else if (r) showToast('danger', r.error||'Erreur');
    });
}

function showAddRevenueModal() { new bootstrap.Modal(document.getElementById('addRevenueModal')).show(); }
</script>

<?php include __DIR__ . '/../layouts/footer.php'; ?>