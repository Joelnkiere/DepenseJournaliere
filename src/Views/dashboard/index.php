<?php include __DIR__ . '/../layouts/header.php'; ?>

<div class="row mb-3">
    <div class="col">
        <h3><i class="bi bi-speedometer2"></i> Tableau de bord</h3>
    </div>
    <div class="col-auto d-flex align-items-center gap-2">
        <a href="exports/export.php?mois=<?= $mois ?>&format=csv" class="btn btn-sm btn-outline-success" title="CSV">
            <i class="bi bi-filetype-csv"></i>
        </a>
        <a href="exports/export.php?mois=<?= $mois ?>&format=pdf" class="btn btn-sm btn-outline-danger" title="PDF">
            <i class="bi bi-filetype-pdf"></i>
        </a>
        <form method="GET" class="d-flex align-items-center">
            <label class="me-2 text-muted small">Mois :</label>
            <input type="month" name="mois" class="form-control form-control-sm" value="<?= $mois ?>" onchange="this.form.submit()">
        </form>
    </div>
</div>

<div class="row g-3 mb-4">
    <div class="col-md-3">
        <div class="card border-primary h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <p class="card-text text-muted small">Revenu</p>
                        <h4 class="text-primary mb-0" id="revenuDisplay"><?= number_format($budget['revenu_mensuel'], 2) ?>€</h4>
                    </div>
                    <i class="bi bi-cash-stack display-6 text-primary opacity-50"></i>
                </div>
                <button class="btn btn-sm btn-outline-primary mt-2" onclick="editRevenu()">
                    <i class="bi bi-pencil"></i> Modifier
                </button>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-success h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <p class="card-text text-muted small">Reste à vivre</p>
                        <h4 class="text-success mb-0" id="resteAVivreDisplay"><?= number_format($resteAVivre, 2) ?>€</h4>
                    </div>
                    <i class="bi bi-heart-pulse display-6 text-success opacity-50"></i>
                </div>
                <?php if ($resteAVivre > 0 && !empty($savingsGoals)): ?>
                <button class="btn btn-sm btn-outline-info mt-2" onclick="showTransferModal()">
                    <i class="bi bi-send"></i> Verser à l'épargne
                </button>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-danger h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <p class="card-text text-muted small">Total dépensé</p>
                        <h4 class="text-danger mb-0"><?= number_format($totalDepense, 2) ?>€</h4>
                    </div>
                    <i class="bi bi-cart-dash display-6 text-danger opacity-50"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-info h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <p class="card-text text-muted small">Épargne totale</p>
                        <h4 class="text-info mb-0"><?= number_format($totalEpargne, 2) ?>€</h4>
                    </div>
                    <i class="bi bi-piggy-bank display-6 text-info opacity-50"></i>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row g-3 mb-4">
    <div class="col-md-6">
        <div class="card h-100">
            <div class="card-header"><i class="bi bi-pie-chart"></i> Répartition par catégorie</div>
            <div class="card-body">
                <canvas id="categoryPieChart" height="250"></canvas>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card h-100">
            <div class="card-header"><i class="bi bi-graph-up"></i> Dépenses quotidiennes</div>
            <div class="card-body">
                <canvas id="dailyLineChart" height="250"></canvas>
            </div>
        </div>
    </div>
</div>

