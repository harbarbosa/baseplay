<?= $this->extend('layouts/base') ?>

<?= $this->section('content') ?>
<div class="card">
    <div style="display:flex; justify-content:space-between; align-items:center; gap:12px; flex-wrap:wrap;">
        <div>
            <h1>Quadro tático</h1>
            <p style="color:var(--muted);">Pranchetas por equipe e categoria.</p>
        </div>
        <div style="display:flex; gap:8px; flex-wrap:wrap;">
            <?php if (has_permission('templates.view')): ?>
                <a href="<?= base_url('/tactical-boards/templates') ?>" class="button secondary">Modelos</a>
            <?php endif; ?>
            <?php if (has_permission('tactical_board.create')): ?>
                <a href="<?= base_url('/tactical-boards/create') ?>" class="button">Nova prancheta</a>
            <?php endif; ?>
        </div>
    </div>

    <form method="get" action="<?= base_url('/tactical-boards') ?>" style="margin:16px 0; display:flex; gap:12px; flex-wrap:wrap;">
        <input type="text" name="search" placeholder="Buscar por título" value="<?= esc($filters['search'] ?? '') ?>">
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
                <option value="<?= esc($category['id']) ?>" <?= ($filters['category_id'] ?? '') == $category['id'] ? 'selected' : '' ?>>
                    <?= esc($category['name']) ?>
                </option>
            <?php endforeach; ?>
        </select>
        <button type="submit">Filtrar</button>
        <a href="<?= base_url('/tactical-boards') ?>" class="button secondary">Limpar</a>
    </form>

    <table class="table">
        <thead>
        <tr>
            <th>Título</th>
            <th>Equipe</th>
            <th>Categoria</th>
            <th>Atualizado em</th>
            <th>Ações</th>
        </tr>
        </thead>
        <tbody>
        <?php foreach ($boards as $board): ?>
            <tr>
                <td><?= esc($board['title']) ?></td>
                <td><?= esc($board['team_name'] ?? '-') ?></td>
                <td><?= esc($board['category_name'] ?? '-') ?></td>
                <td><?= esc(format_datetime_br($board['updated_at'] ?? null)) ?></td>
                <td>
                    <a href="<?= base_url('/tactical-boards/' . $board['id']) ?>">Abrir</a>
                    | <a href="<?= base_url('/tactical-boards/' . $board['id'] . '/states') ?>">Versões</a>
                    <?php if (has_permission('tactical_board.create')): ?>
                        | <form method="post" action="<?= base_url('/tactical-boards/' . $board['id'] . '/duplicate') ?>" style="display:inline;">
                            <?= csrf_field() ?>
                            <button type="submit" style="padding:0; border:none; background:none; color:inherit; text-decoration:underline; cursor:pointer;">Duplicar</button>
                        </form>
                    <?php endif; ?>
                    <?php if (has_permission('tactical_board.delete')): ?>
                        | <form method="post" action="<?= base_url('/tactical-boards/' . $board['id'] . '/delete') ?>" style="display:inline;" onsubmit="return confirm('Excluir esta prancheta?');">
                            <?= csrf_field() ?>
                            <button type="submit" style="padding:0; border:none; background:none; color:inherit; text-decoration:underline; cursor:pointer;">Excluir</button>
                        </form>
                    <?php endif; ?>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>

    <?php if ($pager): ?>
        <?= $pager->links('tactical_boards', 'default_full') ?>
    <?php endif; ?>
</div>
<?= $this->endSection() ?>