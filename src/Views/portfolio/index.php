<?php include __DIR__ . '/../layouts/header.php';

$userId = \App\Core\Session::get('user_id');
$accounts = \App\Models\Account::getByUser($userId);
$totalNetWorth = \App\Models\Account::getTotalNetWorth($userId);
$totalSaved = \App\Models\SavingsGoal::getTotalEpargne($userId);
$allTransactions = \App\Models\Account::getAllTransactions($userId, 30);
?>

<div class="row mb-3">
    <div class="col"><h3><i class="bi bi-wallet2"></i> Portefeuille</h3></div>
    <div class="col-auto">
        <button class="btn btn-sm btn-outline-primary" onclick="new bootstrap.Modal(document.getElementById('newAccountModal')).show()">
            <i class="bi bi-plus-lg"></i> Nouveau compte
        </button>
    </div>
</div>

<div class="row g-3 mb-4">
    <div class="col-md-4">
        <div class="card border-success h-100">
            <div class="card-body text-center">
                <p class="text-muted small">Valeur nette totale</p>
                <h2 class="text-success"><?= number_format($totalNetWorth, 2) ?>€</h2>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card border-info h-100">
            <div class="card-body text-center">
                <p class="text-muted small">Épargne totale</p>
                <h2 class="text-info"><?= number_format($totalSaved, 2) ?>€</h2>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card border-primary h-100">
            <div class="card-body text-center">
                <p class="text-muted small">Comptes actifs</p>
                <h2 class="text-primary"><?= count($accounts) ?></h2>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <?php foreach ($accounts as $a): ?>
    <div class="col-md-6 mb-3">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span><i class="bi bi-<?= $a['type'] === 'epargne' ? 'piggy-bank' : ($a['type'] === 'especes' ? 'cash' : ($a['type'] === 'credit' ? 'credit-card' : 'bank')) ?>"></i> <?= htmlspecialchars($a['nom']) ?></span>
                <span class="badge bg-<?= $a['type']==='epargne'?'success':($a['type']==='credit'?'danger':'secondary') ?>"><?= $a['type'] ?></span>
            </div>
            <div class="card-body">
                <h4 class="text-<?= $a['solde_actuel'] >= 0 ? 'success' : 'danger' ?> mb-0"><?= number_format($a['solde_actuel'], 2) ?>€</h4>
                <small class="text-muted">Date d'ouverture : <?= date('d/m/Y', strtotime($a['date_creation'])) ?></small>
                <div class="mt-2">
                    <button class="btn btn-sm btn-outline-info" onclick="showTransactionModal(<?= $a['id'] ?>, '<?= htmlspecialchars($a['nom'], ENT_QUOTES) ?>')"><i class="bi bi-arrow-left-right"></i> Transaction</button>
                    <button class="btn btn-sm btn-outline-danger" onclick="deleteAccount(<?= $a['id'] ?>)"><i class="bi bi-trash"></i></button>
                </div>
            </div>
        </div>
    </div>
    <?php endforeach; ?>
    <?php if (empty($accounts)): ?>
    <div class="col-12"><div class="alert alert-info">Aucun compte. Créez-en un !</div></div>
    <?php endif; ?>
</div>

<div class="card mt-3">
    <div class="card-header"><i class="bi bi-activity"></i> Dernières transactions</div>
    <div class="card-body table-responsive">
        <table class="table table-sm">
            <thead class="table-dark">
                <tr><th>Date</th><th>Compte</th><th>Type</th><th>Montant</th><th>Description</th><th></th></tr>
            </thead>
            <tbody>
                <?php if (empty($allTransactions)): ?>
                <tr><td colspan="6" class="text-muted text-center">Aucune transaction</td></tr>
                <?php else: foreach ($allTransactions as $tx): ?>
                <tr>
                    <td><?= date('d/m/Y', strtotime($tx['date_transaction'])) ?></td>
                    <td><?= htmlspecialchars($tx['account_nom']) ?></td>
                    <td><span class="badge bg-<?= $tx['type']==='depot'?'success':($tx['type']==='retrait'?'danger':'info') ?>"><?= $tx['type'] ?></span></td>
                    <td class="fw-bold text-<?= $tx['type']==='depot'||$tx['type']==='virement_in'?'success':'danger' ?>">
                        <?= ($tx['type']==='depot'||$tx['type']==='virement_in'?'+':'-') . number_format(abs($tx['montant']), 2) ?>€
                    </td>
                    <td><?= htmlspecialchars($tx['description'] ?: '-') ?></td>
                    <td><button class="btn btn-sm btn-outline-danger py-0" onclick="deleteTransaction(<?= $tx['id'] ?>)"><i class="bi bi-x"></i></button></td>
                </tr>
                <?php endforeach; endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- New Account Modal -->
