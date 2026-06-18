<?php include __DIR__ . '/../layouts/header.php';
$recurring = \App\Models\RecurringTransaction::getByUser($userId);
$categories = \App\Models\Category::getAll();
?>
<div class="row mb-3">
    <div class="col"><h3><i class="bi bi-arrow-repeat"></i> Transactions récurrentes</h3></div>
    <div class="col-auto"><button class="btn btn-primary" onclick="showRecurringModal()"><i class="bi bi-plus-lg"></i> Nouvelle</button></div>
</div>
<div class="card">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-dark">
                    <tr><th>Description</th><th>Catégorie</th><th>Montant</th><th>Fréquence</th><th>Prochaine exécution</th><th>Statut</th><th>Actions</th></tr>
                </thead>
                <tbody>
                    <?php if (empty($recurring)): ?>
                    <tr><td colspan="7" class="text-center text-muted py-4">Aucune transaction récurrente</td></tr>
                    <?php else: foreach ($recurring as $r): ?>
                    <tr>
                        <td><?= htmlspecialchars($r['description']?:'-') ?></td>
                        <td><span class="badge bg-secondary"><?= htmlspecialchars($r['category_nom']) ?></span></td>
                        <td class="text-danger fw-bold"><?= number_format($r['montant'],2) ?>€</td>
                        <td><?= $r['frequence'] ?></td>
                        <td><?= date('d/m/Y', strtotime($r['prochaine_execution'])) ?></td>
                        <td><?= $r['actif'] ? '<span class="badge bg-success">Actif</span>' : '<span class="badge bg-secondary">Inactif</span>' ?></td>
                        <td>
                            <button class="btn btn-sm btn-outline-<?= $r['actif']?'warning':'success' ?>" onclick="toggleRecurring(<?= $r['id'] ?>, <?= $r['actif']?0:1 ?>)">
                                <i class="bi bi-<?= $r['actif']?'pause':'play' ?>"></i>
                            </button>
                            <button class="btn btn-sm btn-outline-danger" onclick="deleteRecurring(<?= $r['id'] ?>)"><i class="bi bi-trash"></i></button>
                        </td>
                    </tr>
                    <?php endforeach; endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<div class="modal fade" id="recurringModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header"><h5 class="modal-title"><i class="bi bi-arrow-repeat"></i> Nouvelle transaction récurrente</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
            <form id="recurringForm">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Catégorie</label>
                        <select name="category_id" class="form-select" required><?php foreach ($categories as $c): ?><option value="<?= $c['id'] ?>"><?= htmlspecialchars($c['nom']) ?></option><?php endforeach; ?></select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Montant (€)</label>
                        <input type="number" step="0.01" name="montant" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Description</label>
                        <input type="text" name="description" class="form-control" placeholder="Ex: Abonnement Netflix">
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Fréquence</label>
                            <select name="frequence" class="form-select">
                                <option value="mensuel">Mensuel</option>
                                <option value="bimestriel">Bimestriel</option>
                                <option value="trimestriel">Trimestriel</option>
                                <option value="annuel">Annuel</option>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Jour d'exécution</label>
                            <input type="number" name="jour_execution" class="form-control" min="1" max="28" value="1">
                        </div>
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
<script>
function showRecurringModal() { new bootstrap.Modal(document.getElementById('recurringModal')).show(); }
document.getElementById('recurringForm').addEventListener('submit', async (e) => {
    e.preventDefault();
    const data = new FormData(e.target);
    const r = await apiFetch('api/recurring.php?action=add', {method:'POST', body:data});
    if (r?.success) { showToast('success', 'Transaction récurrente créée'); location.reload(); }
    else if (r) showToast('danger', r.error||'Erreur');
});
async function toggleRecurring(id, actif) {
    const data = new FormData(); data.append('id', id); data.append('actif', actif);
    const r = await apiFetch('api/recurring.php?action=toggle', {method:'POST', body:data});
    if (r?.success) { showToast('success', 'Statut modifié'); location.reload(); }
}
async function deleteRecurring(id) {
    if (!confirm('Supprimer cette transaction récurrente ?')) return;
    const data = new FormData(); data.append('id', id);
    const r = await apiFetch('api/recurring.php?action=delete', {method:'POST', body:data});
    if (r?.success) { showToast('success', 'Supprimée'); location.reload(); }
}
</script>
<?php include __DIR__ . '/../layouts/footer.php'; ?>