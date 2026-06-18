<!DOCTYPE html>
<html lang="fr" data-bs-theme="<?= \App\Core\Session::isLoggedIn() ? \App\Models\User::getTheme(\App\Core\Session::get('user_id')) : 'dark' ?>">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no">
    <title>Budget Manager</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link href="assets/css/style.css" rel="stylesheet">
</head>
<body>

<!-- Desktop Top Navbar -->
<nav class="navbar navbar-expand-lg navbar-dark bg-dark border-bottom border-secondary top-navbar">
    <div class="container-fluid">
        <a class="navbar-brand fw-bold" href="index.php"><i class="bi bi-wallet2"></i> Budget</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav me-auto">
                <li class="nav-item"><a class="nav-link" href="index.php"><i class="bi bi-speedometer2"></i> Dashboard</a></li>
                <li class="nav-item"><a class="nav-link" href="plan.php"><i class="bi bi-clipboard-data"></i> Plan</a></li>
                <li class="nav-item"><a class="nav-link" href="budget.php"><i class="bi bi-piggy-bank"></i> Budget</a></li>
                <li class="nav-item"><a class="nav-link" href="revenus.php"><i class="bi bi-cash-stack"></i> Revenus</a></li>
                <li class="nav-item"><a class="nav-link" href="expenses.php"><i class="bi bi-receipt"></i> Dépenses</a></li>
                <li class="nav-item"><a class="nav-link" href="portfolio.php"><i class="bi bi-wallet2"></i> Portefeuille</a></li>
                <li class="nav-item"><a class="nav-link" href="savings.php"><i class="bi bi-graph-up-arrow"></i> Épargne</a></li>
                <li class="nav-item"><a class="nav-link" href="recurring.php"><i class="bi bi-arrow-repeat"></i> Récurrent</a></li>
                <li class="nav-item"><a class="nav-link" href="advisor.php"><i class="bi bi-lightbulb"></i> Conseil</a></li>
                <li class="nav-item"><a class="nav-link" href="archive.php"><i class="bi bi-clock-history"></i> Historique</a></li>
            </ul>
            <ul class="navbar-nav">
                <li class="nav-item"><a class="nav-link" href="import.php" title="Import"><i class="bi bi-upload"></i></a></li>
                <li class="nav-item dropdown">
                    <a class="nav-link position-relative" href="#" id="notifDropdown" data-bs-toggle="dropdown">
                        <i class="bi bi-bell"></i>
                        <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger" id="notifBadge" style="font-size:0.6rem;display:none;">0</span>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end" id="notifMenu" style="width:320px;max-height:400px;overflow-y:auto;">
                        <li class="dropdown-header d-flex justify-content-between">
                            <span>Notifications</span>
                            <small><a href="#" onclick="markAllRead()" class="text-decoration-none">Tout lu</a></small>
                        </li>
                        <div id="notifList"><li><span class="dropdown-item-text text-muted">Chargement...</span></li></div>
                    </ul>
                </li>
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" data-bs-toggle="dropdown">
                        <i class="bi bi-person-circle"></i>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li><a class="dropdown-item" href="profile.php"><i class="bi bi-person"></i> Profil</a></li>
                        <li><a class="dropdown-item" href="categories.php"><i class="bi bi-tags"></i> Catégories</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item text-danger" href="logout.php"><i class="bi bi-box-arrow-right"></i> Déconnexion</a></li>
                    </ul>
                </li>
            </ul>
        </div>
    </div>
</nav>

<!-- Mobile Bottom Navigation -->
<nav class="bottom-nav">
    <a href="index.php" class="<?= basename($_SERVER['PHP_SELF']) === 'index.php' ? 'active' : '' ?>">
        <i class="bi bi-speedometer2"></i><span>Dashboard</span>
    </a>
    <a href="plan.php" class="<?= basename($_SERVER['PHP_SELF']) === 'plan.php' ? 'active' : '' ?>">
        <i class="bi bi-clipboard-data"></i><span>Plan</span>
    </a>
    <a href="budget.php" class="<?= basename($_SERVER['PHP_SELF']) === 'budget.php' ? 'active' : '' ?>">
        <i class="bi bi-piggy-bank"></i><span>Budget</span>
    </a>
    <a href="expenses.php" class="<?= basename($_SERVER['PHP_SELF']) === 'expenses.php' ? 'active' : '' ?>">
        <i class="bi bi-receipt"></i><span>Dépenses</span>
    </a>
    <a href="portfolio.php" class="<?= basename($_SERVER['PHP_SELF']) === 'portfolio.php' ? 'active' : '' ?>">
        <i class="bi bi-wallet2"></i><span>Portefeuille</span>
    </a>
    <a href="savings.php" class="<?= basename($_SERVER['PHP_SELF']) === 'savings.php' ? 'active' : '' ?>">
        <i class="bi bi-graph-up-arrow"></i><span>Épargne</span>
    </a>
    <a href="javascript:void(0)" onclick="document.getElementById('mobileMoreMenu').classList.toggle('d-none')">
        <i class="bi bi-grid-3x3-gap-fill"></i><span>Plus</span>
    </a>
