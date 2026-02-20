<?= $this->extend('layouts/base') ?>

<?= $this->section('content') ?>
<div class="card">
    <div style="display:flex; justify-content:space-between; align-items:center;">
        <div>
            <h1><?= esc($team['name']) ?></h1>
            <p style="color:var(--muted);"><?= esc($team['description'] ?? '') ?></p>
        </div>
        <div>
            <?php if (has_permission('teams.update')): ?>
                <a href="<?= base_url('/teams/' . $team['id'] . '/edit') ?>" class="button">Editar equipe</a>
            <?php endif; ?>
        </div>
    </div>

    <div style="margin-top:24px;">
        <h2>Categorias</h2>
        <?php if (has_permission('categories.create')): ?>
            <a href="<?= base_url('/teams/' . $team['id'] . '/categories/create') ?>" class="button" style="margin:12px 0;">Nova categoria</a>
        <?php endif; ?>
        <table class="table">
            <thead>
                <tr>
                    <th>Nome</th>
                    <th>Ano</th>
                    <th>GÃªnero</th>
                    <th>Dias</th>
                    <th>Status</th>
                    <th>AÃ§Ãµes</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($categories as $category): ?>
                <tr>
                    <td><?= esc($category['name']) ?></td>
                    <td><?= esc(($category['year_from'] ?? '-') . ' / ' . ($category['year_to'] ?? '-')) ?></td>
                    <td><?= esc($category['gender']) ?></td>
                    <td><?= esc($category['training_days'] ?? '-') ?></td>
                    <td><?= esc(enum_label($category['status'], 'status')) ?></td>
                    <td>
                        <?php if (has_permission('categories.update')): ?>
                            <a href="<?= base_url('/categories/' . $category['id'] . '/edit') ?>">Editar</a>
                        <?php endif; ?>
                        <?php if (has_permission('categories.delete')): ?>
                            | <a href="<?= base_url('/categories/' . $category['id'] . '/delete') ?>">Excluir</a>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
<?= $this->endSection() ?>
