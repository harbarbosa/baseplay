<?= $this->extend('layouts/base') ?>

<?= $this->section('content') ?>
<?php
$cards = $cards ?? [];
$upcoming = $upcoming ?? [];
$createdEventId = (int) ($createdEventId ?? 0);
?>

<div class="bp-card" style="margin-bottom:18px;">
    <div class="bp-card-body" style="display:flex; align-items:center; justify-content:space-between; gap:12px; flex-wrap:wrap;">
        <div>
            <h1 style="margin:0 0 6px;">Operação — Visão geral</h1>
            <p style="margin:0; color:var(--muted);">Fluxo guiado: criar evento, convocar, registrar presença e relatório.</p>
        </div>
        <?php if (has_permission('events.create')): ?>
            <a href="<?= base_url('/events/create') ?>" class="bp-btn-primary">Novo evento</a>
        <?php endif; ?>
    </div>
</div>

<div class="bp-card" style="margin-bottom:18px;">
    <div class="bp-card-body">
        <div style="display:grid; gap:14px; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));">
            <div class="bp-card" style="padding:14px;">
                <div style="color:var(--muted); font-size:12px;">Próximos eventos (7 dias)</div>
                <div style="font-size:26px; font-weight:700; color:var(--primary);"><?= esc($cards['upcoming'] ?? 0) ?></div>
            </div>
            <div class="bp-card" style="padding:14px;">
                <div style="color:var(--muted); font-size:12px;">Eventos hoje</div>
                <div style="font-size:26px; font-weight:700; color:var(--primary);"><?= esc($cards['today'] ?? 0) ?></div>
            </div>
            <div class="bp-card" style="padding:14px;">
                <div style="color:var(--muted); font-size:12px;">Sem convocação</div>
                <div style="font-size:26px; font-weight:700; color:var(--primary);"><?= esc($cards['no_callups'] ?? 0) ?></div>
            </div>
            <div class="bp-card" style="padding:14px;">
                <div style="color:var(--muted); font-size:12px;">Presenças pendentes</div>
                <div style="font-size:26px; font-weight:700; color:var(--primary);"><?= esc($cards['pending_attendance'] ?? 0) ?></div>
            </div>
        </div>
    </div>
</div>

<div class="bp-card" style="margin-bottom:18px;">
    <div class="bp-card-body">
        <div style="display:flex; align-items:center; justify-content:space-between; gap:12px; margin-bottom:12px;">
            <div>
                <h2 style="margin:0;">Próximos eventos</h2>
                <div style="color:var(--muted); font-size:12px;">Ações rapidas para manter o fluxo.</div>
            </div>
            <a href="<?= base_url('/events') ?>" class="bp-btn-secondary">Ver agenda</a>
        </div>
        <div class="bp-table-wrap">
            <table class="bp-table">
                <thead>
                    <tr>
                        <th>Evento</th>
                        <th>Equipe / Categoria</th>
                        <th>Data</th>
                        <th>Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!$upcoming): ?>
                        <tr>
                            <td colspan="4" style="color:var(--muted);">Nenhum evento nos próximos 7 dias.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($upcoming as $event): ?>
                            <?php
                            $dateLabel = $event['start_datetime'] ? date('d/m/Y H:i', strtotime($event['start_datetime'])) : '-';
                            $eventId = (int) ($event['id'] ?? 0);
                            ?>
                            <tr>
                                <td><?= esc($event['title'] ?? '-') ?></td>
                                <td><?= esc(($event['team_name'] ?? '-') . ' / ' . ($event['category_name'] ?? '-')) ?></td>
                                <td><?= esc($dateLabel) ?></td>
                                <td style="display:flex; gap:6px; flex-wrap:wrap;">
                                    <a class="bp-btn-ghost" href="<?= base_url('/events/' . $eventId) ?>">Abrir</a>
                                    <?php if (has_permission('invitations.manage') || has_permission('callups.create')): ?>
                                        <a class="bp-btn-ghost" href="<?= base_url('/events/' . $eventId) ?>">Convocar</a>
                                    <?php endif; ?>
                                    <?php if (has_permission('attendance.manage')): ?>
                                        <a class="bp-btn-ghost" href="<?= base_url('/events/' . $eventId) ?>">Modo campo</a>
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

<div class="bp-card">
    <div class="bp-card-body">
        <h2 style="margin-top:0;">Wizard operacional</h2>
        <div style="display:grid; gap:14px; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));">
            <div class="bp-card" style="padding:14px;">
                <strong>Passo 1</strong>
                <p style="margin:6px 0 12px; color:var(--muted);">Crie o evento base para a operação.</p>
                <?php if (has_permission('events.create')): ?>
                    <a href="<?= base_url('/events/create') ?>" class="bp-btn-primary">Criar evento</a>
                <?php endif; ?>
            </div>
            <div class="bp-card" style="padding:14px;">
                <strong>Passo 2</strong>
                <p style="margin:6px 0 12px; color:var(--muted);">Convocar atletas para o evento criado.</p>
                <form method="get" action="<?= base_url('/events') ?>" style="display:flex; gap:8px; flex-wrap:wrap;">
                    <input type="number" name="id" placeholder="ID do evento" class="bp-input" style="max-width:160px;">
                    <button class="bp-btn-secondary" type="submit">Abrir evento</button>
                </form>
                <small style="color:var(--muted); display:block; margin-top:8px;">Abra o evento e use a area de convocação.</small>
            </div>
        </div>
    </div>
</div>

<?= $this->endSection() ?>
