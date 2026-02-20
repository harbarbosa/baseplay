<?= $this->extend('layouts/base') ?>

<?= $this->section('content') ?>
<div class="card">
    <div style="display:flex; justify-content:space-between; align-items:center;">
        <h1>Papéis</h1>
    </div>
    <table class="table">
        <thead>
            <tr>
                <th>ID</th>
                <th>Nome</th>
                <th>Descrição</th>
            </tr>
        </thead>
        <tbody>
        <?php foreach ($roles as $role): ?>
            <tr>
                <td><?= esc($role['id']) ?></td>
                <td><?= esc($role['name']) ?></td>
                <td><?= esc($role['description']) ?></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>
<?= $this->endSection() ?>
