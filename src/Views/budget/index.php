<?php include __DIR__ . '/../layouts/header.php';

$userId = \App\Core\Session::get('user_id');
$mois = $_GET['mois'] ?? date('Y-m');
$budget = \App\Models\Budget::getCurrent($userId, $mois);
$allBudgets = \App\Models\Budget::getAllByUser($userId);
$categories = \App\Models\Category::getAll();
$previsions = $budget ? \App\Models\Budget::getPrevisions($budget['id']) : [];
?>

<div class="row mb-3">
    <div class="col">
        <h3><i class="bi bi-piggy-bank"></i> Configuration du budget</h3>
    </div>
    <div class="col-auto">
        <form method="GET" class="d-flex align-items-center">
            <label class="me-2 text-muted small">Mois :</label>
            <input type="month" name="mois" class="form-control form-control-sm" value="<?= $mois ?>" onchange="this.form.submit()">
        </form>
    </div>
</div>

<div class="row g-3">
    <div class="col-md-5">
        <div class="card">
            <div class="card-header"><i class="bi bi-cash-stack"></i> Revenu mensuel</div>
            <div class="card-body">
                <form id="revenuForm">
                    <div class="mb-3">
                        <label class="form-label">Revenu du mois (€)</label>
                        <input type="number" step="0.01" name="revenu" class="form-control form-control-lg"
                               value="<?= $budget['revenu_mensuel'] ?? 0 ?>" required>
                        <input type="hidden" name="mois" value="<?= $mois ?>">
                    </div>
                    <button type="submit" class="btn btn-primary w-100"><i class="bi bi-save"></i> Enregistrer</button>
                </form>
            </div>
        </div>

        <div class="card mt-3">
            <div class="card-header"><i class="bi bi-clock-history"></i> Historique des budgets</div>
            <div class="card-body p-0">
                <ul class="list-group list-group-flush">
                    <?php foreach ($allBudgets as $b): ?>
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                        <span>
                            <?= htmlspecialchars($b['mois']) ?>
                            <?php if ($b['cloture']): ?>
                            <span class="badge bg-success ms-1">Clôturé</span>
                            <?php endif; ?>
                        </span>
                        <span><?= number_format($b['revenu_mensuel'], 0) ?>€</span>
                    </li>
                    <?php endforeach; ?>
                </ul>
            </div>
        </div>
    </div>

    <div class="col-md-7">
        <div class="card">
            <div class="card-header"><i class="bi bi-sliders"></i> Budget prévisionnel par catégorie</div>
            <div class="card-body">
                <form id="previsionsForm">
                    <div class="row g-3">
                        <?php foreach ($categories as $cat): 
                            $prevMontant = 0;
                            foreach ($previsions as $p) {
                                if ((int)$p['category_id'] === (int)$cat['id']) {
                                    $prevMontant = (float)$p['montant_prevu'];
                                    break;
                                }
                            }
                        ?>
                        <div class="col-md-6">
                            <label class="form-label small"><?= htmlspecialchars($cat['nom']) ?>
                                <span class="badge bg-<?= $cat['type'] === 'besoin' ? 'primary' : ($cat['type'] === 'envie' ? 'success' : 'info') ?>"><?= $cat['type'] ?></span>
                            </label>
                            <div class="input-group">
                                <span class="input-group-text">€</span>
                                <input type="number" step="0.01" class="form-control prev-input"
                                       data-category="<?= $cat['id'] ?>" value="<?= $prevMontant ?>">
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <input type="hidden" name="mois" value="<?= $mois ?>">
                    <button type="submit" class="btn btn-primary mt-3"><i class="bi bi-save"></i> Enregistrer</button>
                </form>
            </div>
        </div>

        <?php if ($budget && !$budget['cloture']): ?>
        <div class="card mt-3 border-warning">
            <div class="card-body text-center">
                <p class="mb-2">Une fois le mois terminé, vous pouvez clôturer pour archiver.</p>
                <button class="btn btn-warning" onclick="cloturerMois('<?= $mois ?>')">
                    <i class="bi bi-archive"></i> Clôturer le mois
                </button>
            </div>
        </div>
        <?php endif; ?>
    </div>
</div>

<script>
const moisActuel = '<?= $mois ?>';
</script>

<?php include __DIR__ . '/../layouts/footer.php'; ?>