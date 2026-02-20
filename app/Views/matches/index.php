<?= $this->extend('layouts/base') ?>

<?= $this->section('content') ?>
<div class="card">
    <div style="display:flex; justify-content:space-between; align-items:center;">
        <div>
            <h1>Jogos</h1>
            <p style="color:var(--muted);">Cadastro e acompanhamento de partidas.</p>
        </div>
        <?php if (has_permission('matches.create')): ?>
            <a href="<?= base_url('/matches/create') ?>" class="button">Novo jogo</a>
        <?php endif; ?>
    </div>

    <form method="get" action="<?= base_url('/matches') ?>" style="margin:16px 0; display:flex; gap:12px; flex-wrap:wrap;">
        <select name="team_id" id="team_id" onchange="this.form.submit()">
            <option value="">Equipe</option>
            <?php foreach ($teams as $team): ?>
                <option value="<?= esc($team['id']) ?>" <?= ($filters['team_id'] ?? '') == $team['id'] ? 'selected' : '' ?>><?= esc($team['name']) ?></option>
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
        <input type="date" name="date_from" value="<?= esc($filters['date_from'] ?? '') ?>">
        <input type="date" name="date_to" value="<?= esc($filters['date_to'] ?? '') ?>">
        <input type="text" name="competition_name" placeholder="Competição" value="<?= esc($filters['competition_name'] ?? '') ?>">
        <select name="status">
            <option value="">Status</option>
            <option value="scheduled" <?= ($filters['status'] ?? '') === 'scheduled' ? 'selected' : '' ?>>Agendado</option>
            <option value="completed" <?= ($filters['status'] ?? '') === 'completed' ? 'selected' : '' ?>>Concluído</option>
            <option value="cancelled" <?= ($filters['status'] ?? '') === 'cancelled' ? 'selected' : '' ?>>Cancelado</option>
        </select>
        <button type="submit">Filtrar</button>
        <a href="<?= base_url('/matches') ?>" class="button secondary">Limpar</a>
    </form>

    <table class="table">
        <thead>
            <tr>
                <th>Data</th>
                <th>Equipe</th>
                <th>Categoria</th>
                <th>Adversário</th>
                <th>Placar</th>
                <th>Status</th>
                <th>Ações</th>
            </tr>
        </thead>
        <tbody>
        <?php foreach ($matches as $match): ?>
            <tr>
                <td><?= esc(format_date_br($match['match_date'])) ?></td>
                <td><?= esc($match['team_name'] ?? '-') ?></td>
                <td><?= esc($match['category_name'] ?? '-') ?></td>
                <td><?= esc($match['opponent_name']) ?></td>
                <td>
                    <?php if ($match['status'] === 'completed'): ?>
                        <?= esc($match['score_for'] ?? '-') ?> x <?= esc($match['score_against'] ?? '-') ?>
                    <?php else: ?>
                        -
                    <?php endif; ?>
                </td>
                <td><?= esc(enum_label($match['status'], 'status')) ?></td>
                <td>
                    <a href="<?= base_url('/matches/' . $match['id']) ?>">Detalhes</a>
                    <?php if (has_permission('matches.update')): ?>
                        | <a href="<?= base_url('/matches/' . $match['id'] . '/edit') ?>">Editar</a>
                    <?php endif; ?>
                    <?php if (has_permission('matches.delete')): ?>
                        | <a href="<?= base_url('/matches/' . $match['id'] . '/delete') ?>">Excluir</a>
                    <?php endif; ?>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>

    <?php if ($pager): ?>
        <?= $pager->links('matches', 'default_full') ?>
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
