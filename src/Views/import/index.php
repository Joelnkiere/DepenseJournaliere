<?php include __DIR__ . '/../layouts/header.php';
$categories = \App\Models\Category::getAll();
?>
<div class="row mb-3">
    <div class="col"><h3><i class="bi bi-upload"></i> Import CSV</h3></div>
</div>
<div class="card">
    <div class="card-body">
        <p class="text-muted">Importez un fichier CSV de votre banque. Format attendu : Date, Description, Montant (négatif pour les dépenses), Catégorie (optionnelle).</p>
        <form id="importForm" enctype="multipart/form-data">
            <div class="row g-3">
                <div class="col-md-4">
                    <label class="form-label">Fichier CSV</label>
                    <input type="file" name="csv_file" class="form-control" accept=".csv" required>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Colonne Date</label>
                    <select name="col_date" class="form-select"><option value="0">1</option><option value="1">2</option><option value="2">3</option><option value="3">4</option><option value="4">5</option></select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Colonne Description</label>
                    <select name="col_desc" class="form-select"><option value="1">2</option><option value="0">1</option><option value="2">3</option><option value="3">4</option><option value="4">5</option></select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Colonne Montant</label>
                    <select name="col_montant" class="form-select"><option value="2">3</option><option value="0">1</option><option value="1">2</option><option value="3">4</option><option value="4">5</option></select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Catégorie par défaut</label>
                    <select name="default_category" class="form-select">
                        <?php foreach ($categories as $c): ?>
                        <option value="<?= $c['id'] ?>"><?= htmlspecialchars($c['nom']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Mois d'assignation</label>
                    <input type="month" name="mois" class="form-control" value="<?= date('Y-m') ?>">
                </div>
                <div class="col-md-2 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary w-100"><i class="bi bi-upload"></i> Importer</button>
                </div>
            </div>
        </form>
        <div id="importResult" class="mt-3" style="display:none;"></div>
    </div>
</div>
<script>
document.getElementById('importForm').addEventListener('submit', async (e) => {
    e.preventDefault();
    const data = new FormData(e.target);
    const btn = e.target.querySelector('button[type="submit"]');
    btn.disabled = true; btn.innerHTML = '<span class="spinner-border spinner-border-sm"></span> Import...';
    const r = await apiFetch('api/import.php?action=csv', {method:'POST', body:data});
    btn.disabled = false; btn.innerHTML = '<i class="bi bi-upload"></i> Importer';
    const div = document.getElementById('importResult');
    if (r?.success) {
        div.className = 'alert alert-success mt-3';
        div.innerHTML = `<i class="bi bi-check-circle"></i> ${r.message} (${r.imported} importées, ${r.skipped} ignorées)`;
    } else if (r) {
        div.className = 'alert alert-danger mt-3';
        div.innerHTML = `<i class="bi bi-exclamation-triangle"></i> ${r.error||'Erreur'}`;
    }
    div.style.display = 'block';
});
</script>
<?php include __DIR__ . '/../layouts/footer.php'; ?>