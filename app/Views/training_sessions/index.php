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
                    <div class="bp-action-buttons">
                        <a href="<?= base_url('/training-sessions/' . $session['id']) ?>" class="bp-icon-btn" title="Detalhar" aria-label="Detalhar">
                            <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M2 12s4-6 10-6 10 6 10 6-4 6-10 6-10-6-10-6z"/><circle cx="12" cy="12" r="3"/></svg>
                        </a>
                        <a href="<?= base_url('/training-sessions/' . $session['id'] . '/field') ?>" class="bp-icon-btn" title="Modo campo" aria-label="Modo campo">
                            <svg viewBox="0 0 24 24" aria-hidden="true"><circle cx="12" cy="12" r="9"/><circle cx="12" cy="12" r="3"/></svg>
                        </a>
                        <?php if (has_permission('training_sessions.update')): ?>
                            <a href="<?= base_url('/training-sessions/' . $session['id'] . '/edit') ?>" class="bp-icon-btn" title="Editar" aria-label="Editar">
                                <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M4 20h4l10-10-4-4L4 16v4z"/><path d="M14 6l4 4"/></svg>
                            </a>
                        <?php endif; ?>
                        <?php if (has_permission('training_sessions.delete')): ?>
                            <form method="post" action="<?= base_url('/training-sessions/' . $session['id'] . '/delete') ?>" class="bp-inline-form" onsubmit="return confirm('Excluir esta sessão?');">
                                <?= csrf_field() ?>
                                <button type="submit" class="bp-icon-btn bp-icon-danger" title="Excluir" aria-label="Excluir">
                                    <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M3 6h18"/><path d="M8 6V4h8v2"/><path d="M6 6l1 14h10l1-14"/></svg>
                                </button>
                            </form>
                        <?php endif; ?>
                    </div>
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
