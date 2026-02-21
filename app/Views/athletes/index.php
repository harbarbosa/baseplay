<?= $this->extend('layouts/base') ?>

<?= $this->section('content') ?>
<div class="card">
    <div style="display:flex; justify-content:space-between; align-items:center;">
        <div>
            <h1>Atletas</h1>
            <p style="color:var(--muted);">Cadastro e gestÃƒÂ£o de atletas.</p>
        </div>
        <?php if (has_permission('athletes.create')): ?>
            <a href="<?= base_url('/athletes/create') ?>" class="button">Novo atleta</a>
        <?php endif; ?>
    </div>

    <form method="get" action="<?= base_url('/athletes') ?>" style="margin:16px 0; display:flex; gap:12px; flex-wrap:wrap;">
        <input type="text" name="search" placeholder="Buscar por nome" value="<?= esc($filters['search'] ?? '') ?>">
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
        <select name="status">
            <option value="">Status</option>
            <option value="active" <?= ($filters['status'] ?? '') === 'active' ? 'selected' : '' ?>>Ativo</option>
            <option value="inactive" <?= ($filters['status'] ?? '') === 'inactive' ? 'selected' : '' ?>>Inativo</option>
        </select>
        <button type="submit">Filtrar</button>
        <a href="<?= base_url('/athletes') ?>" class="button secondary">Limpar</a>
    </form>

    <?php if (empty($athletes)): ?>
        <div class="bp-empty-state" style="margin-top:16px;">
            <strong>Nenhum atleta ainda</strong>
            <div>Crie o primeiro atleta para comecar o acompanhamento.</div>
            <?php if (has_permission('athletes.create')): ?>
                <div style="margin-top:12px;">
                    <a href="<?= base_url('/athletes/create') ?>" class="bp-btn-primary">Criar atleta</a>
                </div>
            <?php endif; ?>
        </div>
    <?php else: ?>
        <table class="table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Nome</th>
                    <th>Equipe</th>
                    <th>Categoria</th>
                    <th>Status</th>
                    <th>AÃƒÂ§ÃƒÂµes</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($athletes as $athlete): ?>
                <tr>
                    <td><?= esc($athlete['id']) ?></td>
                    <td><?= esc(trim($athlete['first_name'] . ' ' . ($athlete['last_name'] ?? ''))) ?></td>
                    <td><?= esc($athlete['team_name'] ?? '-') ?></td>
                    <td><?= esc($athlete['category_name'] ?? '-') ?></td>
                    <td><?= esc(enum_label($athlete['status'], 'status')) ?></td>
                    <td>
                        <a href="<?= base_url('/athletes/' . $athlete['id']) ?>">Ver perfil</a>
                        <?php if (has_permission('athletes.update')): ?>
                            | <a href="<?= base_url('/athletes/' . $athlete['id'] . '/edit') ?>">Editar</a>
                        <?php endif; ?>
                        <?php if (has_permission('athletes.delete')): ?>
                            | <a href="<?= base_url('/athletes/' . $athlete['id'] . '/delete') ?>">Excluir</a>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>

    <?php if ($pager): ?>
        <?= $pager->links('athletes', 'default_full') ?>
    <?php endif; ?>
</div>
<?= $this->endSection() ?>
