<?php include __DIR__ . '/../layouts/header.php';

$categories = \App\Models\Category::getAll();
?>

<div class="row mb-3">
    <div class="col">
        <h3><i class="bi bi-tags"></i> Gestion des catégories</h3>
    </div>
    <div class="col-auto">
        <button class="btn btn-primary" onclick="showCategoryModal()"><i class="bi bi-plus-lg"></i> Nouvelle catégorie</button>
    </div>
</div>

<div class="card">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-dark">
                    <tr>
                        <th>ID</th>
                        <th>Nom</th>
                        <th>Type</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($categories as $cat): ?>
                    <tr id="cat-<?= $cat['id'] ?>">
                        <td><?= $cat['id'] ?></td>
                        <td><?= htmlspecialchars($cat['nom']) ?></td>
                        <td>
                            <span class="badge bg-<?= $cat['type'] === 'besoin' ? 'primary' : ($cat['type'] === 'envie' ? 'success' : 'info') ?>">
                                <?= $cat['type'] ?>
                            </span>
                        </td>
                        <td>
                            <button class="btn btn-sm btn-outline-primary" onclick="editCategory(<?= $cat['id'] ?>)">
                                <i class="bi bi-pencil"></i>
                            </button>
                            <button class="btn btn-sm btn-outline-danger" onclick="deleteCategory(<?= $cat['id'] ?>)">
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

<!-- Category Modal -->
<div class="modal fade" id="categoryModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="categoryModalTitle"><i class="bi bi-tag"></i> Nouvelle catégorie</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="categoryForm">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Nom</label>
                        <input type="text" name="nom" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Type</label>
                        <select name="type" class="form-select">
                            <option value="besoin">Besoin</option>
                            <option value="envie">Envie</option>
                            <option value="epargne">Épargne</option>
                        </select>
                    </div>
                    <input type="hidden" name="id" value="0">
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
function showCategoryModal(data = null) {
    const form = document.getElementById('categoryForm');
    const title = document.getElementById('categoryModalTitle');
    if (data) {
        title.innerHTML = '<i class="bi bi-pencil"></i> Modifier la catégorie';
        form.querySelector('[name=id]').value = data.id;
        form.querySelector('[name=nom]').value = data.nom;
        form.querySelector('[name=type]').value = data.type;
    } else {
        title.innerHTML = '<i class="bi bi-tag"></i> Nouvelle catégorie';
        form.reset();
        form.querySelector('[name=id]').value = 0;
    }
    new bootstrap.Modal(document.getElementById('categoryModal')).show();
}

async function editCategory(id) {
    const resp = await fetch('api/categories.php?action=list');
    const cats = await resp.json();
    const cat = cats.find(c => c.id == id);
    if (cat) showCategoryModal(cat);
}

async function deleteCategory(id) {
    if (!confirm('Supprimer cette catégorie ?')) return;
    const data = new FormData();
    data.append('id', id);
    const resp = await fetch('api/categories.php?action=delete', { method: 'POST', body: data });
    const result = await resp.json();
    if (result.success) {
        showToast('success', 'Catégorie supprimée');
        document.getElementById(`cat-${id}`)?.remove();
    } else {
        showToast('danger', result.error || 'Erreur');
    }
}

document.addEventListener('DOMContentLoaded', () => {
    document.getElementById('categoryForm').addEventListener('submit', async (e) => {
        e.preventDefault();
        const form = e.target;
        const id = parseInt(form.querySelector('[name=id]').value);
        const action = id > 0 ? 'update' : 'add';
        const data = new FormData(form);
        const resp = await fetch(`api/categories.php?action=${action}`, { method: 'POST', body: data });
        const result = await resp.json();
        if (result.success) {
            showToast('success', id > 0 ? 'Catégorie modifiée' : 'Catégorie créée');
            bootstrap.Modal.getInstance(document.getElementById('categoryModal')).hide();
            setTimeout(() => location.reload(), 500);
        } else {
            showToast('danger', result.error || 'Erreur');
        }
    });
});
</script>

<?php include __DIR__ . '/../layouts/footer.php'; ?>