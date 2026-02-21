<?= $this->extend('layouts/base') ?>

<?= $this->section('content') ?>
<div class="card">
    <div style="display:flex; justify-content:space-between; align-items:center;">
        <div>
            <h1>Responsáveis</h1>
            <p style="color:var(--muted);">Cadastro de responsáveis.</p>
        </div>
        <?php if (has_permission('guardians.create')): ?>
            <a href="<?= base_url('/guardians/create') ?>" class="button">Novo responsável</a>
        <?php endif; ?>
    </div>

    <form method="get" action="<?= base_url('/guardians') ?>" style="margin:16px 0; display:flex; gap:12px; flex-wrap:wrap;">
        <input type="text" name="search" placeholder="Buscar por nome, e-mail ou telefone" value="<?= esc($filters['search'] ?? '') ?>">
        <select name="status">
            <option value="">Status</option>
            <option value="active" <?= ($filters['status'] ?? '') === 'active' ? 'selected' : '' ?>>Ativo</option>
            <option value="inactive" <?= ($filters['status'] ?? '') === 'inactive' ? 'selected' : '' ?>>Inativo</option>
        </select>
        <button type="submit">Filtrar</button>
        <a href="<?= base_url('/guardians') ?>" class="button secondary">Limpar</a>
    </form>

    <table class="table">
        <thead>
            <tr>
                <th>ID</th>
                <th>Nome</th>
                <th>Telefone</th>
                <th>E-mail</th>
                <th>Status</th>
                <th>Ações</th>
            </tr>
        </thead>
        <tbody>
        <?php foreach ($guardians as $guardian): ?>
            <tr>
                <td><?= esc($guardian['id']) ?></td>
                <td><?= esc($guardian['full_name']) ?></td>
                <td><?= esc($guardian['phone'] ?? '-') ?></td>
                <td><?= esc($guardian['email'] ?? '-') ?></td>
                <td><?= esc(enum_label($guardian['status'], 'status')) ?></td>
                <td>
                    <a href="<?= base_url('/guardians/' . $guardian['id']) ?>">Detalhes</a>
                    <?php if (has_permission('guardians.update')): ?>
                        | <a href="<?= base_url('/guardians/' . $guardian['id'] . '/edit') ?>">Editar</a>
                    <?php endif; ?>
                    <?php if (has_permission('guardians.delete')): ?>
                        | <a href="<?= base_url('/guardians/' . $guardian['id'] . '/delete') ?>">Excluir</a>
                    <?php endif; ?>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>

    <?php if ($pager): ?>
        <?= $pager->links('guardians', 'default_full') ?>
    <?php endif; ?>
</div>
<?= $this->endSection() ?>
