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
        <?php
        $genderLabels = [
            'male' => 'Masculino',
            'female' => 'Feminino',
            'mixed' => 'Misto',
            'other' => 'Outro',
        ];
        ?>
        <h2>Categorias</h2>
        <?php if (has_permission('categories.create')): ?>
            <a href="<?= base_url('/teams/' . $team['id'] . '/categories/create') ?>" class="button" style="margin:12px 0;">Nova categoria</a>
        <?php endif; ?>
        <table class="table">
            <thead>
                <tr>
                    <th>Nome</th>
                    <th>Ano</th>
                    <th>Gênero</th>
                    <th>Dias</th>
                    <th>Status</th>
                    <th>Ações</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($categories as $category): ?>
                <tr>
                    <td><?= esc($category['name']) ?></td>
                    <td><?= esc(($category['year_from'] ?? '-') . ' / ' . ($category['year_to'] ?? '-')) ?></td>
                    <td><?= esc($genderLabels[$category['gender']] ?? $category['gender']) ?></td>
                    <td><?= esc($category['training_days'] ?? '-') ?></td>
                    <td><?= esc(enum_label($category['status'], 'status')) ?></td>
                    <td>
                        <div class="bp-action-buttons">
                            <?php if (has_permission('categories.update')): ?>
                                <a href="<?= base_url('/categories/' . $category['id'] . '/edit') ?>" class="bp-icon-btn" title="Editar" aria-label="Editar">
                                    <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M4 20h4l10-10-4-4L4 16v4z"/><path d="M14 6l4 4"/></svg>
                                </a>
                            <?php endif; ?>
                            <?php if (has_permission('categories.delete')): ?>
                                <form method="post" action="<?= base_url('/categories/' . $category['id'] . '/delete') ?>" class="bp-inline-form" onsubmit="return confirm('Excluir esta categoria?');">
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
    </div>
</div>
<?= $this->endSection() ?>
