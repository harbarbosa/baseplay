<?= $this->extend('layouts/base') ?>

<?= $this->section('content') ?>
<div class="card">
    <div style="display:flex; justify-content:space-between; align-items:center;">
        <div>
            <h1>Sessões de treinos</h1>
            <p style="color:var(--muted);">Histórico de treinos.</p>
        </div>
        <?php if (has_permission('training_sessions.create')): ?>
            <a href="<?= base_url('/training-sessions/create') ?>" class="button">Nova sessão</a>
        <?php endif; ?>
    </div>

    <form method="get" action="<?= base_url('/training-sessions') ?>" style="margin:16px 0; display:flex; gap:12px; flex-wrap:wrap;">
        <select name="team_id" id="team_id">
            <option value="">Equipe</option>
            <?php foreach ($teams as $team): ?>
                <option value="<?= esc($team['id']) ?>" <?= ($filters['team_id'] ?? '') == $team['id'] ? 'selected'  : ''  ?>>
                    <?= esc($team['name']) ?>
                </option>
            <?php endforeach; ?>
        </select>
        <select name="category_id" id="category_id">
            <option value="">Categoria</option>
            <?php foreach ($categories as $category): ?>
                <option value="<?= esc($category['id']) ?>"
                    data-team-id="<?= esc($category['team_id'] ?? '') ?>"
                    <?= ($filters['category_id'] ?? '') == $category['id'] ? 'selected'  : ''  ?>>
                    <?= esc($category['name']) ?>
                </option>
            <?php endforeach; ?>
        </select>
        <input type="date" name="date_from" value="<?= esc($filters['date_from'] ?? '') ?>">
        <input type="date" name="date_to" value="<?= esc($filters['date_to'] ?? '') ?>">
        <button type="submit">Filtrar</button>
        <a href="<?= base_url('/training-sessions') ?>" class="button secondary">Limpar</a>
    </form>

    <table class="table">
        <thead>
            <tr>
                <th>Título</th>
                <th>Equipe</th>
                <th>Categoria</th>
                <th>Data</th>
                <th>Ações</th>
            </tr>
        </thead>
        <tbody>
        <?php foreach ($sessions as $session): ?>
            <tr>
                <td><?= esc($session['title']) ?></td>
                <td><?= esc($session['team_name'] ?? '-') ?></td>
                <td><?= esc($session['category_name'] ?? '-') ?></td>
                <td><?= esc(format_date_br($session['session_date'])) ?></td>
                <td>
                    <a href="<?= base_url('/training-sessions/' . $session['id']) ?>">Detalhes</a>
                    | <a href="<?= base_url('/training-sessions/' . $session['id'] . '/field') ?>">Modo campo</a>
                    <?php if (has_permission('training_sessions.update')): ?>
                        | <a href="<?= base_url('/training-sessions/' . $session['id'] . '/edit') ?>">Editar</a>
                    <?php endif; ?>
                    <?php if (has_permission('training_sessions.delete')): ?>
                        | <a href="<?= base_url('/training-sessions/' . $session['id'] . '/delete') ?>">Excluir</a>
                    <?php endif; ?>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>

    <?php if ($pager): ?>
        <?= $pager->links('training_sessions', 'default_full') ?>
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
        if (categorySelect.selectedOptions[0].hidden) {
            categorySelect.value = '';
        }
    };
    teamSelect.addEventListener('change', filterCategories);
    filterCategories();
})();
</script>
<?= $this->endSection() ?>
