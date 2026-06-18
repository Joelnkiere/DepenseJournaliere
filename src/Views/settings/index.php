<?php include __DIR__ . '/../layouts/header.php';
$user = \App\Models\User::findById($userId);
?>
<div class="row mb-3">
    <div class="col"><h3><i class="bi bi-person-gear"></i> Paramètres</h3></div>
</div>
<div class="row g-3">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header"><i class="bi bi-person"></i> Profil</div>
            <div class="card-body">
                <form id="profileForm">
                    <div class="mb-3">
                        <label class="form-label">Nom</label>
                        <input type="text" name="nom" class="form-control" value="<?= htmlspecialchars($user['nom']) ?>" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Email</label>
                        <input type="email" name="email" class="form-control" value="<?= htmlspecialchars($user['email']) ?>" required>
                    </div>
                    <button type="submit" class="btn btn-primary"><i class="bi bi-check-lg"></i> Enregistrer</button>
                </form>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card">
            <div class="card-header"><i class="bi bi-lock"></i> Mot de passe</div>
            <div class="card-body">
                <form id="passwordForm">
                    <div class="mb-3">
                        <label class="form-label">Mot de passe actuel</label>
                        <input type="password" name="current_password" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Nouveau mot de passe</label>
                        <input type="password" name="new_password" class="form-control" required minlength="6">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Confirmer</label>
                        <input type="password" name="confirm_password" class="form-control" required minlength="6">
                    </div>
                    <button type="submit" class="btn btn-warning"><i class="bi bi-check-lg"></i> Changer</button>
                </form>
            </div>
        </div>
        <div class="card mt-3">
            <div class="card-header"><i class="bi bi-palette"></i> Thème</div>
            <div class="card-body">
                <form id="themeForm">
                    <div class="d-flex gap-3">
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="theme" value="dark" id="themeDark" <?= $user['theme'] === 'dark' ? 'checked' : '' ?>>
                            <label class="form-check-label" for="themeDark"><i class="bi bi-moon-stars"></i> Dark</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="theme" value="light" id="themeLight" <?= $user['theme'] === 'light' ? 'checked' : '' ?>>
                            <label class="form-check-label" for="themeLight"><i class="bi bi-sun"></i> Light</label>
                        </div>
                    </div>
                    <button type="submit" class="btn btn-outline-primary mt-2"><i class="bi bi-check-lg"></i> Appliquer</button>
                </form>
            </div>
        </div>
    </div>
</div>
<script>
document.getElementById('profileForm').addEventListener('submit', async (e) => {
    e.preventDefault();
    const data = new FormData(e.target);
    const r = await apiFetch('api/user.php?action=update_profile', {method:'POST', body: data});
    if (r?.success) { showToast('success', 'Profil mis à jour'); } else if (r) showToast('danger', r.error||'Erreur');
});
document.getElementById('passwordForm').addEventListener('submit', async (e) => {
    e.preventDefault();
    const data = new FormData(e.target);
    if (data.get('new_password') !== data.get('confirm_password')) { showToast('danger', 'Les mots de passe ne correspondent pas'); return; }
    const r = await apiFetch('api/user.php?action=update_password', {method:'POST', body: data});
    if (r?.success) { showToast('success', 'Mot de passe changé'); e.target.reset(); } else if (r) showToast('danger', r.error||'Erreur');
});
document.getElementById('themeForm').addEventListener('submit', async (e) => {
    e.preventDefault();
    const data = new FormData(e.target);
    const r = await apiFetch('api/user.php?action=update_theme', {method:'POST', body: data});
    if (r?.success) {
        showToast('success', 'Thème changé');
        document.documentElement.setAttribute('data-bs-theme', r.theme);
    } else if (r) showToast('danger', r.error||'Erreur');
});
</script>
<?php include __DIR__ . '/../layouts/footer.php'; ?>