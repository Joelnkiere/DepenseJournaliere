<!DOCTYPE html>
<html lang="fr" data-bs-theme="dark">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Connexion &mdash; BudgetPro</title>
    <meta name="description" content="Connectez-vous a votre espace BudgetPro.">
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
<body class="login-page bg-body-tertiary">
<div class="position-fixed top-0 end-0 p-3" style="z-index:100;">
    <button class="btn btn-sm btn-outline-secondary" id="authThemeBtn" title="Changer le theme">
        <i class="bi bi-sun-fill" id="authIconSun" style="display:none;"></i>
        <i class="bi bi-moon-stars-fill" id="authIconMoon"></i>
    </button>
</div>

<div class="auth-wrapper">
    <div class="auth-card">
        <div class="card card-outline card-primary">
            <div class="card-body p-4 p-md-5">
                <div class="text-center mb-4">
                    <i class="bi bi-wallet2 auth-logo-icon"></i>
                    <h1 class="h3 mt-2 mb-1 fw-bold">BudgetPro</h1>
                    <p class="text-body-secondary mb-0 small">Connectez-vous a votre espace</p>
                </div>

                <?php if (isset($error)): ?>
                <div class="alert alert-danger d-flex align-items-center gap-2 alert-dismissible fade show" role="alert">
                    <i class="bi bi-exclamation-triangle-fill flex-shrink-0"></i>
                    <div><?= htmlspecialchars($error) ?></div>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
                <?php endif; ?>

                <?php if ($msg = \App\Core\Session::getFlash('success')): ?>
                <div class="alert alert-success d-flex align-items-center gap-2 alert-dismissible fade show" role="alert">
                    <i class="bi bi-check-circle-fill flex-shrink-0"></i>
                    <div><?= htmlspecialchars($msg) ?></div>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
                <?php endif; ?>

                <form method="POST" action="login.php" novalidate>
                    <div class="mb-3">
                        <label for="loginEmail" class="form-label fw-semibold">Email</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="bi bi-envelope"></i></span>
                            <input type="email" id="loginEmail" name="email"
                                   class="form-control form-control-lg"
                                   placeholder="votre@email.com" required autofocus>
                        </div>
                    </div>
                    <div class="mb-4">
                        <label for="loginPassword" class="form-label fw-semibold">Mot de passe</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="bi bi-lock"></i></span>
                            <input type="password" id="loginPassword" name="password"
                                   class="form-control form-control-lg"
                                   placeholder="Mot de passe" required>
                            <button class="btn btn-outline-secondary" type="button"
                                    onclick="togglePwd('loginPassword','eyeIcon1')"
                                    title="Afficher/masquer">
                                <i class="bi bi-eye" id="eyeIcon1"></i>
                            </button>
                        </div>
                    </div>
                    <button type="submit" class="btn btn-primary btn-lg w-100">
                        <i class="bi bi-box-arrow-in-right me-2"></i>Se connecter
                    </button>
                </form>

                <hr class="my-4">
                <p class="text-center text-body-secondary small mb-0">
                    Pas encore de compte ?
                    <a href="register.php" class="fw-semibold text-decoration-none">Creer un compte</a>
                </p>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/admin-lte@4/dist/js/adminlte.min.js"></script>
<script>
function syncIcons(theme) {
    document.getElementById('authIconSun').style.display = theme === 'light' ? '' : 'none';
    document.getElementById('authIconMoon').style.display = theme === 'dark' ? '' : 'none';
}
syncIcons(document.documentElement.getAttribute('data-bs-theme') || 'dark');

document.getElementById('authThemeBtn').addEventListener('click', function() {
    const cur = document.documentElement.getAttribute('data-bs-theme') || 'dark';
    const next = cur === 'dark' ? 'light' : 'dark';
    document.documentElement.setAttribute('data-bs-theme', next);
    localStorage.setItem('bm_theme', next);
    syncIcons(next);
});

function togglePwd(inputId, iconId) {
    const el = document.getElementById(inputId);
    const ic = document.getElementById(iconId);
    el.type = el.type === 'password' ? 'text' : 'password';
    ic.className = el.type === 'password' ? 'bi bi-eye' : 'bi bi-eye-slash';
}
</script>
</body>
</html>
