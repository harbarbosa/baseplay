<?= $this->extend('layouts/base') ?>

<?= $this->section('content') ?>
<?php
$pendingItems = $pending['items'] ?? [];
$lowItems = $lowAttendance['items'] ?? [];
$pendingTotal = (int) ($pending['total'] ?? 0);
$lowTotal = (int) ($lowAttendance['total'] ?? 0);
$pendingPage = (int) ($pending['page'] ?? 1);
$lowPage = (int) ($lowAttendance['page'] ?? 1);
$perPage = (int) ($paging['per_page'] ?? 10);

$pageUrl = static function (string $key, int $page): string {
    $params = $_GET;
    $params[$key] = $page;
    return current_url() . '?' . http_build_query($params);
};
?>

<div class="bp-card" style="margin-bottom:18px;">
    <div class="bp-card-body" style="display:flex; align-items:center; justify-content:space-between; gap:12px; flex-wrap:wrap;">
        <div>
            <h1 style="margin:0 0 6px;">Elenco — Visão geral</h1>
            <p style="margin:0; color:var(--muted);">Resumo rapido com foco no que precisa de atenção imédiata.</p>
        </div>
        <?php if (has_permission('athletes.create')): ?>
            <a href="<?= base_url('/athletes/create') ?>" class="bp-btn-primary">Novo atleta</a>
        <?php endif; ?>
    </div>
</div>

<div class="bp-card" style="margin-bottom:18px;">
    <div class="bp-card-body">
        <div style="display:grid; gap:14px; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));">
            <div class="bp-card" style="padding:14px;">
                <div style="color:var(--muted); font-size:12px;">Atletas ativos</div>
                <div style="font-size:26px; font-weight:700; color:var(--primary);"><?= esc($kpis['active_athletes'] ?? 0) ?></div>
            </div>
            <div class="bp-card" style="padding:14px;">
                <div style="color:var(--muted); font-size:12px;">Pendências de documentos (7 dias)</div>
                <div style="font-size:26px; font-weight:700; color:var(--primary);"><?= esc($kpis['pending_documents'] ?? 0) ?></div>
            </div>
            <div class="bp-card" style="padding:14px;">
                <div style="color:var(--muted); font-size:12px;">Baixa presença (30 dias)</div>
                <div style="font-size:26px; font-weight:700; color:var(--primary);"><?= esc($kpis['low_attendance'] ?? 0) ?></div>
            </div>
            <div class="bp-card" style="padding:14px;">
                <div style="color:var(--muted); font-size:12px;">Próximos eventos (7 dias)</div>
                <div style="font-size:26px; font-weight:700; color:var(--primary);"><?= esc($kpis['upcoming_events'] ?? 0) ?></div>
            </div>
        </div>
    </div>
</div>

<?php if (has_permission('documents.view')): ?>
    <div class="bp-card" style="margin-bottom:18px;">
        <div class="bp-card-body">
            <div style="display:flex; align-items:center; justify-content:space-between; gap:12px; margin-bottom:12px;">
                <div>
                    <h2 style="margin:0;">Atletas com pendencia</h2>
                    <div style="color:var(--muted); font-size:12px;">Top 10 com documentos vencidos ou a vencer.</div>
                </div>
                <a class="bp-btn-secondary" href="<?= base_url('/documents?expiring_in_days=7') ?>">Ver tudo</a>
            </div>
            <div class="bp-table-wrap">
                <table class="bp-table">
                    <thead>
                        <tr>
                            <th>Atleta</th>
                            <th>Equipe / Categoria</th>
                            <th>Pendências</th>
                            <th>Próximo vencimento</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!$pendingItems): ?>
                            <tr>
                                <td colspan="4" style="color:var(--muted);">Nenhuma pendencia encontrada.</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($pendingItems as $row): ?>
                                <tr>
                                    <td><?= esc(trim(($row['first_name'] ?? '') . ' ' . ($row['last_name'] ?? ''))) ?></td>
                                    <td><?= esc(($row['team_name'] ?? '-') . ' / ' . ($row['category_name'] ?? '-')) ?></td>
                                    <td><?= esc($row['pending_count'] ?? 0) ?></td>
                                    <td><?= esc($row['next_expiry'] ?? '-') ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
            <div style="display:flex; align-items:center; justify-content:space-between; margin-top:12px; font-size:12px; color:var(--muted);">
                <span><?= $pendingTotal ?> itens</span>
                <div style="display:flex; gap:8px;">
                    <?php if ($pendingPage > 1): ?>
                        <a class="bp-btn-ghost" href="<?= esc($pageUrl('pending_page', $pendingPage - 1)) ?>">Anterior</a>
                    <?php endif; ?>
                    <?php if ($pendingPage * $perPage < $pendingTotal): ?>
                        <a class="bp-btn-ghost" href="<?= esc($pageUrl('pending_page', $pendingPage + 1)) ?>">Proxima</a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
