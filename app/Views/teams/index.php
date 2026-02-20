<?= $this->extend('layouts/base') ?>

<?= $this->section('content') ?>
<div class="card">
    <div style="display:flex; justify-content:space-between; align-items:center;">
        <div>
            <h1>Equipes</h1>
            <p style="color:var(--muted);">GestÃ£o de equipes e categorias.</p>
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
                <th>AÃ§Ãµes</th>
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
                    <a href="<?= base_url('/teams/' . $team['id']) ?>">Detalhes</a>
                    <?php if (has_permission('teams.update')): ?>
                        | <a href="<?= base_url('/teams/' . $team['id'] . '/edit') ?>">Editar</a>
                    <?php endif; ?>
                    <?php if (has_permission('teams.delete')): ?>
                        | <a href="<?= base_url('/teams/' . $team['id'] . '/delete') ?>">Excluir</a>
                    <?php endif; ?>
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
