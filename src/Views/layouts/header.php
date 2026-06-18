<?php
$currentPage = basename($_SERVER['PHP_SELF']);
$isLoggedIn  = \App\Core\Session::isLoggedIn();
$userTheme   = $isLoggedIn ? \App\Models\User::getTheme(\App\Core\Session::get('user_id')) : 'dark';
$userName    = $isLoggedIn ? (\App\Core\Session::get('user_nom') ?? '') : '';
$userEmail   = $isLoggedIn ? (\App\Core\Session::get('user_email') ?? '') : '';
$userInitial = $userName ? strtoupper(mb_substr($userName, 0, 1)) : 'U';

$navItems = [
    ['href' => 'index.php',     'icon' => 'bi-speedometer2',   'label' => 'Dashboard'],
    ['href' => 'plan.php',      'icon' => 'bi-clipboard-data', 'label' => 'Plan mensuel'],
    ['href' => 'budget.php',    'icon' => 'bi-piggy-bank',     'label' => 'Budget'],
    ['href' => 'revenus.php',   'icon' => 'bi-cash-stack',     'label' => 'Revenus'],
    ['href' => 'expenses.php',  'icon' => 'bi-receipt',        'label' => 'D&eacute;penses'],
    ['href' => 'portfolio.php', 'icon' => 'bi-wallet2',        'label' => 'Portefeuille'],
    ['href' => 'savings.php',   'icon' => 'bi-graph-up-arrow', 'label' => '&Eacute;pargne'],
    ['href' => 'recurring.php', 'icon' => 'bi-arrow-repeat',   'label' => 'R&eacute;currents'],
    ['href' => 'advisor.php',   'icon' => 'bi-lightbulb',      'label' => 'Conseiller'],
    ['href' => 'archive.php',   'icon' => 'bi-clock-history',  'label' => 'Historique'],
    ['href' => 'import.php',    'icon' => 'bi-upload',         'label' => 'Importer'],
];

$pageTitles = [
    'index.php'      => 'Dashboard',
    'plan.php'       => 'Plan Mensuel',
    'budget.php'     => 'Budget',
    'revenus.php'    => 'Revenus',
    'expenses.php'   => 'D&eacute;penses',
    'portfolio.php'  => 'Portefeuille',
    'savings.php'    => '&Eacute;pargne',
    'recurring.php'  => 'R&eacute;currents',
    'advisor.php'    => 'Conseiller IA',
    'archive.php'    => 'Historique',
    'import.php'     => 'Importer',
    'profile.php'    => 'Profil',
    'categories.php' => 'Cat&eacute;gories',
];
$pageTitle = $pageTitles[$currentPage] ?? 'Budget Manager';
$plainTitle = html_entity_decode($pageTitle, ENT_QUOTES, 'UTF-8');
?>
<!DOCTYPE html>
<html lang="fr" data-bs-theme="<?= htmlspecialchars($userTheme) ?>">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">
    <title><?= htmlspecialchars($plainTitle) ?> &mdash; BudgetPro</title>
    <meta name="description" content="Gerez vos depenses, revenus et budgets avec BudgetPro.">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/admin-lte@4/dist/css/adminlte.min.css">
    <link href="../assets/css/style.css" rel="stylesheet">
    <script>
        (function(){
            const t = localStorage.getItem('bm_theme');
            if (t) document.documentElement.setAttribute('data-bs-theme', t);
        })();
    </script>
