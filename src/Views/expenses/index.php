<?php include __DIR__ . '/../layouts/header.php'; 

$mois = $_GET['mois'] ?? date('Y-m');
$budget = \App\Models\Budget::getCurrent($userId, $mois);
$previsions = $budget ? \App\Models\Budget::getPrevisions($budget['id']) : [];
$categories = \App\Models\Category::getAll();
?>

<div class="row mb-3">
    <div class="col">
        <h3><i class="bi bi-receipt"></i> Gestion des dépenses</h3>
    </div>
    <div class="col-auto">
        <button class="btn btn-primary" onclick="showAddExpenseModal()"><i class="bi bi-plus-lg"></i> Ajouter</button>
    </div>
</div>

<ul class="nav nav-tabs mb-3" id="expenseTabs" role="tablist">
    <li class="nav-item">
        <button class="nav-link active" data-bs-toggle="tab" data-bs-target="#reelTab" onclick="loadExpenses('reel')">
            <i class="bi bi-cart"></i> Dépenses réelles
        </button>
    </li>
    <li class="nav-item">
        <button class="nav-link" data-bs-toggle="tab" data-bs-target="#prevuTab" onclick="loadExpenses('prevu')">
            <i class="bi bi-calendar-check"></i> Dépenses prévues
        </button>
    </li>
    <li class="nav-item">
        <button class="nav-link" data-bs-toggle="tab" data-bs-target="#prevTab">
            <i class="bi bi-sliders"></i> Budget prévisionnel
        </button>
    </li>
</ul>

<div class="tab-content">
    <div class="tab-pane fade show active" id="reelTab">
        <div class="card">
            <div class="card-body">
                <div class="row g-2 mb-3">
                    <div class="col-md-4">
                        <input type="text" id="searchExpense" class="form-control form-control-sm search-box" placeholder="🔍 Rechercher...">
                    </div>
                    <div class="col-md-2">
                        <select id="filterCategory" class="form-select form-select-sm">
                            <option value="">Toutes catégories</option>
                            <?php foreach ($categories as $cat): ?>
                            <option value="<?= $cat['id'] ?>"><?= htmlspecialchars($cat['nom']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <input type="date" id="filterDateFrom" class="form-control form-control-sm" placeholder="Du">
                    </div>
                    <div class="col-md-2">
                        <input type="date" id="filterDateTo" class="form-control form-control-sm" placeholder="Au">
                    </div>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover mb-0" id="expensesTable">
                        <thead class="table-dark">
                            <tr>
                                <th>Date</th>
                                <th>Catégorie</th>
                                <th>Montant</th>
                                <th>Description</th>
                                <th>Reçu</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    <div class="tab-pane fade" id="prevuTab">
        <div class="card">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0" id="prevuTable">
                        <thead class="table-dark">
                            <tr>
                                <th>Date</th>
                                <th>Catégorie</th>
                                <th>Montant</th>
                                <th>Description</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    <div class="tab-pane fade" id="prevTab">
        <div class="card">
            <div class="card-header">Définir le budget prévisionnel par catégorie</div>
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
                        <div class="col-md-4 col-lg-3">
                            <label class="form-label small"><?= htmlspecialchars($cat['nom']) ?></label>
                            <div class="input-group input-group-sm">
                                <span class="input-group-text">€</span>
                                <input type="number" step="0.01" class="form-control prev-input"
                                       data-category="<?= $cat['id'] ?>" value="<?= $prevMontant ?>">
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <button type="submit" class="btn btn-primary mt-3"><i class="bi bi-save"></i> Enregistrer</button>
                </form>
            </div>
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
                        <label class="form-label">Type</label>
                        <select name="est_prevu" class="form-select">
                            <option value="0">Dépense réelle</option>
                            <option value="1">Dépense prévue</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Catégorie</label>
                        <select name="category_id" class="form-select" required>
                            <option value="">Choisir...</option>
                            <?php foreach ($categories as $cat): ?>
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
                    <div class="mb-3">
                        <label class="form-label">Reçu (image)</label>
                        <input type="file" name="receipt" class="form-control" accept="image/*">
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

<script>
const moisActuel = '<?= $mois ?>';
document.addEventListener('DOMContentLoaded', () => loadExpenses('reel'));
</script>

<?php include __DIR__ . '/../layouts/footer.php'; ?>