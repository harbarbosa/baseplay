<?= $this->extend('layouts/base') ?>

<?= $this->section('content') ?>
<div class="card">
    <div style="display:flex; justify-content:space-between; align-items:center;">
        <div>
            <h1>Exercícios</h1>
            <p style="color:var(--muted);">Biblioteca de exercícios.</p>
        </div>
        <?php if (has_permission('exercises.create')): ?>
            <a href="<?= base_url('/exercises/create') ?>" class="button">Novo exercício</a>
        <?php endif; ?>
    </div>

    <?php
    $ageLabels = [
        'u10' => 'Sub-10',
        'u11' => 'Sub-11',
        'u12' => 'Sub-12',
        'u13' => 'Sub-13',
        'u14' => 'Sub-14',
        'u15' => 'Sub-15',
        'u16' => 'Sub-16',
        'u17' => 'Sub-17',
        'u18' => 'Sub-18',
        'u19' => 'Sub-19',
        'u20' => 'Sub-20',
        'all' => 'Todas',
    ];
    $intensityLabels = [
        'low' => 'Baixa',
        'medium' => 'Média',
        'high' => 'Alta',
    ];
    ?>

    <form method="get" action="<?= base_url('/exercises') ?>" style="margin:16px 0; display:flex; gap:12px; flex-wrap:wrap;">
        <input type="text" name="search" placeholder="Buscar" value="<?= esc($filters['search'] ?? '') ?>">
        <input type="text" name="objective" placeholder="Objetivo" value="<?= esc($filters['objective'] ?? '') ?>">
        <select name="age_group">
            <option value="">Faixa etária</option>
            <?php foreach ($ageLabels as $age => $label): ?>
                <option value="<?= esc($age) ?>" <?= ($filters['age_group'] ?? '') === $age ? 'selected' : '' ?>><?= esc($label) ?></option>
            <?php endforeach; ?>
        </select>
        <select name="intensity">
            <option value="">Intensidade</option>
            <?php foreach ($intensityLabels as $intensity => $label): ?>
                <option value="<?= esc($intensity) ?>" <?= ($filters['intensity'] ?? '') === $intensity ? 'selected' : '' ?>><?= esc($label) ?></option>
            <?php endforeach; ?>
        </select>
        <select name="status">
            <option value="">Status</option>
            <option value="active" <?= ($filters['status'] ?? '') === 'active' ? 'selected' : '' ?>>Ativo</option>
            <option value="inactive" <?= ($filters['status'] ?? '') === 'inactive' ? 'selected' : '' ?>>Inativo</option>
        </select>
        <button type="submit">Filtrar</button>
        <a href="<?= base_url('/exercises') ?>" class="button secondary">Limpar</a>
    </form>

    <table class="table">
        <thead>
            <tr>
                <th>Título</th>
                <th>Objetivo</th>
                <th>Faixa</th>
                <th>Intensidade</th>
                <th>Status</th>
                <th>Ações</th>
            </tr>
        </thead>
        <tbody>
        <?php foreach ($exercises as $exercise): ?>
            <tr>
                <td><?= esc($exercise['title']) ?></td>
                <td><?= esc($exercise['objective'] ?? '-') ?></td>
                <td><?= esc($ageLabels[$exercise['age_group']] ?? $exercise['age_group']) ?></td>
                <td><?= esc($intensityLabels[$exercise['intensity']] ?? $exercise['intensity']) ?></td>
                <td><?= esc($exercise['status']) ?></td>
                <td>
                    <a href="<?= base_url('/exercises/' . $exercise['id']) ?>">Detalhes</a>
                    <?php if (has_permission('exercises.update')): ?>
                        | <a href="<?= base_url('/exercises/' . $exercise['id'] . '/edit') ?>">Editar</a>
                    <?php endif; ?>
                    <?php if (has_permission('exercises.delete')): ?>
                        | <a href="<?= base_url('/exercises/' . $exercise['id'] . '/delete') ?>">Excluir</a>
                    <?php endif; ?>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>

    <?php if ($pager): ?>
        <?= $pager->links('exercises', 'default_full') ?>
    <?php endif; ?>
</div>
<?= $this->endSection() ?>
