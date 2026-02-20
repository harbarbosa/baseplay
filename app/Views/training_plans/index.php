<?= $this->extend('layouts/base') ?>

<?= $this->section('content') ?>
<div class="card">
    <div style="display:flex; justify-content:space-between; align-items:center;">
        <div>
            <h1>Planos de treino</h1>
            <p style="color:var(--muted);">Planejamento por blocos.</p>
        </div>
        <?php if (has_permission('training_plans.create')): ?>
            <a href="<?= base_url('/training-plans/create') ?>" class="button">Novo plano</a>
        <?php endif; ?>
    </div>

    <form method="get" action="<?= base_url('/training-plans') ?>" style="margin:16px 0; display:flex; gap:12px; flex-wrap:wrap;">
        <select name="team_id" id="team_id">
            <option value="">Equipe</option>
            <?php foreach ($teams as $team): ?>
                <option value="<?= esc($team['id']) ?>" <?= ($filters['team_id'] ?? '') == $team['id'] ? 'selected' : '' ?>>
                    <?= esc($team['name']) ?>
                </option>
            <?php endforeach; ?>
        </select>
        <select name="category_id" id="category_id">
            <option value="">Categoria</option>
            <?php foreach ($categories as $category): ?>
                <option
                    value="<?= esc($category['id']) ?>"
                    data-team-id="<?= esc($category['team_id'] ?? '') ?>"
                    <?= ($filters['category_id'] ?? '') == $category['id'] ? 'selected' : '' ?>
                >
                    <?= esc($category['name']) ?>
                </option>
            <?php endforeach; ?>
        </select>
        <input type="date" name="planned_date_from" value="<?= esc($filters['planned_date_from'] ?? '') ?>">
        <input type="date" name="planned_date_to" value="<?= esc($filters['planned_date_to'] ?? '') ?>">
        <select name="status">
            <option value="">Status</option>
            <option value="draft" <?= ($filters['status'] ?? '') === 'draft' ? 'selected' : '' ?>>Rascunho</option>
            <option value="ready" <?= ($filters['status'] ?? '') === 'ready' ? 'selected' : '' ?>>Pronto</option>
            <option value="archived" <?= ($filters['status'] ?? '') === 'archived' ? 'selected' : '' ?>>Arquivado</option>
        </select>
        <button type="submit">Filtrar</button>
        <a href="<?= base_url('/training-plans') ?>" class="button secondary">Limpar</a>
    </form>

    <table class="table">
        <thead>
            <tr>
                <th>Título</th>
                <th>Equipe</th>
                <th>Categoria</th>
                <th>Data</th>
                <th>Status</th>
                <th>Total (min)</th>
                <th>Ações</th>
            </tr>
        </thead>
        <tbody>
        <?php foreach ($plans as $plan): ?>
            <tr>
                <td><?= esc($plan['title']) ?></td>
                <td><?= esc($plan['team_name'] ?? '-') ?></td>
                <td><?= esc($plan['category_name'] ?? '-') ?></td>
                <td><?= esc(format_date_br($plan['planned_date'] ?? null)) ?></td>
                <td><?= esc(enum_label($plan['status'], 'status')) ?></td>
                <td><?= esc($plan['total_duration_min'] ?? '-') ?></td>
                <td>
                    <a href="<?= base_url('/training-plans/' . $plan['id']) ?>">Detalhes</a>
                    <?php if (has_permission('training_plans.update')): ?>
                        | <a href="<?= base_url('/training-plans/' . $plan['id'] . '/edit') ?>">Editar</a>
                    <?php endif; ?>
                    <?php if (has_permission('training_plans.delete')): ?>
                        | <a href="<?= base_url('/training-plans/' . $plan['id'] . '/delete') ?>">Excluir</a>
                    <?php endif; ?>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>

    <?php if ($pager): ?>
        <?= $pager->links('training_plans', 'default_full') ?>
    <?php endif; ?>
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
