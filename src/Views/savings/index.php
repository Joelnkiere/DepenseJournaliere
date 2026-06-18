<?php include __DIR__ . '/../layouts/header.php';

$goals = \App\Models\SavingsGoal::getByUser($userId);
$accounts = \App\Models\Account::getByUser($userId);
?>

<div class="row mb-3">
    <div class="col">
        <h3><i class="bi bi-graph-up-arrow"></i> Objectifs d'épargne</h3>
    </div>
    <div class="col-auto">
        <button class="btn btn-warning" onclick="showAddGoalModal()">
            <i class="bi bi-plus-lg"></i> Nouvel objectif
        </button>
    </div>
</div>

<div class="row g-3" id="goalsContainer">
    <?php if (empty($goals)): ?>
    <div class="col-12">
        <div class="card">
            <div class="card-body text-center py-5">
                <i class="bi bi-piggy-bank display-1 text-muted"></i>
                <h4 class="text-muted mt-3">Aucun objectif d'épargne</h4>
                <p class="text-muted">Créez votre premier objectif pour commencer à épargner.</p>
                <button class="btn btn-warning btn-lg" onclick="showAddGoalModal()">
                    <i class="bi bi-plus-circle"></i> Créer un objectif
                </button>
            </div>
        </div>
    </div>
    <?php else: ?>
    <?php foreach ($goals as $goal): 
        $pct = $goal['montant_cible'] > 0 ? min(100, round(($goal['montant_actuel'] / $goal['montant_cible']) * 100, 1)) : 0;
    ?>
    <div class="col-md-6 col-lg-4">
        <div class="card h-100" id="goal-<?= $goal['id'] ?>">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start mb-3">
                    <h5 class="card-title"><?= htmlspecialchars($goal['titre']) ?></h5>
                    <div class="dropdown">
                        <button class="btn btn-sm btn-outline-secondary" data-bs-toggle="dropdown"><i class="bi bi-three-dots"></i></button>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li><a class="dropdown-item" href="#" onclick="showAddFundsModal(<?= $goal['id'] ?>, '<?= htmlspecialchars($goal['titre'], ENT_QUOTES) ?>')"><i class="bi bi-plus-circle"></i> Ajouter des fonds</a></li>
                            <li><a class="dropdown-item" href="#" onclick="showEditGoalModal(<?= $goal['id'] ?>)"><i class="bi bi-pencil"></i> Modifier</a></li>
                            <li><a class="dropdown-item" href="#" onclick="deleteGoal(<?= $goal['id'] ?>)"><i class="bi bi-trash text-danger"></i> Supprimer</a></li>
                        </ul>
                    </div>
                </div>
                <div class="mb-2">
                    <div class="d-flex justify-content-between">
                        <span class="text-muted small">Progression</span>
                        <span class="fw-bold"><?= $pct ?>%</span>
                    </div>
                    <div class="progress" style="height: 15px;">
                        <div class="progress-bar progress-bar-striped bg-warning" style="width: <?= $pct ?>%">
                            <?= $pct ?>%
                        </div>
                    </div>
                </div>
                <div class="d-flex justify-content-between mb-2">
                    <span><i class="bi bi-piggy-bank"></i> <?= number_format($goal['montant_actuel'], 2) ?>€</span>
                    <span class="text-muted">/ <?= number_format($goal['montant_cible'], 2) ?>€</span>
                </div>
                <?php if ($goal['date_limite']): ?>
                <small class="text-muted"><i class="bi bi-calendar"></i> Limite : <?= date('d/m/Y', strtotime($goal['date_limite'])) ?></small><br>
                <?php endif; ?>
                <?php if ($goal['account_nom']): ?>
                <small class="text-muted"><i class="bi bi-wallet2"></i> Compte lié : <?= htmlspecialchars($goal['account_nom']) ?></small><br>
                <?php endif; ?>
                <?php if ($goal['auto_save_type'] !== 'none'): ?>
                <small class="text-info"><i class="bi bi-arrow-repeat"></i> Auto-save : <?= $goal['auto_save_value'] ?><?= $goal['auto_save_type'] === 'percentage' ? '%' : '€' ?> / <?= $goal['auto_save_frequence'] ?></small>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <?php endforeach; ?>
    <?php endif; ?>