<?php endif; ?>

<?php if (has_permission('attendance.manage') || has_permission('reports.view')): ?>
    <div class="bp-card" style="margin-bottom:18px;">
        <div class="bp-card-body">
            <div style="display:flex; align-items:center; justify-content:space-between; gap:12px; margin-bottom:12px;">
                <div>
                    <h2 style="margin:0;">Atletas com baixa presença</h2>
                    <div style="color:var(--muted); font-size:12px;">Abaixo de 60% nos últimos 30 dias.</div>
                </div>
                <?php if (has_permission('reports.view')): ?>
                    <a class="bp-btn-secondary" href="<?= base_url('/reports/attendance') ?>">Ver relatório</a>
                <?php endif; ?>
            </div>
            <div class="bp-table-wrap">
                <table class="bp-table">
                    <thead>
                        <tr>
                            <th>Atleta</th>
                            <th>Equipe / Categoria</th>
                            <th>Presença</th>
                            <th>Total eventos</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!$lowItems): ?>
                            <tr>
                                <td colspan="4" style="color:var(--muted);">Nenhum atleta abaixo da meta.</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($lowItems as $row): ?>
                                <?php
                                $attended = (int) ($row['attended'] ?? 0);
                                $total = (int) ($row['total'] ?? 0);
                                $rate = $total > 0 ? round(($attended / $total) * 100, 1) : 0;
                                ?>
                                <tr>
                                    <td><?= esc(trim(($row['first_name'] ?? '') . ' ' . ($row['last_name'] ?? ''))) ?></td>
                                    <td><?= esc(($row['team_name'] ?? '-') . ' / ' . ($row['category_name'] ?? '-')) ?></td>
                                    <td><?= esc($rate) ?>%</td>
                                    <td><?= esc($total) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
            <div style="display:flex; align-items:center; justify-content:space-between; margin-top:12px; font-size:12px; color:var(--muted);">
                <span><?= $lowTotal ?> itens</span>
                <div style="display:flex; gap:8px;">
                    <?php if ($lowPage > 1): ?>
                        <a class="bp-btn-ghost" href="<?= esc($pageUrl('low_page', $lowPage - 1)) ?>">Anterior</a>
                    <?php endif; ?>
                    <?php if ($lowPage * $perPage < $lowTotal): ?>
                        <a class="bp-btn-ghost" href="<?= esc($pageUrl('low_page', $lowPage + 1)) ?>">Proxima</a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
<?php endif; ?>

<div class="bp-card">
    <div class="bp-card-body">
        <h2 style="margin-top:0;">Ações rapidas</h2>
        <div style="display:flex; flex-wrap:wrap; gap:10px;">
            <?php if (has_permission('athletes.create')): ?>
                <a href="<?= base_url('/athletes/create') ?>" class="bp-btn-primary">Novo atleta</a>
            <?php endif; ?>
            <button type="button" class="bp-btn-secondary" disabled>Importar CSV</button>
            <?php if (has_permission('documents.view')): ?>
                <a href="<?= base_url('/documents') ?>" class="bp-btn-secondary">Solicitar documentos</a>
            <?php endif; ?>
        </div>
    </div>
</div>

<?= $this->endSection() ?>