</head>
<body class="layout-fixed sidebar-expand-lg">
<div class="app-wrapper">
    <nav class="app-header navbar navbar-expand bg-body border-bottom">
        <div class="container-fluid">
            <ul class="navbar-nav">
                <li class="nav-item">
                    <button class="nav-link btn btn-link px-2" type="button" data-lte-toggle="sidebar" aria-label="Ouvrir le menu">
                        <i class="bi bi-list fs-5"></i>
                    </button>
                </li>
                <li class="nav-item d-none d-md-block">
                    <span class="nav-link page-title"><?= $pageTitle ?></span>
                </li>
            </ul>

            <ul class="navbar-nav ms-auto align-items-center gap-2">
                <li class="nav-item dropdown">
                    <button class="btn btn-sm btn-outline-secondary position-relative"
                            id="notifBtn" data-bs-toggle="dropdown"
                            aria-expanded="false" aria-label="Notifications">
                        <i class="bi bi-bell fs-6"></i>
                        <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger"
                              id="notifBadge" style="display:none;font-size:.6rem;">0</span>
                    </button>
                    <div class="dropdown-menu dropdown-menu-end shadow notif-dropdown-menu"
                         aria-labelledby="notifBtn">
                        <div class="notif-header">
                            <span>Notifications</span>
                            <button class="btn btn-link btn-sm p-0 text-decoration-none"
                                    onclick="markAllRead()">Tout lu</button>
                        </div>
                        <div id="notifList">
                            <div class="text-center text-body-secondary py-3 small">Chargement...</div>
                        </div>
                    </div>
                </li>

                <li class="nav-item">
                    <button class="btn btn-sm btn-outline-secondary" id="headerThemeToggle"
                            aria-label="Basculer le theme" title="Changer le theme">
                        <i class="bi bi-sun-fill" id="themeIconSun"
                           style="<?= $userTheme==='light'?'':'display:none' ?>"></i>
                        <i class="bi bi-moon-stars-fill" id="themeIconMoon"
                           style="<?= $userTheme==='dark'?'':'display:none' ?>"></i>
                    </button>
                </li>
            </ul>
        </div>
    </nav>

    <aside class="app-sidebar bg-body-secondary" id="appSidebar" aria-label="Sidebar">
        <div class="sidebar-brand">
            <a href="index.php" class="brand-link">
                <i class="bi bi-wallet2 brand-image text-primary"></i>
                <span class="brand-text fw-semibold">BudgetPro</span>
            </a>
        </div>

        <div class="sidebar-wrapper">
            <?php if ($isLoggedIn): ?>
            <div class="user-panel d-flex align-items-center gap-3 px-3 py-3 border-bottom">
                <div class="profile-avatar"><?= htmlspecialchars($userInitial) ?></div>
                <div class="info overflow-hidden">
                    <div class="fw-semibold text-truncate"><?= htmlspecialchars($userName) ?></div>
                    <div class="text-body-secondary text-truncate small"><?= htmlspecialchars($userEmail) ?></div>
                </div>
            </div>
            <?php endif; ?>

            <nav class="mt-2">
                <span class="sidebar-section-label">Navigation</span>
                <ul class="nav sidebar-menu flex-column" data-lte-toggle="treeview" role="menu" data-accordion="false">
                    <?php foreach ($navItems as $item): ?>
                    <li class="nav-item">
                        <a href="<?= htmlspecialchars($item['href']) ?>"
                           class="nav-link <?= $currentPage === $item['href'] ? 'active' : '' ?>"
                           <?= $currentPage === $item['href'] ? 'aria-current="page"' : '' ?>>
                            <i class="nav-icon bi <?= htmlspecialchars($item['icon']) ?>"></i>
                            <p><?= $item['label'] ?></p>
                        </a>
                    </li>
                    <?php endforeach; ?>
                </ul>

                <span class="sidebar-section-label">Compte</span>
                <ul class="nav sidebar-menu flex-column" role="menu">
                    <li class="nav-item">
                        <a href="profile.php" class="nav-link <?= $currentPage==='profile.php'?'active':'' ?>">
                            <i class="nav-icon bi bi-person-circle"></i>
                            <p>Profil</p>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="categories.php" class="nav-link <?= $currentPage==='categories.php'?'active':'' ?>">
                            <i class="nav-icon bi bi-tags"></i>
                            <p>Cat&eacute;gories</p>
                        </a>
                    </li>
                </ul>
            </nav>
        </div>

        <div class="sidebar-custom px-3 py-3 border-top">
            <div class="btn-group w-100 mb-2" role="group" aria-label="Theme">
                <button type="button" id="themeLightBtn"
                        class="btn btn-sm <?= $userTheme==='light' ? 'btn-primary' : 'btn-outline-secondary' ?>"
                        onclick="setTheme('light')">
                    <i class="bi bi-sun-fill me-1"></i>Clair
                </button>
                <button type="button" id="themeDarkBtn"
                        class="btn btn-sm <?= $userTheme==='dark' ? 'btn-primary' : 'btn-outline-secondary' ?>"
                        onclick="setTheme('dark')">
                    <i class="bi bi-moon-stars-fill me-1"></i>Sombre
                </button>
            </div>
            <a href="logout.php" class="btn btn-outline-danger btn-sm w-100">
                <i class="bi bi-box-arrow-right me-2"></i>Deconnexion
            </a>
        </div>
    </aside>

    <main class="app-main">
        <div class="app-content-header">
            <div class="container-fluid">
                <div class="row align-items-center">
                    <div class="col">
                        <h1 class="m-0"><?= $pageTitle ?></h1>
                    </div>
                    <div class="col-auto d-none d-sm-block">
                        <ol class="breadcrumb float-sm-end mb-0">
                            <li class="breadcrumb-item"><a href="index.php">Accueil</a></li>
                            <li class="breadcrumb-item active" aria-current="page"><?= $pageTitle ?></li>
                        </ol>
                    </div>
                </div>
            </div>
        </div>
        <div class="app-content">
            <div class="container-fluid">
                <div id="toastContainer" class="toast-container position-fixed top-0 end-0 p-3" style="z-index:1080;"></div>

<script>
const API_BASE = '<?= rtrim(dirname($_SERVER['SCRIPT_NAME']), '/\\') ?>/../';
</script>
<script>
document.addEventListener('DOMContentLoaded', async () => {
    try {
        const r1 = await fetch(API_BASE + 'api/notifications.php?action=count_unread');
        const d1 = await r1.json();
        const badge = document.getElementById('notifBadge');
        if (badge && d1.count > 0) { badge.textContent = d1.count; badge.style.display = 'inline'; }

        const r2 = await fetch(API_BASE + 'api/notifications.php?action=list');
        const notifs = await r2.json();
        const list = document.getElementById('notifList');
        if (list && Array.isArray(notifs)) {
            list.innerHTML = notifs.length === 0
                ? '<div class="text-center text-body-secondary py-3 small"><i class="bi bi-bell-slash d-block fs-4 mb-1"></i>Aucune notification</div>'
                : notifs.map(n => `
                    <a class="notif-item ${n.lu?'':'unread'}" href="#" onclick="markRead(${n.id}); return false;">
                        <div class="notif-dot bg-${n.type}"></div>
                        <div>
                            <div class="fw-semibold" style="font-size:.82rem;">${n.titre}</div>
                            <div class="text-body-secondary" style="font-size:.8rem;">${n.message}</div>
                            <div class="text-body-tertiary" style="font-size:.72rem;">${new Date(n.created_at).toLocaleDateString('fr-FR')}</div>
                        </div>
                    </a>`).join('');
        }
    } catch(e) {}
});
async function markRead(id) {
    const fd = new FormData(); fd.append('id', id);
    await fetch(API_BASE + 'api/notifications.php?action=mark_read', {method:'POST',body:fd});
    location.reload();
}
async function markAllRead() {
    await fetch(API_BASE + 'api/notifications.php?action=mark_all_read', {method:'POST'});
    location.reload();
}
</script>
