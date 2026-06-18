<?php include __DIR__ . '/../layouts/header.php';

$goals = \App\Models\SavingsGoal::getByUser($userId);
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
                            <li><a class="dropdown-item" href="#" onclick="showAddFundsModal(<?= $goal['id'] ?>, '<?= htmlspecialchars($goal['titre']) ?>')"><i class="bi bi-plus-circle"></i> Ajouter des fonds</a></li>
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
                <small class="text-muted"><i class="bi bi-calendar"></i> Limite : <?= date('d/m/Y', strtotime($goal['date_limite'])) ?></small>
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
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                    <button type="submit" class="btn btn-warning"><i class="bi bi-check-lg"></i> Créer</button>
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
document.addEventListener('DOMContentLoaded', () => {
    document.getElementById('addGoalForm').addEventListener('submit', async (e) => {
        e.preventDefault();
        const form = e.target;
        const data = new FormData(form);
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
});

function showAddGoalModal() {
    const modal = new bootstrap.Modal(document.getElementById('addGoalModal'));
    modal.show();
}

function showAddFundsModal(id, titre) {
    document.getElementById('fundsGoalId').value = id;
    document.getElementById('fundsGoalTitle').textContent = titre;
    const modal = new bootstrap.Modal(document.getElementById('addFundsModal'));
    modal.show();
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