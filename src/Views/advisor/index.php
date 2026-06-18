<?php include __DIR__ . '/../layouts/header.php';

use App\Helpers\FinancialAdvisor;
use App\Models\Budget;
use App\Models\Expense;

$mois = $_GET['mois'] ?? date('Y-m');
$budget = Budget::getCurrent($userId, $mois);
$conseils = [];
$resteAVivre = 0;

if ($budget && $budget['revenu_mensuel'] > 0) {
    $previsions = Budget::getPrevisions($budget['id']);
    $depensesReelles = Budget::getReelByCategory($budget['id']);
    $advisor = new FinancialAdvisor($budget['revenu_mensuel'], $depensesReelles, $previsions);
    $conseils = $advisor->getAdvice();
    $totalDepense = Expense::getTotalByBudget($budget['id']);
    $resteAVivre = $budget['revenu_mensuel'] - $totalDepense;
}
?>

<div class="row mb-3">
    <div class="col">
        <h3><i class="bi bi-lightbulb text-warning"></i> Conseiller Financier</h3>
    </div>
    <div class="col-auto">
        <form method="GET" class="d-flex align-items-center">
            <label class="me-2 text-muted small">Mois :</label>
            <input type="month" name="mois" class="form-control form-control-sm" value="<?= $mois ?>" onchange="this.form.submit()">
        </form>
    </div>
</div>

<?php if (!$budget || $budget['revenu_mensuel'] <= 0): ?>
<div class="card">
    <div class="card-body text-center py-5">
        <i class="bi bi-currency-exchange display-1 text-muted"></i>
        <h4 class="text-muted mt-3">Configurez votre revenu mensuel</h4>
        <p class="text-muted">Rendez-vous dans l'onglet Budget pour définir votre revenu et activer le conseiller.</p>
        <a href="budget.php" class="btn btn-primary"><i class="bi bi-piggy-bank"></i> Configurer le budget</a>
    </div>
</div>
<?php else: ?>

