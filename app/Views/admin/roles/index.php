<?= $this->extend('layouts/base') ?>

<?= $this->section('content') ?>
<div class="bp-card">
    <div class="bp-card-header" style="display:flex; align-items:center; justify-content:space-between; gap:16px;">
        <div>
            <h1 style="margin:0;">Papéis</h1>
            <div class="bp-text-muted">Gerencie papéis e suas permissões.</div>
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
                            <div class="bp-action-buttons">
                                <a href="<?= base_url('/admin/roles/' . $role['id'] . '/edit') ?>" class="bp-icon-btn" title="Editar" aria-label="Editar">
                                    <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M4 20h4l10-10-4-4L4 16v4z"/><path d="M14 6l4 4"/></svg>
                                </a>
                                <form method="post" action="<?= base_url('/admin/roles/' . $role['id'] . '/delete') ?>" class="bp-inline-form" onsubmit="return confirm('Remover este papel?');">
                                    <?= csrf_field() ?>
                                    <button type="submit" class="bp-icon-btn bp-icon-danger" title="Excluir" aria-label="Excluir">
                                        <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M3 6h18"/><path d="M8 6V4h8v2"/><path d="M6 6l1 14h10l1-14"/></svg>
                                    </button>
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
