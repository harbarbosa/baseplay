<?= $this->extend('layouts/base') ?>

<?= $this->section('content') ?>
<div class="card">
    <div style="display:flex; justify-content:space-between; align-items:center;">
        <h1>UsuÃ¡rios</h1>
        <a href="<?= base_url('/admin/users/create') ?>" class="button">Novo usuÃ¡rio</a>
    </div>
    <table class="table">
        <thead>
            <tr>
                <th>ID</th>
                <th>Nome</th>
                <th>E-mail</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
        <?php foreach ($users as $user): ?>
            <tr>
                <td><?= esc($user['id']) ?></td>
                <td><?= esc($user['name']) ?></td>
                <td><?= esc($user['email']) ?></td>
                <td><?= esc(enum_label($user['status'], 'status')) ?></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
    <?php if ($pager): ?>
        <?= $pager->links('users', 'default_full') ?>
    <?php endif; ?>
</div>
<?= $this->endSection() ?>
