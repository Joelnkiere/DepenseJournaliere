<?php include __DIR__ . '/../layouts/header.php'; 

use App\Models\Budget;
use App\Models\Expense;

$allBudgets = Budget::getAllByUser($userId);
$mois1 = $_GET['mois1'] ?? ($allBudgets[1]['mois'] ?? '');
$mois2 = $_GET['mois2'] ?? ($allBudgets[0]['mois'] ?? date('Y-m'));
$comparison = [];

if ($mois1 && $mois2 && $mois1 !== $mois2) {
    $comparison = Budget::getComparison($userId, $mois1, $mois2);
    $budget1 = Budget::getCurrent($userId, $mois1);
    $budget2 = Budget::getCurrent($userId, $mois2);
}
?>

<div class="row mb-3">
    <div class="col">
        <h3><i class="bi bi-clock-history"></i> Historique & Comparaison</h3>
    </div>
    <div class="col-auto">
        <a href="exports/export.php?mois=<?= date('Y-m') ?>&format=csv" class="btn btn-success btn-sm me-1">
            <i class="bi bi-filetype-csv"></i> CSV
        </a>
        <a href="exports/export.php?mois=<?= date('Y-m') ?>&format=pdf" class="btn btn-danger btn-sm">
            <i class="bi bi-filetype-pdf"></i> PDF
        </a>
    </div>
</div>

<div class="row g-3 mb-4">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header"><i class="bi bi-compare"></i> Comparer deux mois</div>
            <div class="card-body">
                <form method="GET" class="row g-2 align-items-end">
                    <div class="col-auto">
                        <label class="form-label small">Mois 1</label>
                        <input type="month" name="mois1" class="form-control" value="<?= $mois1 ?>" required>
                    </div>
                    <div class="col-auto">
                        <label class="form-label small">Mois 2</label>
                        <input type="month" name="mois2" class="form-control" value="<?= $mois2 ?>" required>
                    </div>
                    <div class="col-auto">
                        <button type="submit" class="btn btn-primary"><i class="bi bi-search"></i> Comparer</button>
                    </div>
                </form>

                <?php if (!empty($comparison) && count($comparison) >= 2): ?>
                <div class="table-responsive mt-3">
                    <table class="table table-bordered">
                        <thead class="table-dark">
                            <tr>
                                <th>Indicateur</th>
                                <th><?= $comparison[0]['mois'] ?></th>
                                <th><?= $comparison[1]['mois'] ?></th>
                                <th>Différence</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>Revenu</td>
                                <td><?= number_format((float)$comparison[0]['revenu_mensuel'], 2) ?>€</td>
                                <td><?= number_format((float)$comparison[1]['revenu_mensuel'], 2) ?>€</td>
                                <td class="<?= (float)$comparison[1]['revenu_mensuel'] >= (float)$comparison[0]['revenu_mensuel'] ? 'text-success' : 'text-danger' ?>">
                                    <?= number_format((float)$comparison[1]['revenu_mensuel'] - (float)$comparison[0]['revenu_mensuel'], 2) ?>€
                                </td>
                            </tr>
                            <tr>
                                <td>Total dépensé</td>
                                <td><?= number_format((float)$comparison[0]['total_depense'], 2) ?>€</td>
                                <td><?= number_format((float)$comparison[1]['total_depense'], 2) ?>€</td>
                                <td class="<?= (float)$comparison[1]['total_depense'] <= (float)$comparison[0]['total_depense'] ? 'text-success' : 'text-danger' ?>">
                                    <?= number_format((float)$comparison[1]['total_depense'] - (float)$comparison[0]['total_depense'], 2) ?>€
                                </td>
                            </tr>
                            <tr>
                                <td>Reste à vivre</td>
                                <td><?= number_format((float)$comparison[0]['reste'], 2) ?>€</td>
                                <td><?= number_format((float)$comparison[1]['reste'], 2) ?>€</td>
                                <td class="<?= (float)$comparison[1]['reste'] >= (float)$comparison[0]['reste'] ? 'text-success' : 'text-danger' ?>">
                                    <?= number_format((float)$comparison[1]['reste'] - (float)$comparison[0]['reste'], 2) ?>€
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <?php elseif ($mois1 && $mois2): ?>
                <p class="text-muted mt-3">Sélectionnez deux mois différents avec des données.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<div class="row g-3">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header"><i class="bi bi-archive"></i> Historique des budgets</div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-dark">
                            <tr>
                                <th>Mois</th>
                                <th>Revenu</th>
                                <th>Dépensé</th>
                                <th>Reste</th>
                                <th>Statut</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($allBudgets as $b): 
                                $total = Expense::getTotalByBudget($b['id']);
                                $reste = $b['revenu_mensuel'] - $total;
                            ?>
                            <tr>
                                <td><strong><?= htmlspecialchars($b['mois']) ?></strong></td>
                                <td><?= number_format($b['revenu_mensuel'], 2) ?>€</td>
                                <td class="text-danger">-<?= number_format($total, 2) ?>€</td>
                                <td class="<?= $reste >= 0 ? 'text-success' : 'text-danger' ?>">
                                    <?= number_format($reste, 2) ?>€
                                </td>
                                <td>
                                    <?php if ($b['cloture']): ?>
                                    <span class="badge bg-success">Clôturé</span>
                                    <?php else: ?>
                                    <span class="badge bg-warning text-dark">En cours</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <a href="exports/export.php?mois=<?= $b['mois'] ?>&format=csv" class="btn btn-sm btn-outline-success" title="CSV">
                                        <i class="bi bi-filetype-csv"></i>
                                    </a>
                                    <a href="exports/export.php?mois=<?= $b['mois'] ?>&format=pdf" class="btn btn-sm btn-outline-danger" title="PDF">
                                        <i class="bi bi-filetype-pdf"></i>
                                    </a>
                                    <a href="index.php?mois=<?= $b['mois'] ?>" class="btn btn-sm btn-outline-info" title="Voir">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../layouts/footer.php'; ?>