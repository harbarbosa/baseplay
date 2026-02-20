<?= $this->extend('layouts/base') ?>

<?= $this->section('content') ?>
<div class="card">
    <div style="display:flex; justify-content:space-between; align-items:center; gap:12px; flex-wrap:wrap;">
        <div>
            <h1>Documentos</h1>
            <p style="color:var(--muted);">Controle de vencimento, conformidade e upload por atleta.</p>
        </div>
        <?php if (has_permission('documents.upload')): ?>
            <a href="<?= base_url('/documents/create') ?>" class="button">Enviar documento</a>
        <?php endif; ?>
    </div>

    <div class="stat-grid" style="margin-top:14px;">
        <div class="card stat-card"><strong>Vencidos</strong><div class="stat-value"><?= esc($statusCounters['expired'] ?? 0) ?></div></div>
        <div class="card stat-card"><strong>A vencer (30 dias)</strong><div class="stat-value"><?= esc($statusCounters['expiring'] ?? 0) ?></div></div>
        <div class="card stat-card"><strong>Ativos</strong><div class="stat-value"><?= esc($statusCounters['active'] ?? 0) ?></div></div>
    </div>

    <form method="get" action="<?= base_url('/documents') ?>" style="margin:16px 0; display:flex; gap:12px; flex-wrap:wrap;">
        <select name="team_id" id="filter_team_id">
            <option value="">Equipe</option>
            <?php foreach ($teams as $team): ?>
                <option value="<?= esc($team['id']) ?>" <?= ($filters['team_id'] ?? '') == $team['id'] ? 'selected' : '' ?>><?= esc($team['name']) ?></option>
            <?php endforeach; ?>
        </select>

        <select name="category_id" id="filter_category_id">
            <option value="">Categoria</option>
            <?php foreach ($categories as $category): ?>
                <option value="<?= esc($category['id']) ?>" data-team-id="<?= esc($category['team_id'] ?? '') ?>" <?= ($filters['category_id'] ?? '') == $category['id'] ? 'selected' : '' ?>>
                    <?= esc(preg_replace('/^sub[\s-]*/iu', 'Categoria ', (string) $category['name'])) ?>
                </option>
            <?php endforeach; ?>
        </select>

        <input type="text" name="athlete_name" placeholder="Nome do atleta" value="<?= esc($filters['athlete_name'] ?? '') ?>">

        <select name="document_type_id">
            <option value="">Tipo</option>
            <?php foreach ($types as $type): ?>
                <option value="<?= esc($type['id']) ?>" <?= ($filters['document_type_id'] ?? '') == $type['id'] ? 'selected' : '' ?>><?= esc($type['name']) ?></option>
            <?php endforeach; ?>
        </select>

        <select name="status">
            <option value="">Status</option>
            <option value="active" <?= ($filters['status'] ?? '') === 'active' ? 'selected' : '' ?>>Ativo</option>
            <option value="expired" <?= ($filters['status'] ?? '') === 'expired' ? 'selected' : '' ?>>Vencido</option>
            <option value="archived" <?= ($filters['status'] ?? '') === 'archived' ? 'selected' : '' ?>>Arquivado</option>
        </select>

        <select name="expiring_in_days">
            <option value="">Vencendo em</option>
            <option value="7" <?= ($filters['expiring_in_days'] ?? '') == '7' ? 'selected' : '' ?>>7 dias</option>
            <option value="15" <?= ($filters['expiring_in_days'] ?? '') == '15' ? 'selected' : '' ?>>15 dias</option>
            <option value="30" <?= ($filters['expiring_in_days'] ?? '') == '30' ? 'selected' : '' ?>>30 dias</option>
        </select>

        <select name="sort">
            <option value="expires_nearest" <?= ($filters['sort'] ?? '') === 'expires_nearest' ? 'selected' : '' ?>>Vencimento mais próximo</option>
            <option value="created_desc" <?= ($filters['sort'] ?? '') === 'created_desc' ? 'selected' : '' ?>>Mais recentes</option>
        </select>

        <input type="date" name="from_date" value="<?= esc($filters['from_date'] ?? '') ?>">
        <input type="date" name="to_date" value="<?= esc($filters['to_date'] ?? '') ?>">
        <button type="submit">Filtrar</button>
        <a href="<?= base_url('/documents') ?>" class="button secondary">Limpar</a>
    </form>

    <div class="progress-wrap">
        <h2>Conformidade por categoria</h2>
        <?php foreach (array_slice($complianceByCategory ?? [], 0, 8) as $row): ?>
            <div class="progress-row">
                <div class="progress-label">
                    <span><?= esc(($row['team_name'] ?? '-') . ' · ' . ($row['category_name'] ?? '-')) ?></span>
                    <span><?= esc($row['percentage'] ?? 0) ?>%</span>
                </div>
                <div class="progress-track"><div class="progress-bar" style="width: <?= esc((string) ($row['percentage'] ?? 0)) ?>%;"></div></div>
            </div>
        <?php endforeach; ?>
        <?php if (empty($complianceByCategory)): ?>
            <p style="color:var(--muted);">Sem dados de conformidade.</p>
        <?php endif; ?>
    </div>

    <table class="table" style="margin-top:16px;">
        <thead>
            <tr>
                <th>Arquivo</th>
                <th>Tipo</th>
                <th>Atleta</th>
                <th>Responsável</th>
                <th>Equipe</th>
                <th>Validade</th>
                <th>Status</th>
                <th>Ações</th>
            </tr>
        </thead>
        <tbody>
        <?php foreach ($documents as $doc): ?>
            <?php
                $status = (string) ($doc['status'] ?? 'active');
                $badgeClass = $status === 'expired' ? 'badge-expired' : ($status === 'archived' ? 'badge-archived' : 'badge-active');
                $expiresTs = !empty($doc['expires_at']) ? strtotime($doc['expires_at']) : null;
                $isUrgent = $expiresTs && $expiresTs <= strtotime('+7 days') && $status === 'active';
                $isWarning = $expiresTs && $expiresTs > strtotime('+7 days') && $expiresTs <= strtotime('+30 days') && $status === 'active';
                $fullName = trim(($doc['first_name'] ?? '') . ' ' . ($doc['last_name'] ?? ''));
            ?>
            <tr>
                <td><?= esc($doc['original_name'] ?? '-') ?></td>
                <td><?= esc($doc['type_name'] ?? '-') ?></td>
                <td><?= esc($fullName !== '' ? $fullName : '-') ?></td>
                <td><?= esc($doc['guardian_name'] ?? '-') ?></td>
                <td><?= esc($doc['team_name'] ?? '-') ?></td>
                <td>
                    <?= esc(format_date_br($doc['expires_at'] ?? null)) ?>
                    <?php if ($isUrgent): ?><span class="badge badge-critical" style="margin-left:6px;">Urgente</span><?php endif; ?>
                    <?php if ($isWarning): ?><span class="badge badge-warning" style="margin-left:6px;">A vencer</span><?php endif; ?>
                </td>
                <td><span class="badge <?= esc($badgeClass) ?>"><?= esc(enum_label($status, 'status')) ?></span></td>
                <td>
                    <a href="<?= base_url('/documents/' . $doc['id']) ?>">Detalhes</a>
                    | <a href="<?= base_url('/documents/' . $doc['id'] . '/download') ?>">Baixar</a>
                    <?php if (has_permission('documents.update')): ?>
                        | <a href="<?= base_url('/documents/' . $doc['id'] . '/edit') ?>">Editar</a>
                    <?php endif; ?>
                    <?php if (has_permission('documents.delete')): ?>
                        | <a href="<?= base_url('/documents/' . $doc['id'] . '/delete') ?>">Excluir</a>
                    <?php endif; ?>
                </td>
            </tr>
        <?php endforeach; ?>
        <?php if (empty($documents)): ?>
            <tr><td colspan="8">Nenhum documento encontrado.</td></tr>
        <?php endif; ?>
        </tbody>
    </table>

    <?php if ($pager): ?>
        <?= $pager->links('documents', 'default_full') ?>
    <?php endif; ?>
</div>

<script>
(() => {
    const teamSelect = document.getElementById('filter_team_id');
    const categorySelect = document.getElementById('filter_category_id');
    if (!teamSelect || !categorySelect) return;

    const filterCategories = () => {
        const teamId = teamSelect.value;
        Array.from(categorySelect.options).forEach((opt) => {
            if (!opt.value) return;
            const optTeam = opt.getAttribute('data-team-id');
            opt.hidden = teamId && optTeam !== teamId;
        });
        const selected = categorySelect.selectedOptions[0];
        if (selected && selected.hidden) {
            categorySelect.value = '';
        }
    };

    teamSelect.addEventListener('change', filterCategories);
    filterCategories();
})();
</script>
<?= $this->endSection() ?>
