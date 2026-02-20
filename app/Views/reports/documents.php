<?= $this->extend('layouts/base') ?>

<?= $this->section('content') ?>
<div class="card">
    <h1><?= esc($title) ?></h1>
    <form method="get" action="<?= current_url() ?>" style="display:flex; gap:12px; flex-wrap:wrap; margin:12px 0;">
        <select name="team_id" id="team_id" onchange="this.form.submit()">
            <option value="">Equipe</option>
            <?php foreach ($teams as $team): ?>
                <option value="<?= esc($team['id']) ?>" <?= ($filters['team_id'] ?? '') == $team['id'] ? 'selected' : '' ?>><?= esc($team['name']) ?></option>
            <?php endforeach; ?>
        </select>
        <select name="category_id" id="category_id">
            <option value="">Categoria</option>
            <?php foreach ($categories as $category): ?>
                <option value="<?= esc($category['id']) ?>" data-team-id="<?= esc($category['team_id'] ?? '') ?>" <?= ($filters['category_id'] ?? '') == $category['id'] ? 'selected' : '' ?>><?= esc($category['name']) ?></option>
            <?php endforeach; ?>
        </select>
        <select name="athlete_id">
            <option value="">Atleta</option>
            <?php foreach ($athletes as $athlete): ?>
                <option value="<?= esc($athlete['id']) ?>" <?= ($filters['athlete_id'] ?? '') == $athlete['id'] ? 'selected' : '' ?>>
                    <?= esc(trim(($athlete['first_name'] ?? '') . ' ' . ($athlete['last_name'] ?? ''))) ?>
                </option>
            <?php endforeach; ?>
        </select>
        <input type="number" name="expiring_in_days" placeholder="Vence em (dias)" value="<?= esc($filters['expiring_in_days'] ?? '') ?>" style="width:140px;">
        <input type="date" name="date_from" value="<?= esc($filters['date_from'] ?? '') ?>">
        <input type="date" name="date_to" value="<?= esc($filters['date_to'] ?? '') ?>">
        <button type="submit">Filtrar</button>
        <a href="<?= current_url() ?>" class="button secondary">Limpar</a>
    </form>

    <div style="display:flex; gap:8px; margin-bottom:12px;">
        <a class="button secondary" href="<?= current_url() . '?' . http_build_query(array_merge($filters, ['format' => 'pdf'])) ?>">Exportar PDF</a>
        <a class="button secondary" href="<?= current_url() . '?' . http_build_query(array_merge($filters, ['format' => 'xlsx'])) ?>">Exportar Excel</a>
    </div>

    <table class="table">
        <thead>
            <tr>
                <?php foreach ($headers as $header): ?>
                    <th><?= esc($header) ?></th>
                <?php endforeach; ?>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($rows as $row): ?>
                <tr>
                    <?php foreach ($row as $cell): ?>
                        <td><?= esc($cell) ?></td>
                    <?php endforeach; ?>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
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
