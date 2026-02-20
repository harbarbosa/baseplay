<?= $this->extend('layouts/base') ?>

<?= $this->section('content') ?>
<div class="card">
    <div style="display:flex; justify-content:space-between; align-items:center;">
        <div>
            <h1>Tipos de documento</h1>
            <p style="color:var(--muted);">Controle dos tipos e regras de validade.</p>
        </div>
        <a href="<?= base_url('/document-types/create') ?>" class="button">Novo tipo</a>
    </div>

    <form method="get" action="<?= base_url('/document-types') ?>" style="margin:16px 0; display:flex; gap:12px; flex-wrap:wrap;">
        <input type="text" name="search" placeholder="Buscar nome" value="<?= esc($filters['search'] ?? '') ?>">
        <select name="status">
            <option value="">Status</option>
            <option value="active" <?= ($filters['status'] ?? '') === 'active' ? 'selected' : '' ?>>Ativo</option>
            <option value="inactive" <?= ($filters['status'] ?? '') === 'inactive' ? 'selected' : '' ?>>Inativo</option>
        </select>
        <button type="submit">Filtrar</button>
        <a href="<?= base_url('/document-types') ?>" class="button secondary">Limpar</a>
    </form>

    <table class="table">
        <thead>
            <tr>
                <th>Nome</th>
                <th>Exige vencimento</th>
                <th>Dias padrão</th>
                <th>Status</th>
                <th>Ações</th>
            </tr>
        </thead>
        <tbody>
        <?php foreach ($types as $type): ?>
            <tr>
                <td><?= esc($type['name']) ?></td>
                <td><?= (int) $type['requires_expiration'] === 1 ? 'Sim' : 'Não' ?></td>
                <td><?= esc($type['default_valid_days'] ?? '-') ?></td>
                <td><?= esc($type['status']) ?></td>
                <td>
                    <a href="<?= base_url('/document-types/' . $type['id'] . '/edit') ?>">Editar</a>
                    | <a href="<?= base_url('/document-types/' . $type['id'] . '/delete') ?>">Excluir</a>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>

    <?php if ($pager): ?>
        <?= $pager->links('document_types', 'default_full') ?>
    <?php endif; ?>
</div>
<?= $this->endSection() ?>
