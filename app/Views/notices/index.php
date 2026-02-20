<?= $this->extend('layouts/base') ?>

<?= $this->section('content') ?>
<div class="card">
    <div style="display:flex; justify-content:space-between; align-items:center;">
        <div>
            <h1>Avisos</h1>
            <p style="color:var(--muted);">Comunicados e confirmações de leitura.</p>
        </div>
        <?php if (has_permission('notices.create')): ?>
            <a href="<?= base_url('/notices/create') ?>" class="button">Novo aviso</a>
        <?php endif; ?>
    </div>

    <form method="get" action="<?= base_url('/notices') ?>" style="margin:16px 0; display:flex; gap:12px; flex-wrap:wrap;">
        <input type="text" name="search" placeholder="Buscar título" value="<?= esc($filters['search'] ?? '') ?>">
        <select name="team_id" onchange="this.form.submit()">
            <option value="">Equipe</option>
            <?php foreach ($teams as $team): ?>
                <option value="<?= esc($team['id']) ?>" <?= ($filters['team_id'] ?? '') == $team['id'] ? 'selected' : '' ?>>
                    <?= esc($team['name']) ?>
                </option>
            <?php endforeach; ?>
        </select>
        <select name="category_id">
            <option value="">Categoria</option>
            <?php foreach ($categories as $category): ?>
                <option value="<?= esc($category['id']) ?>" <?= ($filters['category_id'] ?? '') == $category['id'] ? 'selected' : '' ?>>
                    <?= esc($category['name']) ?>
                </option>
            <?php endforeach; ?>
        </select>
        <select name="priority">
            <option value="">Prioridade</option>
            <option value="normal" <?= ($filters['priority'] ?? '') === 'normal' ? 'selected' : '' ?>>Normal</option>
            <option value="important" <?= ($filters['priority'] ?? '') === 'important' ? 'selected' : '' ?>>Importante</option>
            <option value="urgent" <?= ($filters['priority'] ?? '') === 'urgent' ? 'selected' : '' ?>>Urgente</option>
        </select>
        <select name="status">
            <option value="">Status</option>
            <option value="draft" <?= ($filters['status'] ?? '') === 'draft' ? 'selected' : '' ?>>Rascunho</option>
            <option value="published" <?= ($filters['status'] ?? '') === 'published' ? 'selected' : '' ?>>Publicado</option>
            <option value="archived" <?= ($filters['status'] ?? '') === 'archived' ? 'selected' : '' ?>>Arquivado</option>
        </select>
        <input type="date" name="from_date" value="<?= esc($filters['from_date'] ?? '') ?>">
        <input type="date" name="to_date" value="<?= esc($filters['to_date'] ?? '') ?>">
        <button type="submit">Filtrar</button>
        <a href="<?= base_url('/notices') ?>" class="button secondary">Limpar</a>
    </form>

    <table class="table">
        <thead>
            <tr>
                <th>Título</th>
                <th>Prioridade</th>
                <th>Equipe</th>
                <th>Categoria</th>
                <th>Status</th>
                <th>Publicação</th>
                <th>Leitura</th>
                <th>Ações</th>
            </tr>
        </thead>
        <tbody>
        <?php foreach ($notices as $notice): ?>
            <tr>
                <td><?= esc($notice['title']) ?></td>
                <td><span class="badge badge-<?= esc($notice['priority']) ?>"><?= esc(ucfirst($notice['priority'])) ?></span></td>
                <td><?= esc($notice['team_name'] ?? '-') ?></td>
                <td><?= esc($notice['category_name'] ?? '-') ?></td>
                <td><?= esc(enum_label($notice['status'], 'status')) ?></td>
                <td><?= esc(format_datetime_br($notice['publish_at'] ?? null)) ?></td>
                <td><?= esc($notice['read_count']) ?>/<?= esc($notice['target_count']) ?> (<?= esc($notice['read_percent']) ?>%)</td>
                <td>
                    <a href="<?= base_url('/notices/' . $notice['id']) ?>">Detalhes</a>
                    <?php if (has_permission('notices.update')): ?>
                        | <a href="<?= base_url('/notices/' . $notice['id'] . '/edit') ?>">Editar</a>
                    <?php endif; ?>
                    <?php if (has_permission('notices.delete')): ?>
                        | <a href="<?= base_url('/notices/' . $notice['id'] . '/delete') ?>">Excluir</a>
                    <?php endif; ?>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>

    <?php if ($pager): ?>
        <?= $pager->links('notices', 'default_full') ?>
    <?php endif; ?>
</div>
<?= $this->endSection() ?>
