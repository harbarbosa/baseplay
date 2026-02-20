<?= $this->extend('layouts/base') ?>

<?= $this->section('content') ?>
<div class="card" style="max-width:900px;">
    <h1>Editar plano</h1>
    <form method="post" action="<?= base_url('/training-plans/' . $plan['id'] . '/update') ?>">
        <?= csrf_field() ?>

        <div class="form-group">
            <label>Equipe</label>
            <select name="team_id" id="team_id">
                <?php foreach ($teams as $team): ?>
                    <option value="<?= esc($team['id']) ?>" <?= old('team_id', $plan['team_id']) == $team['id'] ? 'selected' : '' ?>>
                        <?= esc($team['name']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="form-group">
            <label>Categoria</label>
            <select name="category_id" id="category_id">
                <?php foreach ($categories as $category): ?>
                    <option
                        value="<?= esc($category['id']) ?>"
                        data-team-id="<?= esc($category['team_id'] ?? '') ?>"
                        <?= old('category_id', $plan['category_id']) == $category['id'] ? 'selected' : '' ?>
                    >
                        <?= esc($category['name']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="form-group">
            <label>Título</label>
            <input type="text" name="title" value="<?= esc(old('title', $plan['title'])) ?>">
        </div>

        <div class="form-group">
            <label>Objetivo</label>
            <input type="text" name="goal" value="<?= esc(old('goal', $plan['goal'])) ?>">
        </div>

        <div class="form-group">
            <label>Data planejada</label>
            <input type="date" name="planned_date" value="<?= esc(old('planned_date', $plan['planned_date'])) ?>">
        </div>

        <div class="form-group">
            <label>Status</label>
            <select name="status">
                <option value="draft" <?= old('status', $plan['status']) === 'draft' ? 'selected' : '' ?>>Rascunho</option>
                <option value="ready" <?= old('status', $plan['status']) === 'ready' ? 'selected' : '' ?>>Pronto</option>
                <option value="archived" <?= old('status', $plan['status']) === 'archived' ? 'selected' : '' ?>>Arquivado</option>
            </select>
        </div>

        <button type="submit">Salvar</button>
        <a href="<?= base_url('/training-plans/' . $plan['id']) ?>" class="button secondary">Cancelar</a>
    </form>
</div>

<script>
(() => {
    const teamSelect = document.getElementById('team_id');
    const categorySelect = document.getElementById('category_id');
    if (!teamSelect || !categorySelect) return;

    const filterCategories = () => {
        const teamId = teamSelect.value;
        Array.from(categorySelect.options).forEach((opt) => {
            if (!opt.value) return;
            const optTeam = opt.getAttribute('data-team-id');
            opt.hidden = teamId && optTeam !== teamId;
        });
        const selected = categorySelect.selectedOptions[0];
        if (selected && selected.hidden) {
            categorySelect.value = '';
        }
    };

    teamSelect.addEventListener('change', filterCategories);
    filterCategories();
})();
</script>
<?= $this->endSection() ?>
