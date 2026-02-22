<?= $this->extend('layouts/base') ?>

<?= $this->section('content') ?>
<div class="card">
    <div style="display:flex; justify-content:space-between; align-items:center; gap:12px; flex-wrap:wrap;">
        <div>
            <h1>Quadro tático</h1>
            <p style="color:var(--muted);">Pranchetas por equipe.</p>
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
        <button type="submit">Filtrar</button>
        <a href="<?= base_url('/tactical-boards') ?>" class="button secondary">Limpar</a>
    </form>

    <table class="table">
        <thead>
        <tr>
            <th>Título</th>
            <th>Equipe</th>
            <th>Atualizado em</th>
            <th>Ações</th>
        </tr>
        </thead>
        <tbody>
        <?php foreach ($boards as $board): ?>
            <tr>
                <td><?= esc($board['title']) ?></td>
                <td><?= esc($board['team_name'] ?? '-') ?></td>
                <td><?= esc(format_datetime_br($board['updated_at'] ?? null)) ?></td>
                <td>
                    <div class="bp-action-buttons">
                        <a href="<?= base_url('/tactical-boards/' . $board['id']) ?>" class="bp-icon-btn" title="Abrir" aria-label="Abrir">
                            <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M2 12s4-6 10-6 10 6 10 6-4 6-10 6-10-6-10-6z"/><circle cx="12" cy="12" r="3"/></svg>
                        </a>
                        <a href="<?= base_url('/tactical-boards/' . $board['id'] . '/states') ?>" class="bp-icon-btn" title="Versões" aria-label="Versões">
                            <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M8 6h12"/><path d="M8 12h12"/><path d="M8 18h12"/><circle cx="4" cy="6" r="1"/><circle cx="4" cy="12" r="1"/><circle cx="4" cy="18" r="1"/></svg>
                        </a>
                        <?php if (has_permission('tactical_board.create')): ?>
                            <form method="post" action="<?= base_url('/tactical-boards/' . $board['id'] . '/duplicate') ?>" class="bp-inline-form">
                                <?= csrf_field() ?>
                                <button type="submit" class="bp-icon-btn" title="Duplicar" aria-label="Duplicar">
                                    <svg viewBox="0 0 24 24" aria-hidden="true"><rect x="9" y="9" width="10" height="10" rx="2"/><rect x="5" y="5" width="10" height="10" rx="2"/></svg>
                                </button>
                            </form>
                        <?php endif; ?>
                        <?php if (has_permission('tactical_board.delete')): ?>
                            <form method="post" action="<?= base_url('/tactical-boards/' . $board['id'] . '/delete') ?>" class="bp-inline-form" onsubmit="return confirm('Excluir esta prancheta?');">
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
        <?= $pager->links('tactical_boards', 'default_full') ?>
    <?php endif; ?>
</div>
<?= $this->endSection() ?>
