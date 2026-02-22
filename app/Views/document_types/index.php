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
                <th>Obrigatório</th>
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
                <td><?= !empty($type['is_required']) ? 'Sim' : 'Não' ?></td>
                <td><?= esc(enum_label($type['status'], 'status')) ?></td>
                <td>
                    <div class="bp-action-buttons">
                        <a href="<?= base_url('/document-types/' . $type['id'] . '/edit') ?>" class="bp-icon-btn" title="Editar" aria-label="Editar">
                            <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M4 20h4l10-10-4-4L4 16v4z"/><path d="M14 6l4 4"/></svg>
                        </a>
                        <a href="<?= base_url('/document-types/' . $type['id'] . '/delete') ?>" class="bp-icon-btn bp-icon-danger" title="Excluir" aria-label="Excluir">
                            <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M3 6h18"/><path d="M8 6V4h8v2"/><path d="M6 6l1 14h10l1-14"/></svg>
                        </a>
                    </div>
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
