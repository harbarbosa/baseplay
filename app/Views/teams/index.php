<?= $this->extend('layouts/base') ?>

<?= $this->section('content') ?>
<div class="card">
    <div style="display:flex; justify-content:space-between; align-items:center;">
        <div>
            <h1>Equipes</h1>
            <p style="color:var(--muted);">Gestão de equipes e categorias.</p>
        </div>
        <?php if (has_permission('teams.create')): ?>
            <a href="<?= base_url('/teams/create') ?>" class="button">Nova equipe</a>
        <?php endif; ?>
    </div>

    <form method="get" action="<?= base_url('/teams') ?>" style="margin:16px 0; display:flex; gap:12px; flex-wrap:wrap;">
        <input type="text" name="search" placeholder="Buscar por nome" value="<?= esc($filters['search'] ?? '') ?>">
        <select name="status">
            <option value="">Status</option>
            <option value="active" <?= ($filters['status'] ?? '') === 'active' ? 'selected' : '' ?>>Ativo</option>
            <option value="inactive" <?= ($filters['status'] ?? '') === 'inactive' ? 'selected' : '' ?>>Inativo</option>
        </select>
        <button type="submit">Filtrar</button>
        <a href="<?= base_url('/teams') ?>" class="button secondary">Limpar</a>
    </form>

    <table class="table">
        <thead>
            <tr>
                <th>ID</th>
                <th>Nome</th>
                <th>Apelido</th>
                <th>Status</th>
                <th>Ações</th>
            </tr>
        </thead>
        <tbody>
        <?php foreach ($teams as $team): ?>
            <tr>
                <td><?= esc($team['id']) ?></td>
                <td><?= esc($team['name']) ?></td>
                <td><?= esc($team['short_name'] ?? '-') ?></td>
                <td><?= esc(enum_label($team['status'], 'status')) ?></td>
                <td>
                    <div class="bp-action-buttons">
                        <a href="<?= base_url('/teams/' . $team['id']) ?>" class="bp-icon-btn" title="Detalhar" aria-label="Detalhar">
                            <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M2 12s4-6 10-6 10 6 10 6-4 6-10 6-10-6-10-6z"/><circle cx="12" cy="12" r="3"/></svg>
                        </a>
                        <?php if (has_permission('teams.update')): ?>
                            <a href="<?= base_url('/teams/' . $team['id'] . '/edit') ?>" class="bp-icon-btn" title="Editar" aria-label="Editar">
                                <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M4 20h4l10-10-4-4L4 16v4z"/><path d="M14 6l4 4"/></svg>
                            </a>
                        <?php endif; ?>
                        <?php if (has_permission('teams.delete')): ?>
                            <form method="post" action="<?= base_url('/teams/' . $team['id'] . '/delete') ?>" class="bp-inline-form" onsubmit="return confirm('Excluir esta equipe?');">
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
        <?= $pager->links('teams', 'default_full') ?>
    <?php endif; ?>
</div>
<?= $this->endSection() ?>