<div class="row g-3 mb-4">
    <div class="col-md-4">
        <div class="card h-100">
            <div class="card-header"><i class="bi bi-lightbulb text-warning"></i> Conseiller Financier</div>
            <div class="card-body" id="advisorContent">
                <?php if ($budget['revenu_mensuel'] > 0): ?>
                <div class="mb-3">
                    <h6 class="text-muted">Règle 50/30/20</h6>
                    <div class="d-flex justify-content-between small mb-1">
                        <span class="text-primary">Besoins</span>
                        <span><?= $conseils['regle_50_30_20']['besoins_pct'] ?>% / 50%</span>
                    </div>
                    <div class="progress mb-2" style="height:8px">
                        <div class="progress-bar bg-primary" style="width:<?= min($conseils['regle_50_30_20']['besoins_pct'], 100) ?>%"></div>
                    </div>
                    <div class="d-flex justify-content-between small mb-1">
                        <span class="text-success">Envie</span>
                        <span><?= $conseils['regle_50_30_20']['envies_pct'] ?>% / 30%</span>
                    </div>
                    <div class="progress mb-2" style="height:8px">
                        <div class="progress-bar bg-success" style="width:<?= min($conseils['regle_50_30_20']['envies_pct'], 100) ?>%"></div>
                    </div>
                    <div class="d-flex justify-content-between small mb-1">
                        <span class="text-info">Épargne</span>
                        <span><?= $conseils['regle_50_30_20']['epargne_pct'] ?>% / 20%</span>
                    </div>
                    <div class="progress mb-2" style="height:8px">
                        <div class="progress-bar bg-info" style="width:<?= min($conseils['regle_50_30_20']['epargne_pct'], 100) ?>%"></div>
                    </div>
                </div>
                <div class="alert alert-<?= $conseils['regle_50_30_20']['statut'] ?> small">
                    <?= htmlspecialchars($conseils['regle_50_30_20']['message']) ?>
                </div>
                <hr>
                <div class="alert alert-<?= $conseils['prediction_fin_mois']['statut'] ?? 'info' ?> small">
                    <strong>Prédiction :</strong> <?= htmlspecialchars($conseils['prediction_fin_mois']['message'] ?? '') ?>
                </div>
                <?php else: ?>
                <div class="alert alert-info">
                    <i class="bi bi-info-circle"></i> Configurez votre revenu mensuel pour activer le conseiller.
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card h-100">
            <div class="card-header"><i class="bi bi-trophy"></i> Objectifs d'épargne</div>
            <div class="card-body" id="savingsGoalsList">
                <?php if (empty($savingsGoals)): ?>
                <p class="text-muted text-center my-4">Aucun objectif pour le moment.</p>
                <?php else: ?>
                <?php foreach ($savingsGoals as $goal): 
                    $pct = $goal['montant_cible'] > 0 ? min(100, round(($goal['montant_actuel'] / $goal['montant_cible']) * 100, 1)) : 0;
                ?>
                <div class="mb-3">
                    <div class="d-flex justify-content-between">
                        <strong><?= htmlspecialchars($goal['titre']) ?></strong>
                        <span class="text-muted small"><?= number_format($goal['montant_actuel'], 0) ?>€ / <?= number_format($goal['montant_cible'], 0) ?>€</span>
                    </div>
                    <div class="progress" style="height:10px">
                        <div class="progress-bar bg-warning" style="width:<?= $pct ?>%"><?= $pct ?>%</div>
                    </div>
                    <?php if ($goal['date_limite']): ?>
                    <small class="text-muted">Limite : <?= date('d/m/Y', strtotime($goal['date_limite'])) ?></small>
                    <?php endif; ?>
                </div>
                <?php endforeach; ?>
                <?php endif; ?>
                <button class="btn btn-sm btn-outline-warning w-100 mt-2" onclick="showAddGoalModal()">
                    <i class="bi bi-plus-circle"></i> Nouvel objectif
                </button>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card h-100">
            <div class="card-header"><i class="bi bi-exclamation-triangle text-warning"></i> Alertes budget</div>
            <div class="card-body">
                <?php if (!empty($conseils['alertes_categories'])): ?>
                <?php foreach ($conseils['alertes_categories'] as $alerte): ?>
                <div class="alert alert-warning py-2 small">
                    <i class="bi bi-exclamation-circle"></i>
                    <?= htmlspecialchars($alerte['message']) ?>
                </div>
                <?php endforeach; ?>
                <?php else: ?>
                <p class="text-muted text-center my-4">Aucune alerte. Bon suivi !</p>
                <?php endif; ?>
                <?php if (!empty($conseils['astuce_du_jour']['astuce'])): ?>
                <hr>
                <div class="small">
                    <strong><i class="bi bi-lightbulb text-warning"></i> Astuce </strong><br>
                    <?= htmlspecialchars($conseils['astuce_du_jour']['astuce']) ?>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <span><i class="bi bi-list-ul"></i> Dernières dépenses</span>
        <button class="btn btn-sm btn-primary" onclick="showAddExpenseModal()">
            <i class="bi bi-plus-lg"></i> Ajouter
        </button>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0" id="expensesTable">
                <thead class="table-dark">
                    <tr>
                        <th>Date</th>
                        <th>Catégorie</th>
                        <th>Montant</th>
                        <th>Description</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach (array_slice($depenses, 0, 10) as $dep): ?>
                    <tr>
                        <td><?= date('d/m/Y', strtotime($dep['date_depense'])) ?></td>
                        <td><span class="badge bg-secondary"><?= htmlspecialchars($dep['category_nom']) ?></span></td>
                        <td class="text-danger fw-bold">-<?= number_format($dep['montant'], 2) ?>€</td>
                        <td><?= htmlspecialchars($dep['description'] ?: '-') ?></td>
                        <td>
                            <button class="btn btn-sm btn-outline-danger" onclick="deleteExpense(<?= $dep['id'] ?>)">
                                <i class="bi bi-trash"></i>
                            </button>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Add Expense Modal -->
