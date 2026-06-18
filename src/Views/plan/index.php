<?php include __DIR__ . '/../layouts/header.php';

$planModel = \App\Models\FinancialPlan::getActive($userId);
$plan = $planModel ? $planModel['plan_data'] : null;
$accounts = \App\Models\Account::getByUser($userId);
$goals = \App\Models\SavingsGoal::getByUser($userId);
?>

<div class="row mb-3">
    <div class="col">
        <h4><i class="bi bi-clipboard-data"></i> Plan financier</h4>
    </div>
    <div class="col-auto">
        <button class="btn btn-sm btn-primary" onclick="generatePlan()">
            <i class="bi bi-arrow-repeat"></i> Générer
        </button>
    </div>
</div>

<?php if (!$plan): ?>
<div class="card">
    <div class="card-body text-center py-5">
        <i class="bi bi-robot display-1 text-muted"></i>
        <h5 class="mt-3">Aucun plan généré</h5>
        <p class="text-muted">Lancez la génération pour obtenir un plan personnalisé.</p>
        <button class="btn btn-primary btn-lg" onclick="generatePlan()">
            <i class="bi bi-magic"></i> Générer mon plan
        </button>
    </div>
</div>
<?php else: ?>

<div class="row g-2 mb-3">
    <div class="col-6">
        <div class="card bg-success text-white text-center py-2">
            <i class="bi bi-wallet2 fs-4"></i>
            <small>Disponible</small>
            <strong><?= number_format($plan['disponible_estime'] ?? 0, 0) ?>€</strong>
        </div>
    </div>
    <div class="col-6">
        <div class="card bg-info text-white text-center py-2">
            <i class="bi bi-arrow-repeat fs-4"></i>
            <small>Revenus/mois</small>
            <strong><?= number_format($plan['recurrent_mensuel'] ?? 0, 0) ?>€</strong>
        </div>
    </div>
</div>

<?php if (!empty($plan['suggestions'])): ?>
<div class="mb-3">
    <?php foreach ($plan['suggestions'] as $s): ?>
    <div class="alert alert-<?= $s['type'] === 'danger' ? 'danger' : ($s['type'] === 'epargne' ? 'success' : 'info') ?> py-2 small">
        <i class="bi bi-<?= $s['type'] === 'danger' ? 'exclamation-triangle' : ($s['type'] === 'epargne' ? 'piggy-bank' : 'info-circle') ?>"></i>
        <?= htmlspecialchars($s['message']) ?>
    </div>
    <?php endforeach; ?>
</div>
<?php endif; ?>

<div class="card mb-3">
    <div class="card-header small"><i class="bi bi-list-check"></i> Plan d'action</div>
    <ul class="list-group list-group-flush">
        <?php foreach ($plan['plan_action'] ?? [] as $action): ?>
        <li class="list-group-item small">
            <i class="bi bi-check2-circle text-success"></i> <?= htmlspecialchars($action) ?>
        </li>
        <?php endforeach; ?>
    </ul>
</div>

<?php if (!empty($plan['plan_items'])): ?>
<div class="card mb-3">
    <div class="card-header small"><i class="bi bi-pie-chart"></i> Détail du plan</div>
    <div class="list-group list-group-flush">
        <?php foreach ($plan['plan_items'] as $item): ?>
        <div class="list-group-item">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <?php if ($item['type'] === 'reserve'): ?>
                    <i class="bi bi-shield-check text-warning"></i>
                    <?php elseif ($item['type'] === 'goal'): ?>
                    <i class="bi bi-trophy text-info"></i>
                    <?php endif; ?>
                    <small><?= htmlspecialchars($item['label']) ?></small>
                </div>
                <strong class="text-<?= $item['type'] === 'reserve' ? 'warning' : 'info' ?>">
                    <?= number_format($item['montant'], 0) ?>€
                </strong>
            </div>
            <?php if (!empty($item['par_mois']) && $item['par_mois'] > 0): ?>
            <small class="text-muted"><?= number_format($item['par_mois'], 2) ?>€/mois</small>
            <?php endif; ?>
        </div>
        <?php endforeach; ?>
    </div>
</div>
<?php endif; ?>

<small class="text-muted">
    <i class="bi bi-clock"></i> Généré le <?= date('d/m/Y H:i', strtotime($plan['generated_at'] ?? 'now')) ?>
    <?php if (!empty($plan['ai_note'])): ?> &middot; <?= htmlspecialchars($plan['ai_note']) ?><?php endif; ?>
</small>

<?php endif; ?>

<div class="mt-3">
    <div class="card">
        <div class="card-header small"><i class="bi bi-wallet2"></i> Comptes</div>
        <div class="list-group list-group-flush">
            <?php foreach ($accounts as $a): ?>
            <div class="list-group-item">
                <div class="d-flex justify-content-between">
                    <span><i class="bi bi-<?= $a['type']==='epargne'?'piggy-bank':'wallet' ?>"></i> <?= htmlspecialchars($a['nom']) ?></span>
                    <strong class="text-<?= $a['solde_actuel']>=0?'success':'danger' ?>"><?= number_format($a['solde_actuel'], 0) ?>€</strong>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<div class="mt-3">
    <div class="card">
        <div class="card-header small"><i class="bi bi-trophy"></i> Objectifs</div>
        <div class="list-group list-group-flush">
            <?php foreach ($goals as $g):
                $pct = $g['montant_cible'] > 0 ? min(100, round(($g['montant_actuel'] / $g['montant_cible']) * 100)) : 0;
            ?>
            <div class="list-group-item">
                <div class="d-flex justify-content-between mb-1">
                    <small><?= htmlspecialchars($g['titre']) ?></small>
                    <small><?= $pct ?>%</small>
                </div>
                <div class="progress" style="height:4px;">
                    <div class="progress-bar bg-warning" style="width:<?= $pct ?>%"></div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<script>
async function generatePlan() {
    const resp = await fetch(API_BASE + 'api/planner.php?action=generate', { method: 'POST' });
    const r = await resp.json();
    if (r.success) {
        showToast('success', 'Plan généré !');
        location.reload();
    } else {
        showToast('danger', r.error || 'Erreur');
    }
}
</script>

<?php include __DIR__ . '/../layouts/footer.php'; ?>