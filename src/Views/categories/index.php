<?php include __DIR__ . '/../layouts/header.php';
$categories = \App\Models\Category::getAll();
$templates = \App\Models\BudgetTemplate::getByUser($userId);
$mois = $_GET['mois'] ?? date('Y-m');
$budget = \App\Models\Budget::getCurrent($userId, $mois);
?>
<div class="row mb-3">
    <div class="col"><h3><i class="bi bi-tags"></i> Gestion des catégories</h3></div>
</div>
<div class="row g-3">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">Catégories existantes</div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-dark"><tr><th>Nom</th><th>Type</th></tr></thead>
                        <tbody>
                            <?php foreach ($categories as $c): ?>
                            <tr><td><?= htmlspecialchars($c['nom']) ?></td><td><span class="badge bg-<?= $c['type']==='besoin'?'primary':($c['type']==='envie'?'success':'info') ?>"><?= $c['type'] ?></span></td></tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card">
            <div class="card-header"><i class="bi bi-file-earmark"></i> Templates de budget</div>
            <div class="card-body">
                <form id="templateSaveForm" class="mb-3">
                    <div class="input-group">
                        <input type="text" name="nom" class="form-control" placeholder="Nom du template" required>
                        <input type="hidden" name="mois" value="<?= $mois ?>">
                        <button type="submit" class="btn btn-outline-primary"><i class="bi bi-save"></i> Sauvegarder</button>
                    </div>
                    <small class="text-muted">Sauvegarde les prévisions du mois en cours comme template</small>
                </form>
                <hr>
                <h6>Templates sauvegardés</h6>
                <?php if (empty($templates)): ?>
                <p class="text-muted">Aucun template</p>
                <?php else: foreach ($templates as $t): ?>
                <div class="d-flex justify-content-between align-items-center mb-2 p-2 bg-body-tertiary rounded">
                    <span><strong><?= htmlspecialchars($t['nom']) ?></strong></span>
                    <div>
                        <button class="btn btn-sm btn-outline-success" onclick="applyTemplate(<?= $t['id'] ?>)"><i class="bi bi-upload"></i></button>
                        <button class="btn btn-sm btn-outline-danger" onclick="deleteTemplate(<?= $t['id'] ?>)"><i class="bi bi-trash"></i></button>
                    </div>
                </div>
                <?php endforeach; endif; ?>
            </div>
        </div>
    </div>
</div>
<script>
document.getElementById('templateSaveForm').addEventListener('submit', async (e)=>{
    e.preventDefault();
    const data = new FormData(e.target);
    const r = await apiFetch('api/templates.php?action=save', {method:'POST', body:data});
    if (r?.success) { showToast('success', 'Template sauvegardé'); location.reload(); }
    else if (r) showToast('danger', r.error||'Erreur');
});
async function applyTemplate(id) {
    if (!confirm('Appliquer ce template au mois en cours ?')) return;
    const data = new FormData(); data.append('id', id); data.append('mois', '<?= $mois ?>');
    const r = await apiFetch('api/templates.php?action=apply', {method:'POST', body:data});
    if (r?.success) { showToast('success', 'Template appliqué'); location.reload(); }
}
async function deleteTemplate(id) {
    if (!confirm('Supprimer ce template ?')) return;
    const data = new FormData(); data.append('id', id);
    const r = await apiFetch('api/templates.php?action=delete', {method:'POST', body:data});
    if (r?.success) { showToast('success', 'Template supprimé'); location.reload(); }
}
</script>
<?php include __DIR__ . '/../layouts/footer.php'; ?>