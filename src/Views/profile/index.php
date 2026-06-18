<?php include __DIR__ . '/../layouts/header.php';

$user = \App\Models\User::findById($userId);
$userNom = $_SESSION['user_nom'] ?? $user['nom'] ?? '';
$userEmail = $_SESSION['user_email'] ?? $user['email'] ?? '';
?>

<div class="row g-3">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header"><i class="bi bi-person"></i> Profil</div>
            <div class="card-body">
                <form id="profileForm">
                    <div class="mb-3">
                        <label class="form-label">Nom complet</label>
                        <input type="text" name="nom" class="form-control" value="<?= htmlspecialchars($userNom) ?>" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Email</label>
                        <input type="email" name="email" class="form-control" value="<?= htmlspecialchars($userEmail) ?>" required>
                    </div>
                    <button type="submit" class="btn btn-primary"><i class="bi bi-check-lg"></i> Mettre à jour</button>
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
                    <button type="submit" class="btn btn-warning"><i class="bi bi-key"></i> Changer le mot de passe</button>
                </form>
            </div>
        </div>
    </div>
    <div class="col-12">
        <div class="card">
            <div class="card-header"><i class="bi bi-info-circle"></i> Informations du compte</div>
            <div class="card-body">
                <p class="mb-1"><strong>Membre depuis :</strong> <?= date('d/m/Y', strtotime($user['created_at'] ?? 'now')) ?></p>
                <p class="mb-0"><strong>ID :</strong> #<?= $userId ?></p>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    document.getElementById('profileForm').addEventListener('submit', async (e) => {
        e.preventDefault();
        const data = new FormData(e.target);
        const resp = await fetch('api/profile.php?action=update_profile', { method: 'POST', body: data });
        const result = await resp.json();
        if (result.success) {
            showToast('success', 'Profil mis à jour !');
        } else {
            showToast('danger', result.error || 'Erreur');
        }
    });

    document.getElementById('passwordForm').addEventListener('submit', async (e) => {
        e.preventDefault();
        const data = new FormData(e.target);
        const resp = await fetch('api/profile.php?action=update_password', { method: 'POST', body: data });
        const result = await resp.json();
        if (result.success) {
            showToast('success', 'Mot de passe changé !');
            e.target.reset();
        } else {
            showToast('danger', result.error || 'Erreur');
        }
    });
});
</script>

<?php include __DIR__ . '/../layouts/footer.php'; ?>