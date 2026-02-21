<?= $this->extend('layouts/base') ?>

<?= $this->section('content') ?>
<div class="bp-card">
    <div class="bp-card-header" style="display:flex; align-items:center; justify-content:space-between; gap:16px;">
        <div>
            <h1 style="margin:0;">Papéis</h1>
            <div class="bp-text-muted">Gerencie papéis e suas permissoes.</div>
        </div>
        <div style="display:flex; gap:8px; flex-wrap:wrap;">
            <a href="<?= base_url('/admin/roles/create') ?>" class="bp-btn-primary">Novo papel</a>
        </div>
    </div>
    <div class="bp-card-body">
        <table class="bp-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Nome</th>
                    <th>Descrição</th>
                    <th style="width:160px;">Ações</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($roles as $role): ?>
                <tr>
                    <td><?= esc($role['id']) ?></td>
                    <td>
                        <?= esc($role['name']) ?>
                        <?php if (strtolower((string) $role['name']) === 'admin'): ?>
                            <span class="bp-badge bp-badge-info" style="margin-left:6px;">Protegido</span>
                        <?php endif; ?>
                    </td>
                    <td><?= esc($role['description']) ?></td>
                    <td>
                        <?php if (strtolower((string) $role['name']) !== 'admin'): ?>
                            <div style="display:flex; gap:6px; align-items:center;">
                                <a href="<?= base_url('/admin/roles/' . $role['id'] . '/edit') ?>" class="bp-btn-secondary">Editar</a>
                                <form method="post" action="<?= base_url('/admin/roles/' . $role['id'] . '/delete') ?>" onsubmit="return confirm('Remover este papel?');">
                                    <?= csrf_field() ?>
                                    <button type="submit" class="bp-btn-danger">Remover</button>
                                </form>
                            </div>
                        <?php else: ?>
                            <span class="bp-text-muted">Sem ações</span>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
<?= $this->endSection() ?>