</div>

<!-- Add Goal Modal -->
<div class="modal fade" id="addGoalModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="bi bi-trophy"></i> Nouvel objectif d'épargne</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="addGoalForm">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Titre</label>
                        <input type="text" name="titre" class="form-control" placeholder="Ex: Achat PC, Voyage, ..." required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Montant cible (€)</label>
                        <input type="number" step="0.01" name="montant_cible" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Date limite</label>
                        <input type="date" name="date_limite" class="form-control">
                    </div>
                    <hr>
                    <h6><i class="bi bi-wallet2"></i> Compte lié (optionnel)</h6>
                    <div class="mb-3">
                        <label class="form-label">Compte pour prélèvement</label>
                        <select name="account_id" class="form-select">
                            <option value="">Aucun</option>
                            <?php foreach ($accounts as $a): ?>
                            <option value="<?= $a['id'] ?>"><?= htmlspecialchars($a['nom']) ?> (<?= number_format($a['solde_actuel'], 0) ?>€)</option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <h6><i class="bi bi-arrow-repeat"></i> Épargne automatique</h6>
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Type</label>
                            <select name="auto_save_type" class="form-select" onchange="document.getElementById('autoSaveFields').style.display=this.value!=='none'?'flex':'none'">
                                <option value="none">Désactivé</option>
                                <option value="percentage">Pourcentage (%)</option>
                                <option value="fixed">Montant fixe (€)</option>
                            </select>
                        </div>
                        <div id="autoSaveFields" style="display:none;" class="row col-md-8">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Valeur</label>
                                <input type="number" step="0.01" name="auto_save_value" class="form-control" value="10">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Fréquence</label>
                                <select name="auto_save_frequence" class="form-select">
                                    <option value="mensuel">Mensuel</option>
                                    <option value="trimestriel">Trimestriel</option>
                                    <option value="annuel">Annuel</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                    <button type="submit" class="btn btn-warning"><i class="bi bi-check-lg"></i> Créer</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Goal Modal -->