<div class="modal fade" id="newAccountModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header"><h5><i class="bi bi-wallet2"></i> Nouveau compte</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
            <form id="newAccountForm">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Nom du compte</label>
                        <input type="text" name="nom" class="form-control" required placeholder="Ex: Compte courant BNP">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Type</label>
                        <select name="type" class="form-select">
                            <option value="courant">Courant</option>
                            <option value="epargne">Épargne</option>
                            <option value="credit">Crédit</option>
                            <option value="especes">Espèces</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Solde initial (€)</label>
                        <input type="number" step="0.01" name="solde_initial" class="form-control" value="0">
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

<!-- Transaction Modal -->
<div class="modal fade" id="transactionModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header"><h5 id="txModalTitle"><i class="bi bi-arrow-left-right"></i> Transaction</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
            <form id="transactionForm">
                <div class="modal-body">
                    <input type="hidden" name="account_id" id="txAccountId">
                    <div class="mb-3">
                        <label class="form-label">Type</label>
                        <select name="type" class="form-select">
                            <option value="depot">Dépôt (+)</option>
                            <option value="retrait">Retrait (-)</option>
                            <option value="virement_in">Virement reçu (+)</option>
                            <option value="virement_out">Virement émis (-)</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Montant (€)</label>
                        <input type="number" step="0.01" name="montant" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Description</label>
                        <input type="text" name="description" class="form-control" placeholder="Ex: Salaire mai 2026">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Date</label>
                        <input type="date" name="date_transaction" class="form-control" value="<?= date('Y-m-d') ?>" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                    <button type="submit" class="btn btn-info"><i class="bi bi-check-lg"></i> Ajouter</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.getElementById('newAccountForm').addEventListener('submit', async (e) => {
    e.preventDefault();
    const data = new FormData(e.target);
    const r = await apiFetch('api/accounts.php?action=create', {method:'POST', body:data});
    if (r?.success) { showToast('success', 'Compte créé'); location.reload(); }
    else if (r) showToast('danger', r.error||'Erreur');
});

function showTransactionModal(accountId, accountName) {
    document.getElementById('txAccountId').value = accountId;
    document.getElementById('txModalTitle').innerHTML = `<i class="bi bi-arrow-left-right"></i> Transaction - ${accountName}`;
    new bootstrap.Modal(document.getElementById('transactionModal')).show();
}

document.getElementById('transactionForm').addEventListener('submit', async (e) => {
    e.preventDefault();
    const data = new FormData(e.target);
    const r = await apiFetch('api/accounts.php?action=transaction', {method:'POST', body:data});
    if (r?.success) { showToast('success', 'Transaction ajoutée'); location.reload(); }
    else if (r) showToast('danger', r.error||'Erreur');
});

async function deleteAccount(id) {
    if (!confirm('Supprimer ce compte (les transactions seront conservées) ?')) return;
    const data = new FormData(); data.append('id', id);
    const r = await apiFetch('api/accounts.php?action=delete', {method:'POST', body:data});
    if (r?.success) location.reload();
}

async function deleteTransaction(id) {
    if (!confirm('Supprimer cette transaction ?')) return;
    const data = new FormData(); data.append('id', id);
    const r = await apiFetch('api/accounts.php?action=delete_transaction', {method:'POST', body:data});
    if (r?.success) location.reload();
}
</script>

<?php include __DIR__ . '/../layouts/footer.php'; ?>