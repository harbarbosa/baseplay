<?= $this->extend('layouts/base') ?>

<?= $this->section('content') ?>
<div class="card">
    <div style="display:flex;justify-content:space-between;align-items:center;gap:12px;flex-wrap:wrap;">
        <div>
            <h1>Central de Pendências</h1>
            <p style="color:var(--muted);margin:0;">Visão operacional de documentos e eventos que exigem ação.</p>
        </div>
    </div>

    <form method="get" action="<?= base_url('/pending-center') ?>" style="margin:16px 0; display:flex; gap:12px; flex-wrap:wrap;">
        <select name="team_id">
            <option value="">Equipe</option>
            <?php foreach ($teams as $team): ?>
                <option value="<?= esc($team['id']) ?>" <?= (string) ($filters['team_id'] ?? '') === (string) $team['id'] ? 'selected' : '' ?>>
                    <?= esc($team['name']) ?>
                </option>
            <?php endforeach; ?>
        </select>

        <select name="category_id">
            <option value="">Categoria</option>
            <?php foreach ($categories as $category): ?>
                <option value="<?= esc($category['id']) ?>" <?= (string) ($filters['category_id'] ?? '') === (string) $category['id'] ? 'selected' : '' ?>>
                    <?= esc($category['name']) ?>
                </option>
            <?php endforeach; ?>
        </select>

        <select name="type">
            <option value="">Tipo de pendência</option>
            <option value="expired_documents" <?= ($filters['type'] ?? '') === 'expired_documents' ? 'selected' : '' ?>>Documentos vencidos</option>
            <option value="expiring_documents" <?= ($filters['type'] ?? '') === 'expiring_documents' ? 'selected' : '' ?>>Documentos a vencer</option>
            <option value="missing_required_documents" <?= ($filters['type'] ?? '') === 'missing_required_documents' ? 'selected' : '' ?>>Sem documento obrigatório</option>
            <option value="upcoming_events_without_callups" <?= ($filters['type'] ?? '') === 'upcoming_events_without_callups' ? 'selected' : '' ?>>Eventos sem convocação</option>
        </select>

        <button type="submit">Filtrar</button>
        <a href="<?= base_url('/pending-center') ?>" class="button secondary">Limpar</a>
    </form>

    <div class="stat-grid">
        <div class="card stat-card"><strong>Vencidos</strong><div class="stat-value"><?= count($data['expired_documents'] ?? []) ?></div></div>
        <div class="card stat-card"><strong>A vencer</strong><div class="stat-value"><?= count($data['expiring_documents'] ?? []) ?></div></div>
        <div class="card stat-card"><strong>Sem obrigatório</strong><div class="stat-value"><?= count($data['missing_required_documents'] ?? []) ?></div></div>
        <div class="card stat-card"><strong>Eventos sem convocação</strong><div class="stat-value"><?= count($data['upcoming_events_without_callups'] ?? []) ?></div></div>
    </div>

    <div class="card" style="margin-top:16px;">
        <h2>Documentos vencidos</h2>
        <table class="table">
            <thead><tr><th>Atleta</th><th>Tipo</th><th>Equipe</th><th>Categoria</th><th>Validade</th></tr></thead>
            <tbody>
            <?php foreach (($data['expired_documents'] ?? []) as $item): ?>
                <tr>
                    <td><?= esc(trim(($item['first_name'] ?? '') . ' ' . ($item['last_name'] ?? ''))) ?></td>
                    <td><?= esc($item['type_name'] ?? '-') ?></td>
                    <td><?= esc($item['team_name'] ?? '-') ?></td>
                    <td><?= esc($item['category_name'] ?? '-') ?></td>
                    <td><?= esc(format_date_br($item['expires_at'] ?? null)) ?></td>
                </tr>
            <?php endforeach; ?>
            <?php if (empty($data['expired_documents'])): ?>
                <tr><td colspan="5">Sem pendências.</td></tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>

    <div class="card" style="margin-top:16px;">
        <h2>Documentos a vencer (30 dias)</h2>
        <table class="table">
            <thead><tr><th>Atleta</th><th>Tipo</th><th>Equipe</th><th>Categoria</th><th>Validade</th></tr></thead>
            <tbody>
            <?php foreach (($data['expiring_documents'] ?? []) as $item): ?>
                <tr>
                    <td><?= esc(trim(($item['first_name'] ?? '') . ' ' . ($item['last_name'] ?? ''))) ?></td>
                    <td><?= esc($item['type_name'] ?? '-') ?></td>
                    <td><?= esc($item['team_name'] ?? '-') ?></td>
                    <td><?= esc($item['category_name'] ?? '-') ?></td>
                    <td><?= esc(format_date_br($item['expires_at'] ?? null)) ?></td>
                </tr>
            <?php endforeach; ?>
            <?php if (empty($data['expiring_documents'])): ?>
                <tr><td colspan="5">Sem pendências.</td></tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>

    <div class="card" style="margin-top:16px;">
        <h2>Atletas sem documento obrigatório</h2>
        <table class="table">
            <thead><tr><th>Atleta</th><th>Documento</th><th>Equipe</th><th>Categoria</th></tr></thead>
            <tbody>
            <?php foreach (($data['missing_required_documents'] ?? []) as $item): ?>
                <tr>
                    <td><?= esc(trim(($item['first_name'] ?? '') . ' ' . ($item['last_name'] ?? ''))) ?></td>
                    <td><?= esc($item['type_name'] ?? '-') ?></td>
                    <td><?= esc($item['team_name'] ?? '-') ?></td>
                    <td><?= esc($item['category_name'] ?? '-') ?></td>
                </tr>
            <?php endforeach; ?>
            <?php if (empty($data['missing_required_documents'])): ?>
                <tr><td colspan="4">Sem pendências.</td></tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>

    <div class="card" style="margin-top:16px;">
        <h2>Eventos próximos sem convocação</h2>
        <table class="table">
            <thead><tr><th>Evento</th><th>Tipo</th><th>Equipe</th><th>Categoria</th><th>Data</th></tr></thead>
            <tbody>
            <?php foreach (($data['upcoming_events_without_callups'] ?? []) as $item): ?>
                <tr>
                    <td><?= esc($item['title'] ?? '-') ?></td>
                    <td><?= esc($item['type'] ?? '-') ?></td>
                    <td><?= esc($item['team_name'] ?? '-') ?></td>
                    <td><?= esc($item['category_name'] ?? '-') ?></td>
                    <td><?= esc(format_datetime_br($item['start_datetime'] ?? null)) ?></td>
                </tr>
            <?php endforeach; ?>
            <?php if (empty($data['upcoming_events_without_callups'])): ?>
                <tr><td colspan="5">Sem pendências.</td></tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
<?= $this->endSection() ?>