<div class="row g-3">
    <div class="col-md-6">
        <div class="card h-100">
            <div class="card-header"><i class="bi bi-pie-chart"></i> Analyse 50/30/20</div>
            <div class="card-body">
                <?php $regle = $conseils['regle_50_30_20']; ?>
                <div class="mb-4">
                    <div class="d-flex justify-content-between mb-1">
                        <span class="fw-bold">Besoins <span class="text-muted">(50% idéal)</span></span>
                        <span class="<?= $regle['besoins_pct'] > 50 ? 'text-danger' : 'text-success' ?>"><?= $regle['besoins_pct'] ?>%</span>
                    </div>
                    <div class="progress" style="height:20px">
                        <div class="progress-bar bg-primary" style="width:<?= min($regle['besoins_pct'], 100) ?>%">
                            <?= $regle['besoins_pct'] ?>%
                        </div>
                    </div>
                </div>
                <div class="mb-4">
                    <div class="d-flex justify-content-between mb-1">
                        <span class="fw-bold">Envie <span class="text-muted">(30% idéal)</span></span>
                        <span class="<?= $regle['envies_pct'] > 30 ? 'text-danger' : 'text-success' ?>"><?= $regle['envies_pct'] ?>%</span>
                    </div>
                    <div class="progress" style="height:20px">
                        <div class="progress-bar bg-success" style="width:<?= min($regle['envies_pct'], 100) ?>%">
                            <?= $regle['envies_pct'] ?>%
                        </div>
                    </div>
                </div>
                <div class="mb-3">
                    <div class="d-flex justify-content-between mb-1">
                        <span class="fw-bold">Épargne <span class="text-muted">(20% idéal)</span></span>
                        <span class="<?= $regle['epargne_pct'] < 20 ? 'text-danger' : 'text-success' ?>"><?= $regle['epargne_pct'] ?>%</span>
                    </div>
                    <div class="progress" style="height:20px">
                        <div class="progress-bar bg-info" style="width:<?= min($regle['epargne_pct'], 100) ?>%">
                            <?= $regle['epargne_pct'] ?>%
                        </div>
                    </div>
                </div>
                <div class="alert alert-<?= $regle['statut'] ?>">
                    <?= htmlspecialchars($regle['message']) ?>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-6">
        <div class="card h-100">
            <div class="card-header"><i class="bi bi-graph-up"></i> Prédiction de fin de mois</div>
            <div class="card-body">
                <?php $pred = $conseils['prediction_fin_mois']; ?>
                <div class="alert alert-<?= $pred['statut'] ?? 'info' ?>">
                    <h5 class="alert-heading">
                        <?php if (($pred['statut'] ?? '') === 'danger'): ?>
                        <i class="bi bi-exclamation-triangle"></i> Alerte
                        <?php elseif (($pred['statut'] ?? '') === 'success'): ?>
                        <i class="bi bi-check-circle"></i> Bonne gestion
                        <?php else: ?>
                        <i class="bi bi-info-circle"></i> Information
                        <?php endif; ?>
                    </h5>
                    <p class="mb-0"><?= htmlspecialchars($pred['message'] ?? '') ?></p>
                </div>

                <?php if (isset($pred['projection'])): ?>
                <div class="row text-center g-2 mt-3">
                    <div class="col-6">
                        <div class="card bg-body-tertiary">
                            <div class="card-body py-2">
                                <small class="text-muted">Projection</small>
                                <h5 class="text-warning mb-0"><?= number_format($pred['projection'], 2) ?>€</h5>
                            </div>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="card bg-body-tertiary">
                            <div class="card-body py-2">
                                <small class="text-muted">Reste prévu</small>
                                <h5 class="<?= ($pred['reste_prevu'] ?? 0) >= 0 ? 'text-success' : 'text-danger' ?> mb-0">
                                    <?= number_format($pred['reste_prevu'] ?? 0, 2) ?>€
                                </h5>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <div class="col-md-6">
        <div class="card h-100">
            <div class="card-header"><i class="bi bi-lightbulb text-warning"></i> Astuce du jour</div>
            <div class="card-body">
                <?php $astuce = $conseils['astuce_du_jour'] ?? []; ?>
                <?php if (!empty($astuce['astuce'])): ?>
                <div class="d-flex align-items-start">
                    <i class="bi bi-bulb text-warning fs-1 me-3"></i>
                    <div>
                        <h5 class="text-warning">Catégorie : <?= htmlspecialchars($astuce['categorie'] ?? '') ?></h5>
                        <p class="mb-0"><?= htmlspecialchars($astuce['astuce']) ?></p>
                        <?php if (!empty($astuce['montant']) && $astuce['montant'] > 0): ?>
                        <small class="text-muted">Montant dépensé : <?= number_format($astuce['montant'], 2) ?>€</small>
                        <?php endif; ?>
                    </div>
                </div>
                <?php else: ?>
                <p class="text-muted">Ajoutez des dépenses pour obtenir des astuces personnalisées.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <div class="col-md-6">
        <div class="card h-100">
            <div class="card-header"><i class="bi bi-exclamation-triangle text-warning"></i> Alertes catégories</div>
            <div class="card-body">
                <?php $alertes = $conseils['alertes_categories'] ?? []; ?>
                <?php if (!empty($alertes)): ?>
                <?php foreach ($alertes as $alerte): ?>
                <div class="alert alert-warning d-flex align-items-center">
                    <i class="bi bi-exclamation-circle me-2"></i>
                    <div>
                        <strong><?= htmlspecialchars($alerte['categorie']) ?></strong><br>
                        <small><?= htmlspecialchars($alerte['message']) ?></small>
                    </div>
                </div>
                <?php endforeach; ?>
                <?php else: ?>
                <div class="text-center py-4">
                    <i class="bi bi-emoji-smile display-4 text-success"></i>
                    <p class="text-muted mt-2">Toutes les catégories sont dans le vert !</p>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php endif; ?>

<?php include __DIR__ . '/../layouts/footer.php'; ?>