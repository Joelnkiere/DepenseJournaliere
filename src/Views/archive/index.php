<?php include __DIR__ . '/../layouts/header.php';
$allBudgets = \App\Models\Budget::getAllByUser($userId);
$mois = $_GET['mois'] ?? end($allBudgets)['mois'] ?? date('Y-m');
$budget = \App\Models\Budget::getCurrent($userId, $mois);
$categories = $budget ? \App\Models\Expense::getCategoryTotals($budget['id']) : [];
$totalDepense = $budget ? \App\Models\Expense::getTotalByBudget($budget['id']) : 0;
$revenu = $budget['revenu_mensuel'] ?? 0;
?>
<div class="row mb-3">
    <div class="col"><h3><i class="bi bi-clock-history"></i> Historique & Comparaison</h3></div>
    <div class="col-auto">
        <form method="GET" class="d-flex align-items-center gap-2">
            <select name="mois" class="form-select form-select-sm" onchange="this.form.submit()">
                <?php foreach ($allBudgets as $b): ?>
                <option value="<?= $b['mois'] ?>" <?= $b['mois']===$mois?'selected':'' ?>>
                    <?= htmlspecialchars($b['mois']) ?> <?= $b['cloture']?'🔒':'' ?>
                </option>
                <?php endforeach; ?>
            </select>
            <a href="exports/export.php?mois=<?= $mois ?>&format=csv" class="btn btn-sm btn-outline-success"><i class="bi bi-filetype-csv"></i></a>
            <a href="exports/export.php?mois=<?= $mois ?>&format=pdf" class="btn btn-sm btn-outline-danger"><i class="bi bi-filetype-pdf"></i></a>
        </form>
    </div>
</div>
<?php if (!$budget): ?>
<div class="alert alert-info">Aucun budget pour ce mois.</div>
<?php else: ?>
<div class="row g-3 mb-4">
    <div class="col-md-3"><div class="card border-primary"><div class="card-body text-center"><p class="text-muted small mb-0">Revenu</p><h4 class="text-primary"><?= number_format($revenu,2) ?>€</h4></div></div></div>
    <div class="col-md-3"><div class="card border-danger"><div class="card-body text-center"><p class="text-muted small mb-0">Dépensé</p><h4 class="text-danger"><?= number_format($totalDepense,2) ?>€</h4></div></div></div>
    <div class="col-md-3"><div class="card border-<?= ($revenu-$totalDepense)>=0?'success':'danger' ?>"><div class="card-body text-center"><p class="text-muted small mb-0">Reste</p><h4 class="text-<?= ($revenu-$totalDepense)>=0?'success':'danger' ?>"><?= number_format($revenu-$totalDepense,2) ?>€</h4></div></div></div>
    <div class="col-md-3"><div class="card border-info"><div class="card-body text-center"><p class="text-muted small mb-0">Taux épargne</p><h4 class="text-info"><?= $revenu>0 ? round((($revenu-$totalDepense)/$revenu)*100,1) : 0 ?>%</h4></div></div></div>
</div>
<div class="row g-3">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header"><i class="bi bi-bar-chart"></i> Répartition <?= htmlspecialchars($mois) ?></div>
            <div class="card-body"><canvas id="histPieChart" height="250"></canvas></div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card">
            <div class="card-header"><i class="bi bi-graph-up"></i> Comparaison revenu/dépenses</div>
            <div class="card-body"><canvas id="compareChart" height="250"></canvas></div>
        </div>
    </div>
</div>
<?php if (count($allBudgets) > 1): ?>
<div class="card mt-3">
    <div class="card-header"><i class="bi bi-table"></i> Tableau comparatif des mois</div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-dark">
                    <tr><th>Mois</th><th>Revenu</th><th>Dépensé</th><th>Reste</th><th>Statut</th><th>Actions</th></tr>
                </thead>
                <tbody>
                    <?php foreach ($allBudgets as $b): 
                        $t = \App\Models\Expense::getTotalByBudget($b['id']);
                        $r = $b['revenu_mensuel'] - $t;
                    ?>
                    <tr>
                        <td><?= htmlspecialchars($b['mois']) ?></td>
                        <td><?= number_format($b['revenu_mensuel'],2) ?>€</td>
                        <td class="text-danger"><?= number_format($t,2) ?>€</td>
                        <td class="text-<?= $r>=0?'success':'danger' ?>"><?= number_format($r,2) ?>€</td>
                        <td><?= $b['cloture']?'<span class="badge bg-success">Clôturé</span>':'<span class="badge bg-warning">En cours</span>' ?></td>
                        <td><a href="?mois=<?= $b['mois'] ?>" class="btn btn-sm btn-outline-primary"><i class="bi bi-eye"></i></a></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?php endif; endif; ?>
<script>
const histCategories = <?= json_encode(array_map(fn($c)=>['nom'=>$c['nom'],'total'=>$c['total']], $categories)) ?>;
const allBudgets = <?= json_encode(array_map(function($b) {
    $t = \App\Models\Expense::getTotalByBudget($b['id']);
    return ['mois'=>$b['mois'], 'revenu'=>(float)$b['revenu_mensuel'], 'depense'=>$t];
}, $allBudgets)) ?>;
document.addEventListener('DOMContentLoaded', () => {
    if (histCategories.length) {
        new Chart(document.getElementById('histPieChart'), {
            type: 'pie',
            data: { labels: histCategories.map(c=>c.nom), datasets: [{ data: histCategories.map(c=>parseFloat(c.total)||0), backgroundColor: ['#dc3545','#fd7e14','#ffc107','#20c997','#0dcaf0','#0d6efd','#6610f2','#d63384','#6f42c1','#198754'] }] },
            options: { responsive: true, plugins: { legend: { position: 'right' } } }
        });
    }
    if (allBudgets.length) {
        const sorted = allBudgets.sort((a,b) => a.mois.localeCompare(b.mois));
        new Chart(document.getElementById('compareChart'), {
            type: 'bar',
            data: {
                labels: sorted.map(b=>b.mois),
                datasets: [
                    { label: 'Revenu', data: sorted.map(b=>b.revenu), backgroundColor: '#0d6efd' },
                    { label: 'Dépenses', data: sorted.map(b=>b.depense), backgroundColor: '#dc3545' },
                ]
            },
            options: { responsive: true, scales: { y: { beginAtZero: true } } }
        });
    }
});
</script>
<?php include __DIR__ . '/../layouts/footer.php'; ?>