<div class="modal fade" id="editGoalModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="bi bi-pencil"></i> Modifier l'objectif</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="editGoalForm">
                <div class="modal-body">
                    <input type="hidden" name="id" id="editGoalId">
                    <div class="mb-3">
                        <label class="form-label">Titre</label>
                        <input type="text" name="titre" id="editGoalTitre" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Montant cible (€)</label>
                        <input type="number" step="0.01" name="montant_cible" id="editGoalMontant" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Date limite</label>
                        <input type="date" name="date_limite" id="editGoalDate" class="form-control">
                    </div>
                    <hr>
                    <div class="mb-3">
                        <label class="form-label">Compte lié</label>
                        <select name="account_id" id="editGoalAccount" class="form-select">
                            <option value="">Aucun</option>
                            <?php foreach ($accounts as $a): ?>
                            <option value="<?= $a['id'] ?>"><?= htmlspecialchars($a['nom']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Auto-save</label>
                            <select name="auto_save_type" id="editGoalAutoType" class="form-select">
                                <option value="none">Désactivé</option>
                                <option value="percentage">%</option>
                                <option value="fixed">Fixé</option>
                            </select>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Valeur</label>
                            <input type="number" step="0.01" name="auto_save_value" id="editGoalAutoValue" class="form-control">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Fréquence</label>
                            <select name="auto_save_frequence" id="editGoalAutoFreq" class="form-select">
                                <option value="mensuel">Mensuel</option>
                                <option value="trimestriel">Trimestriel</option>
                                <option value="annuel">Annuel</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                    <button type="submit" class="btn btn-warning"><i class="bi bi-check-lg"></i> Enregistrer</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Add Funds Modal -->
<div class="modal fade" id="addFundsModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="bi bi-plus-circle"></i> Ajouter des fonds</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="addFundsForm">
                <div class="modal-body">
                    <p>Objectif : <strong id="fundsGoalTitle"></strong></p>
                    <div class="mb-3">
                        <label class="form-label">Montant à ajouter (€)</label>
                        <input type="number" step="0.01" name="montant" class="form-control form-control-lg" required>
                    </div>
                    <input type="hidden" name="id" id="fundsGoalId">
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                    <button type="submit" class="btn btn-warning"><i class="bi bi-check-lg"></i> Ajouter</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.getElementById('addGoalForm').addEventListener('submit', async (e) => {
    e.preventDefault();
    const data = new FormData(e.target);
    const resp = await fetch('api/savings.php?action=add', { method: 'POST', body: data });
    const result = await resp.json();
    if (result.success) {
        showToast('success', 'Objectif créé !');
        bootstrap.Modal.getInstance(document.getElementById('addGoalModal')).hide();
        setTimeout(() => location.reload(), 500);
    } else {
        showToast('danger', result.error || 'Erreur');
    }
});

document.getElementById('editGoalForm').addEventListener('submit', async (e) => {
    e.preventDefault();
    const data = new FormData(e.target);
    const resp = await fetch('api/savings.php?action=update', { method: 'POST', body: data });
    const result = await resp.json();
    if (result.success) {
        showToast('success', 'Objectif mis à jour');
        bootstrap.Modal.getInstance(document.getElementById('editGoalModal')).hide();
        setTimeout(() => location.reload(), 500);
    } else {
        showToast('danger', result.error || 'Erreur');
    }
});

document.getElementById('addFundsForm').addEventListener('submit', async (e) => {
    e.preventDefault();
    const form = e.target;
    const data = new FormData(form);
    const resp = await fetch('api/savings.php?action=add_funds', { method: 'POST', body: data });
    const result = await resp.json();
    if (result.success) {
        showToast('success', 'Fonds ajoutés !');
        bootstrap.Modal.getInstance(document.getElementById('addFundsModal')).hide();
        setTimeout(() => location.reload(), 500);
    } else {
        showToast('danger', result.error || 'Erreur');
    }
});

function showAddGoalModal() {
    new bootstrap.Modal(document.getElementById('addGoalModal')).show();
}

function showEditGoalModal(id) {
    fetch('api/savings.php?action=get&id=' + id)
        .then(r => r.json())
        .then(g => {
            document.getElementById('editGoalId').value = g.id;
            document.getElementById('editGoalTitre').value = g.titre;
            document.getElementById('editGoalMontant').value = g.montant_cible;
            document.getElementById('editGoalDate').value = g.date_limite || '';
            document.getElementById('editGoalAccount').value = g.account_id || '';
            document.getElementById('editGoalAutoType').value = g.auto_save_type || 'none';
            document.getElementById('editGoalAutoValue').value = g.auto_save_value || 0;
            document.getElementById('editGoalAutoFreq').value = g.auto_save_frequence || 'mensuel';
            new bootstrap.Modal(document.getElementById('editGoalModal')).show();
        });
}

function showAddFundsModal(id, titre) {
    document.getElementById('fundsGoalId').value = id;
    document.getElementById('fundsGoalTitle').textContent = titre;
    new bootstrap.Modal(document.getElementById('addFundsModal')).show();
}

async function deleteGoal(id) {
    if (!confirm('Supprimer cet objectif ?')) return;
    const data = new FormData();
    data.append('id', id);
    const resp = await fetch('api/savings.php?action=delete', { method: 'POST', body: data });
    const result = await resp.json();
    if (result.success) {
        showToast('success', 'Objectif supprimé');
        document.getElementById(`goal-${id}`)?.remove();
    }
}
</script>

<?php include __DIR__ . '/../layouts/footer.php'; ?>