<?= $this->extend('layouts/base') ?>

<?= $this->section('quick_action') ?>
<?php if (has_permission('documents.upload')): ?>
    <a href="<?= base_url('/documents/create') ?>" class="bp-btn-primary">Novo documento</a>
<?php endif; ?>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="bp-card" style="margin-bottom:18px;">
    <div class="bp-card-body">
        <h1 style="margin:0 0 6px;">Documentos - Visao geral</h1>
        <p style="margin:0; color:var(--muted);">Pendencias, conformidade e acoes rapidas em um unico lugar.</p>
    </div>
</div>

<div class="bp-card" style="margin-bottom:18px;">
    <div class="bp-card-body">
        <form method="get" action="<?= current_url() ?>" style="display:flex; flex-wrap:wrap; gap:10px;">
            <select name="team_id" class="bp-select">
                <option value="">Equipe</option>
                <?php foreach ($teams as $team): ?>
                    <option value="<?= esc($team['id']) ?>" <?= ($filters['team_id'] ?? '') == $team['id'] ? 'selected' : '' ?>><?= esc($team['name']) ?></option>
                <?php endforeach; ?>
            </select>
            <select name="category_id" class="bp-select">
                <option value="">Categoria</option>
                <?php foreach ($categories as $category): ?>
                    <option value="<?= esc($category['id']) ?>" <?= ($filters['category_id'] ?? '') == $category['id'] ? 'selected' : '' ?>><?= esc($category['name']) ?></option>
                <?php endforeach; ?>
            </select>
            <select name="document_type_id" class="bp-select">
                <option value="">Tipo doc</option>
                <?php foreach ($types as $type): ?>
                    <option value="<?= esc($type['id']) ?>" <?= ($filters['document_type_id'] ?? '') == $type['id'] ? 'selected' : '' ?>><?= esc($type['name']) ?></option>
                <?php endforeach; ?>
            </select>
            <select name="status" class="bp-select">
                <option value="">Status</option>
                <option value="active" <?= ($filters['status'] ?? '') === 'active' ? 'selected' : '' ?>>Ativo</option>
                <option value="expired" <?= ($filters['status'] ?? '') === 'expired' ? 'selected' : '' ?>>Vencido</option>
                <option value="archived" <?= ($filters['status'] ?? '') === 'archived' ? 'selected' : '' ?>>Arquivado</option>
            </select>
            <select name="days" class="bp-select">
                <option value="7" <?= ($filters['days'] ?? 7) == 7 ? 'selected' : '' ?>>Prazo 7 dias</option>
                <option value="30" <?= ($filters['days'] ?? 7) == 30 ? 'selected' : '' ?>>Prazo 30 dias</option>
                <option value="90" <?= ($filters['days'] ?? 7) == 90 ? 'selected' : '' ?>>Prazo 90 dias</option>
            </select>
            <button type="submit" class="bp-btn-primary">Filtrar</button>
            <a href="<?= current_url() ?>" class="bp-btn-ghost">Limpar</a>
        </form>
    </div>
</div>

<div class="bp-card" style="margin-bottom:18px;">
    <div class="bp-card-body">
        <div style="display:grid; gap:14px; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));">
            <div class="bp-card" style="padding:14px;">
                <div style="color:var(--muted); font-size:12px;">Vencidos</div>
                <div style="font-size:26px; font-weight:700; color:var(--primary);"><?= esc($cards['expired'] ?? 0) ?></div>
            </div>
            <div class="bp-card" style="padding:14px;">
                <div style="color:var(--muted); font-size:12px;">A vencer</div>
                <div style="font-size:26px; font-weight:700; color:var(--primary);"><?= esc($cards['expiring'] ?? 0) ?></div>
            </div>
            <div class="bp-card" style="padding:14px;">
                <div style="color:var(--muted); font-size:12px;">Faltando obrigatorio</div>
                <div style="font-size:26px; font-weight:700; color:var(--primary);"><?= esc($cards['missing_required'] ?? 0) ?></div>
            </div>
            <div class="bp-card" style="padding:14px;">
                <div style="color:var(--muted); font-size:12px;">Aguardando aprovacao</div>
                <div style="font-size:26px; font-weight:700; color:var(--primary);"><?= esc($cards['awaiting_approval'] ?? 0) ?></div>
            </div>
        </div>
    </div>
</div>

<div class="bp-card" style="margin-bottom:18px;">
    <div class="bp-card-body">
        <h2 style="margin-top:0;">Conformidade por categoria</h2>
        <div class="bp-table-wrap">
            <table class="bp-table">
                <thead>
                    <tr>
                        <th>Categoria</th>
                        <th>Atletas</th>
                        <th>% OK</th>
                        <th>% Pendencia</th>
                        <th>% Vencido</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!$compliance): ?>
                        <tr>
                            <td colspan="5" style="color:var(--muted);">Sem dados de conformidade.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($compliance as $row): ?>
                            <tr>
                                <td><?= esc(($row['team_name'] ?? '-') . ' / ' . ($row['category_name'] ?? '-')) ?></td>
                                <td><?= esc($row['athletes_total'] ?? 0) ?></td>
                                <td>
                                    <?= esc($row['ok_pct'] ?? 0) ?>%
                                    <div class="bp-progress"><span style="width:<?= esc($row['ok_pct'] ?? 0) ?>%"></span></div>
                                </td>
                                <td><?= esc($row['pending_pct'] ?? 0) ?>%</td>
                                <td><?= esc($row['expired_pct'] ?? 0) ?>%</td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="bp-card">
    <div class="bp-card-body">
        <div style="display:flex; align-items:center; justify-content:space-between; gap:12px; margin-bottom:12px;">
            <div>
                <h2 style="margin:0;">Pendencias criticas</h2>
                <div style="color:var(--muted); font-size:12px;">Top 15 com vencimento ou pendencias obrigatorias.</div>
            </div>
            <a href="<?= base_url('/documents') ?>" class="bp-btn-secondary">Ver documentos</a>
        </div>
        <div class="bp-table-wrap">
            <table class="bp-table">
                <thead>
                    <tr>
                        <th>Atleta</th>
                        <th>Categoria</th>
                        <th>Tipo doc</th>
                        <th>Status</th>
                        <th>Vencimento</th>
                        <th>Acao</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!$critical): ?>
                        <tr>
                            <td colspan="6" style="color:var(--muted);">Nenhuma pendencia critica.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($critical as $row): ?>
                            <tr>
                                <td><?= esc(trim(($row['first_name'] ?? '') . ' ' . ($row['last_name'] ?? ''))) ?></td>
                                <td><?= esc(($row['team_name'] ?? '-') . ' / ' . ($row['category_name'] ?? '-')) ?></td>
                                <td><?= esc($row['document_type_name'] ?? '-') ?></td>
                                <td><?= esc($row['status'] ?? '-') ?></td>
                                <td><?= esc($row['expires_at'] ?? '-') ?></td>
                                <td>
                                    <?php if (has_permission('documents.view')): ?>
                                        <a class="bp-btn-ghost" href="<?= base_url('/documents') ?>">Ver</a>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?= $this->endSection() ?>