<div class="modal fade" id="addExpenseModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="bi bi-plus-circle"></i> Nouvelle dépense</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="addExpenseForm">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Catégorie</label>
                        <select name="category_id" class="form-select" required>
                            <option value="">Choisir...</option>
                            <?php foreach (\App\Models\Category::getAll() as $cat): ?>
                            <option value="<?= $cat['id'] ?>"><?= htmlspecialchars($cat['nom']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Montant (€)</label>
                        <input type="number" step="0.01" name="montant" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Date</label>
                        <input type="date" name="date_depense" class="form-control" value="<?= date('Y-m-d') ?>" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Description</label>
                        <input type="text" name="description" class="form-control" placeholder="Optionnel">
                    </div>
                    <input type="hidden" name="mois" value="<?= $mois ?>">
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                    <button type="submit" class="btn btn-primary"><i class="bi bi-check-lg"></i> Ajouter</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Add Goal Modal -->
<div class="modal fade" id="addGoalModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="bi bi-trophy"></i> Nouvel objectif</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="addGoalForm">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Titre</label>
                        <input type="text" name="titre" class="form-control" placeholder="Ex: Achat PC" required>
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

<!-- Transfer to Savings Modal -->
<div class="modal fade" id="transferModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="bi bi-send"></i> Verser à un objectif d'épargne</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="transferForm">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Objectif</label>
                        <select name="id" class="form-select" required>
                            <option value="">Choisir...</option>
                            <?php foreach ($savingsGoals as $g): ?>
                            <option value="<?= $g['id'] ?>"><?= htmlspecialchars($g['titre']) ?> (<?= number_format($g['montant_actuel'], 0) ?>/<?= number_format($g['montant_cible'], 0) ?>€)</option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Montant à transférer (€)</label>
                        <input type="number" step="0.01" name="montant" class="form-control form-control-lg"
                               max="<?= $resteAVivre ?>" value="<?= min(50, $resteAVivre) ?>" required>
                        <small class="text-muted">Disponible : <?= number_format($resteAVivre, 2) ?>€</small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                    <button type="submit" class="btn btn-info"><i class="bi bi-check-lg"></i> Transférer</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Revenue Modal -->
<div class="modal fade" id="editRevenuModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="bi bi-cash-stack"></i> Revenu mensuel</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="editRevenuForm">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Revenu du mois (€)</label>
                        <input type="number" step="0.01" name="revenu" class="form-control form-control-lg"
                               value="<?= $budget['revenu_mensuel'] ?>" required>
                    </div>
                    <input type="hidden" name="mois" value="<?= $mois ?>">
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                    <button type="submit" class="btn btn-primary"><i class="bi bi-check-lg"></i> Enregistrer</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
const categoryData = <?= json_encode($categories) ?>;
const moisActuel = '<?= $mois ?>';
</script>

<?php include __DIR__ . '/../layouts/footer.php'; ?>