</nav>

<!-- Mobile More Menu -->
<div id="mobileMoreMenu" class="d-none position-fixed bottom-nav" style="bottom:var(--bottom-nav-height);height:auto;padding:0.5rem;background:var(--bs-dark);border-top:1px solid var(--bs-border-color);z-index:1029;">
    <div class="d-flex flex-wrap justify-content-around gap-1">
        <a href="revenus.php" class="text-center" style="flex:0 0 25%;font-size:0.6rem;color:var(--bs-secondary-color);text-decoration:none;">
            <i class="bi bi-cash-stack d-block fs-5"></i>Revenus
        </a>
        <a href="recurring.php" class="text-center" style="flex:0 0 25%;font-size:0.6rem;color:var(--bs-secondary-color);text-decoration:none;">
            <i class="bi bi-arrow-repeat d-block fs-5"></i>Récurrent
        </a>
        <a href="advisor.php" class="text-center" style="flex:0 0 25%;font-size:0.6rem;color:var(--bs-secondary-color);text-decoration:none;">
            <i class="bi bi-lightbulb d-block fs-5"></i>Conseil
        </a>
        <a href="archive.php" class="text-center" style="flex:0 0 25%;font-size:0.6rem;color:var(--bs-secondary-color);text-decoration:none;">
            <i class="bi bi-clock-history d-block fs-5"></i>Historique
        </a>
        <a href="import.php" class="text-center" style="flex:0 0 25%;font-size:0.6rem;color:var(--bs-secondary-color);text-decoration:none;">
            <i class="bi bi-upload d-block fs-5"></i>Import
        </a>
        <a href="profile.php" class="text-center" style="flex:0 0 25%;font-size:0.6rem;color:var(--bs-secondary-color);text-decoration:none;">
            <i class="bi bi-person d-block fs-5"></i>Profil
        </a>
        <a href="categories.php" class="text-center" style="flex:0 0 25%;font-size:0.6rem;color:var(--bs-secondary-color);text-decoration:none;">
            <i class="bi bi-tags d-block fs-5"></i>Catégories
        </a>
        <a href="logout.php" class="text-center" style="flex:0 0 25%;font-size:0.6rem;color:var(--bs-danger);text-decoration:none;">
            <i class="bi bi-box-arrow-right d-block fs-5"></i>Quitter
        </a>
    </div>
</div>

<div class="container-fluid p-3">
<div id="toastContainer" class="toast-container position-fixed top-0 end-0 p-3" style="z-index: 1080;"></div>

<script>
// Close mobile more menu on click outside
document.addEventListener('click', function(e) {
    const menu = document.getElementById('mobileMoreMenu');
    if (menu && !menu.classList.contains('d-none') && !e.target.closest('.bottom-nav')) {
        menu.classList.add('d-none');
    }
});

document.addEventListener('DOMContentLoaded', async () => {
    const resp = await fetch('api/notifications.php?action=count_unread');
    const data = await resp.json();
    const badge = document.getElementById('notifBadge');
    if (badge && data.count > 0) { badge.textContent = data.count; badge.style.display = 'inline'; }

    const resp2 = await fetch('api/notifications.php?action=list');
    const notifs = await resp2.json();
    const list = document.getElementById('notifList');
    if (list) {
        if (notifs.length === 0) {
            list.innerHTML = '<li><span class="dropdown-item-text text-muted">Aucune notification</span></li>';
        } else {
            list.innerHTML = notifs.map(n => `
                <li>
                    <a class="dropdown-item ${n.lu ? 'text-muted' : 'fw-bold'}" href="#" onclick="markRead(${n.id}); return false;">
                        <small class="text-${n.type}">${n.titre}</small><br>
                        <span style="font-size:0.85rem;">${n.message}</span>
                        <br><small class="text-muted">${new Date(n.created_at).toLocaleDateString('fr-FR')}</small>
                    </a>
                </li>
            `).join('');
        }
    }
});
async function markRead(id) {
    const data = new FormData(); data.append('id', id);
    await fetch('api/notifications.php?action=mark_read', {method:'POST', body:data});
    location.reload();
}
async function markAllRead() {
    await fetch('api/notifications.php?action=mark_all_read', {method:'POST'});
    location.reload();
}
</script>
